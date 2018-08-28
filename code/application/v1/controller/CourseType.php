<?php
/**
 * Created by PhpStorm.
 * User: zhangchun
 * Date: 2018/7/3
 * Time: 10:52
 */

namespace app\v1\controller;

use app\v1\model\Course;
use app\v1\model\CourseRela;
use tree\Tree;
use app\v1\model\CourseType as CourseTypeModel;

class CourseType extends Core
{
    /**
     * 分类列表
     * @param center_token 场馆令牌
     * @param page 页数
     * @param len  长度
     * @param all  筛选不带分页的数据(适用于下拉框)
     * @return array|bool
     */
    public function index()
    {
        $_GET['center_token'] = 'eb8bf7f7bad2a2dceb7e511fce50a7fad5a677ba';
        $_GET['timestamp'] = time();
        $salt = 'o2fSA';
        $_GET['sign'] = encrypt_key(['v1/coursetype/index', $_GET['timestamp'], $_GET['center_token'], $salt], '');

        //签名校验
        $msg = verify();
        if ($msg['status'] == 0) {
            return $msg;
        } else {
            $center_id = $msg['data']['center_id'];
            if($center_id == 1){
                $center_id = input('center_id', 1, 'intval');
            }
        }

        //获取分类的树形结构
        $column_tree = $this->adminTypeTree($center_id);

        if ($column_tree) {
            return $column_tree;
        } else {
            return $this->fail(11000, '获取分类失败');
        }
    }

    private function adminTypeTree($center_id)
    {
//        $page = input('param.page',1,'intval');
//        $len = input('param.len',10,'intval');
        $all = input('param.all',-1,'intval');   //传all 限定超星馆的分类只有本馆的分类
//        $status = input('param.status',-1,'intval');

        //获取数据
        $typeModel = new CourseTypeModel();
        $where = [];
//        if($status != -1){
//            $where['status'] = $status;
//        }

        if($all != -1){
            if($center_id != 1){
                $where['center_id'] = $center_id;
            }
        }else{
            $where['center_id'] = $center_id;
        }


        $typeList = $typeModel->alias('t')->join('mooc_center mc','mc.id=t.center_id')->where($where)->where(['t.parent_id'=>0])->order('t.center_id asc')->field('t.*,mc.center_name,t.id as value, course_type as label')->select();
        if ($typeList) {
            $typeList = \collection($typeList)->toArray();
            $AllTypeList = $typeModel->alias('t')->join('mooc_center mc','mc.id=t.center_id')->where($where)->order('t.center_id asc')->field('t.*,mc.center_name,t.id as value, course_type as label')->select(); //所有分类
            $AllTypeList = \collection($AllTypeList)->toArray();
        } else {
            return $this->fail(11001, '获取分类失败');
        }

        //生成树型结构数组
        $tree = new Tree();
        $tree->init($AllTypeList);
        foreach($typeList as $key =>$item){
            $columnArray = $tree->getTreeArray($item['id']);
            $typeList[$key]['children'] = array_values($columnArray);
        }

        return $this->ok($typeList,11102,'获取分类成功');
    }

    /**
     * 叶节点找完整树
     * @param leaf_id 叶节点ID
     * @return array
     */
    function tree_from_leaf(){
        $leaf_id = input('param.leaf_id', 0, 'intval');
        return ok($this->parent($leaf_id), 11100, '成功');
    }

    /**
     * @param $id 子节点ID
     * @param array $result
     * @return array
     */
    private function parent($id, $result = []){
        $type = CourseTypeModel::where('id', $id)->find();
        if($type == null){
            return array_reverse($result);
        }
        $type = $type->toArray();
        $result[] = $type;
        if($type['parent_id'] == 0){
            return array_reverse($result);
        }else{
            return $this->parent($type['parent_id'], $result);
        }
    }


    public function getTableTree(){
        $_GET['center_token'] = 'e68c629d24cdbc788c1e00ead7a57b0e13bc6d2f';
        $_GET['timestamp'] = time();
        $salt = 'h1Vvh';
        $_GET['sign'] = encrypt_key(['v1/coursetype/gettabletree', $_GET['timestamp'], $_GET['center_token'], $salt], '');

        //签名校验
        $msg = verify();
        if ($msg['status'] == 0) {
            return $msg;
        } else {
            $center_id = $msg['data']['center_id'];
            if($center_id == 1){
                $center_id = input('center_id', 1, 'intval');
            }
        }

        //获取分类的树形结构
        $type_tree = $this->TableTree($center_id);

        if($type_tree){
            return $type_tree;
        }else{
            return $this->fail(10001,'获取分类失败');
        }
    }

    public function TableTree($center_id){
        $all = input('param.all',-1,'intval');

        //获取数据
        $typeModel = new CourseTypeModel();
        $where = [];
        if($all != -1){
            if($center_id != 1){
                $where['center_id'] = $center_id;
            }
        }else{
            $where['center_id'] = $center_id;
        }

        $typeList = $typeModel->alias('t')->join('mooc_center mc','mc.id=t.center_id')->where($where)->order('t.center_id asc')->field('t.*,mc.center_name,t.id as value, course_type as label')->select();
        if ($typeList) {
            $typeList = \collection($typeList)->toArray();
        } else {
            return $this->fail(11001, '获取分类失败');
        }

//        $typeLists = [];
//        foreach($typeList as $item){
//            $item['status'] = $item['status'] == 1?'显示':'隐藏';
//            $item['update_time'] = date('Y-m-d H:i',$item['update_time']);
////            $item['str_action'] = '<span style="display:inline-block;"><button style="color:#fff;background-color:#2d8cf0;border-color:#2d8cf0;padding:2px，7px;border-radius:3px;font-size: 11px;">编辑</button><button style="color:#fff;background-color:#ed3f14;border-color:#ed3f14;padding:2px，7px;border-radius:3px;font-size: 11px;">删除</button></span>';
//            array_push($typeLists,$item);
//        }

        //生成树型结构数组
        $tree = new Tree();
        $tree->icon = ['&nbsp;&nbsp;│', '&nbsp;&nbsp;├─', '&nbsp;&nbsp;└─'];
        $tree->nbsp = '&nbsp;&nbsp;';
        $tree->init($typeList);

        $tableTree = $tree->getTable(0,'course_type','');

        return $this->ok($tableTree,11101,'获取分类成功');

    }

    /**
     * 添加分类
     * @param center_token 场馆令牌
     * @param course_type  分类名称
     * @param remark  描述
     * @return array|bool
     */
    public function save()
    {
        $_GET['center_token'] = 'd772d5c2ca2e10a36c6dbb3acae6850538ad0704';
        $_GET['timestamp'] = time();
        $salt = '8FrkI';
        $_GET['sign'] = encrypt_key(['v1/coursetype/save', $_GET['timestamp'], $_GET['center_token'], $salt], '');
        $_GET['course_type'] = '诗文';
        $_GET['remark'] = '';
        $_GET['parent_id'] = 4;

        $course_type = input('param.course_type');
        $remark = input('param.remark', '');
        $parent_id = input('param.parent_id', 0, 'intval');
        $status = input('param.status',1,'intval');

        //身份校验
        $msg = verify();
        if ($msg['status'] == 0) {
            return $msg;
        } else {
            $center_id = $msg['data']['center_id'];
        }

        //分类校验
        $typeModel = new CourseTypeModel();
        if(!empty($parent_id)){
            $typeRes = $typeModel->existType($parent_id, $center_id);
            if (true !== $typeRes) {
                return fail(21006, $typeRes, 1);
            }
        }

        if (empty($course_type)) {
            return $this->fail(13011, '名称不能为空');
        }

        $data = [
            'center_id' => $center_id,
            'course_type' => $course_type,
            'remark' => $remark,
            'parent_id' => $parent_id,
            'status'=>$status,
            'create_time' => time()
        ];

        //添加课程分类
        $result = (new CourseTypeModel())->allowField(true)->isUpdate(false)->save($data);
        if ($result) {
            return $this->ok([], 11111, '添加分类成功');
        } else {
            return $this->fail(11000, '添加分类失败');
        }
    }

    /**
     * 分类编辑详情页
     * @param center_token 场馆令牌
     * @param id 分类id
     * @return array|bool
     */
    public function read()
    {
        $_GET['center_token'] = 'd772d5c2ca2e10a36c6dbb3acae6850538ad0704';
        $_GET['timestamp'] = time();
        $salt = '8FrkI';
        $_GET['sign'] = encrypt_key(['v1/coursetype/read', $_GET['timestamp'], $_GET['center_token'], $salt], '');
        $_GET['id'] = 2;

        $type_id = input('param.id');

        //身份校验
        $msg = verify();
        if ($msg['status'] == 0) {
            return $msg;
        } else {
            $center_id = $msg['data']['center_id'];
        }

        //分类校验
//        $typeModel = new CourseTypeModel();
//        $typeRes = $typeModel->existType($type_id, $center_id);
//        if (true !== $typeRes) {
//            return fail(21006, $typeRes, 1);
//        }

        $where = [];
        if($center_id != 1){
            $where['center_id'] = $center_id;
        }


        $typeModel = new CourseTypeModel();
        $typeInfo = $typeModel->where(['id' => $type_id])->where($where)->find();
        if ($typeInfo) {
            return $this->ok($typeInfo, 11111, '分类获取成功');
        } else {
            return $this->fail(11001, '分类获取失败');
        }

    }

    /**
     * 专题编辑
     * @param center_token 场馆令牌
     * @param center_id 场馆id(来源) 如果是超星文化馆必须传 如果是其他文化馆不需要传
     * @param id  专题id
     * @param title  标题
     * @param desc  描述
     * @return array|bool
     */
    public function update()
    {
        $_GET['center_token'] = 'd772d5c2ca2e10a36c6dbb3acae6850538ad0704';
        $_GET['timestamp'] = time();
        $salt = '8FrkI';
        $_GET['sign'] = encrypt_key(['v1/coursetype/update', $_GET['timestamp'], $_GET['center_token'], $salt], '');
        $_GET['course_type'] = '心理';
        $_GET['remark'] = '';
        $_GET['id'] = 6;

        $course_type = input('param.course_type');
        $remark = input('param.remark'.'','string');
        $status = input('param.status', 1, 'intval');
        $type_id = input('param.id',0,'intval');
        $parent_id = input('param.parent_id',0,'intval');


        //身份校验
        $msg = verify();
        if ($msg['status'] == 0) {
            return $msg;
        } else {
            $center_id = $msg['data']['center_id'];
        }

        //分类校验
        $typeModel = new CourseTypeModel();
        $typeRes = $typeModel->existType($parent_id, $center_id);
        if (true !== $typeRes) {
            return fail(21006, $typeRes, 1);
        }
        //父级分类校验
        if(!empty($parent_id)){
            $typeRes = $typeModel->existType($type_id, $center_id);
            if (true !== $typeRes) {
                return fail(21006, '父级分类不存在', 1);
            }
        }

        if (empty($course_type)) {
            return $this->fail(12011, '分类名称不能为空');
        }

        $data = [
            'course_type' => $course_type,
            'status' => $status,
            'remark' => $remark,
            'id' => $type_id,
            'parent_id'=>$parent_id,
            'update_time'=>time()
        ];

        $result = $typeModel->allowField(true)->update($data );
        if ($result === false) {
            return $this->fail(12011, '修改分类失败');
        } else {
            return $this->ok([], 12111, '修改分类成功');
        }
    }

    /**
     * 分类删除
     * @param center_token 场馆令牌
     * @param id  分类id
     * @return array|bool
     */
    public function delete()
    {
//        $_GET['center_token'] = 'd772d5c2ca2e10a36c6dbb3acae6850538ad0704';
//        $_GET['timestamp'] = time();
//        $salt = '8FrkI';
//        $_GET['sign'] = encrypt_key(['v1/column/delete', $_GET['timestamp'], $_GET['center_token'], $salt], '');
//        $_GET['id'] = 3;

        $type_id = input('param.id');

        //身份校验
        $msg = verify();
        if ($msg['status'] == 0) {
            return $msg;
        } else {
            $center_id = $msg['data']['center_id'];
        }

        //分类校验
        $typeModel = new CourseTypeModel();
        $typeRes = $typeModel->existType($type_id, $center_id);
        if (true !== $typeRes) {
            return fail(21006, $typeRes, 1);
        }

        $haschild = $typeModel->where(['parent_id' => $type_id, 'center_id' => $center_id])->select();
        if ($haschild) {
            return $this->fail(12011, '有子分类，请先删除子分类');
        }

        $has_mk = (new CourseRela())->where(['other_id' => $type_id, 'type' => 1, 'center_id' => $center_id])->select();
        if ($has_mk) {
            return $this->fail(12012, '分类下有课程，请先删除课程');
        }

        $result = $typeModel->where(['id' => $type_id])->delete();

        if ($result) {
            return $this->ok([], 11222, '删除分类成功');
        } else {
            return $this->fail(11200, '删除分类失败');
        }
    }


}