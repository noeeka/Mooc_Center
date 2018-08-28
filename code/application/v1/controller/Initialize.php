<?php
/**
 * Created by PhpStorm.
 * User: zhangchun
 * Date: 2018/7/4
 * Time: 15:50
 */

namespace app\v1\controller;

use app\v1\model\CenterCourse;
use app\v1\model\CenterCxType;
use app\v1\model\Course;
use app\v1\model\CourseRela;
use app\v1\model\CourseType;
use think\Log;
use tree\Tree;
use think\Db;
use think\Exception;

class Initialize extends Core
{

    private $ret = [];

    /**
     * 获取所有分类以及分类下的慕课数量
     * @param center_token 场馆令牌
     * @return array|bool
     */
    public function index()
    {
//        $_GET['center_token'] = 'd772d5c2ca2e10a36c6dbb3acae6850538ad0704';
//        $_GET['timestamp'] = time();
//        $salt = '8FrkI';
//        $_GET['sign'] = encrypt_key(['v1/initialize/index', $_GET['timestamp'], $_GET['center_token'], $salt], '');
//        $_GET['center_id'] =1;

        //签名校验
        $msg = verify();
        if ($msg['status'] == 0) {
            return $msg;
        } else {
            $center_id = $msg['data']['center_id'];
        }

        if ($center_id != 1) {
            return $this->fail(12012, '没有权限获取分类');
        }

        //获取分类的树形结构
        $column_tree = $this->TypeTree($center_id);

        if ($column_tree) {
            return $this->ok($column_tree, 11111, '获取分类成功');
        } else {
            return $this->fail(11000, '获取分类失败');
        }

    }

    public function TypeTree($center_id)
    {
        $typeModel = new CourseType();
        $where = [];
        $where['t.status'] = 1;
        $where['t.center_id'] = $center_id;
        $where['cr.type'] = 1;
        $where['cr.status'] = 1;
        $where['c.delete_time'] = 0;
        $type_list = $typeModel
            ->alias('t')
            ->join('course_rela cr', 'cr.other_id=t.id and cr.type = 1 and cr. STATUS = 1 and cr.center_id = t.center_id', 'left')
            ->join('course c', 'c.id=cr.other_id and c.delete_time = 0', 'left')
            ->field('t.id,t.course_type,count(cr.id) as num,t.parent_id')
            ->where('t. status = 1')
            ->where('t.center_id = 1')
            ->group('t.id')
            ->select();

        if ($type_list) {
            $type_list = \collection($type_list)->toArray();
        } else {
            return $this->fail(11000, '获取分类失败');
        }

        $tree = new Tree();
        $tree->init($type_list);
        $newTypeData = $tree->getTreeArray(0);

        return $newTypeData;
    }

    /**
     * 分配
     * @param center_token 场馆令牌
     * @return array|bool
     */
    public function distribute()
    {
        //签名校验
        $msg = verify();
        if ($msg['status'] == 0) {
            return $msg;
        } else {
            $center_id = $msg['data']['center_id'];
        }
        $center_id = 1;
        if ($center_id != 1) {
            return $this->fail(12012, '没有权限获取分类');
        }

        $my_cenid = input('param.center_id', 14, 'intval');
        $type_data = input('param.ids/a', ['3' => 1, '6' => 1, '7' => 1,]); //分类id,对应数量 [5=>20,6=>40]

        $result = [];
        //添加分类
        foreach ($type_data as $cx_id => $insert_num) {
            //标识第一条数据
            $is_first = true;
            //反转数组，确保先插入的是父级
            $this->ret = [];
            $typeTree = array_reverse($this->getReversePidTree($cx_id), true);
            Db::startTrans();
            try {
                foreach ($typeTree as $cx_typeid => $course_type) {
                    //获取当前要插入新纪录的父级ID
                    if ($is_first) {
                        $newTypeData['parent_id'] = 0;
                        $is_first = false;
                    } else {
                        $newTypeData['parent_id'] = CenterCxType::where(['center_id' => $my_cenid, 'cx_typeid' => $this->getPid($cx_typeid)])->value('other_typeid');
                    }

                    //新类型数据整理
                    $newTypeData['course_type'] = $course_type;
                    $newTypeData['center_id'] = $my_cenid;
                    $num = CenterCxType::where(['center_id' => $my_cenid, 'cx_typeid' => $cx_typeid])->count(1);
                    if ($num == 0) {
                        $typeModel = new CourseType();
                        if ($typeModel->save($newTypeData) > 0) {
                            $relaModel = new CenterCxType();
                            $relaData['other_typeid'] = $typeModel->id;
                            $relaData['cx_typeid'] = $cx_typeid;
                            $relaData['center_id'] = $my_cenid;
                            if ($relaModel->save($relaData) == 0) {
                                //此处应收集失败信息，并回滚
                                Db::rollback();
                                $result['rela_insert_error'][] = $cx_typeid;
                                break;
                            }
                        } else {
                            //此处应收集失败信息，并回滚
                            Db::rollback();
                            $result['type_insert_error'][] = $cx_typeid;
                            break;
                        }
                    }
                    //添加慕课关联
                    $other_typeid = CenterCxType::where(['center_id' => $my_cenid, 'cx_typeid' => $cx_typeid])->value('other_typeid');
                    $courseRelaData = (new Course())
                        ->alias('c')
                        ->join('course_rela cr', 'cr.course_id=c.id')
                        ->where(['cr.other_id' => $cx_typeid, 'cr.type' => 1, 'cr.center_id' => 1, 'c.delete_time' => 0])
                        ->fieldRaw('1 as type, c.id as course_id, ' . $other_typeid . ' as other_id, ' . $my_cenid . ' as center_id, unix_timestamp() as create_time')
                        ->orderRaw('rand()')
                        ->limit($insert_num)
                        ->select();

                    //收集要课程场馆关联数据和课程分类关联信息
                    $relaData = $center_course_data = [];
                    foreach ($courseRelaData as $item) {
                        $item = $item->toArray();
                        $rela_num = CourseRela::where(['center_id' => $my_cenid, 'course_id' => $item['course_id']])->count(1);
                        if ($rela_num == 0) $relaData[] = $item;
                        $center_num = CenterCourse::where(['center_id' => $my_cenid, 'course_id' => $item['course_id']])->count(1);
                        if ($center_num == 0) $center_course_data[] = ['center_id' => $item['center_id'], 'course_id' => $item['course_id'], 'create_time' => $item['create_time']];
                    }

                    //写入场馆课程关联表
                    $centerCourseModel = new  CenterCourse();
                    $centerRes = $centerCourseModel->insertAll($center_course_data);
                    if ($centerRes != count($center_course_data)) {
                        //回滚
                        Db::rollback();
                        break;
                    }

                    //写入课程分类关联表
                    $courseTypeModel = new  CourseRela();
                    $typeRes = $courseTypeModel->insertAll($relaData);
                    if ($typeRes != count($relaData)) {
                        //回滚
                        Db::rollback();
                        break;
                    }
                    $result['success'][] = $cx_typeid;
                }
                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                Log::error(['data' => $typeTree, 'request' => $this->request->param(), 'time' => date('Y-m-d H:i:s'), 'msg' => $e->getMessage()]);
                $result['fail'][] = $cx_typeid;
            }
        }
        return ok($result, 31101, '初始化完成');
    }

    function getPid($id)
    {
        return CourseType::where('id', $id)->value('parent_id');
    }

    function getParent($id)
    {
        return CourseType::where('id', $id)->find();
    }

    function getReversePidTree($id)
    {
        $msg = $this->getParent($id);
        $this->ret[strval($msg['id'])] = $msg['course_type'];
        if ($msg['parent_id'] == 0) {
            return $this->ret;
        } else {
            return $this->getReversePidTree($msg['parent_id']);
        }
    }


}