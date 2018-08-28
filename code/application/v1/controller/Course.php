<?php

namespace app\v1\controller;

use app\v1\model\CenterCourse;
use app\v1\model\Collect;
use app\v1\model\CourseRela;
use app\v1\model\CourseType;
use app\v1\model\MoocCenter;
use app\v1\model\Chapter;
use app\v1\model\MoocUser;
use app\v1\model\Section;
use app\v1\model\Course as CourseModel;
use app\v1\model\Schedule;
use think\Db;
use think\Exception;
use think\Log;
use app\v1\model\Column;


class Course extends Core
{

    /**
     * 获取所有的慕课列表
     */
    public function index()
    {
        //文化馆ID
        $list = $this->_getList();
//        return (new MoocCenter())->getLastSql();
//        die;
        $num = $this->_getList(true);
        foreach ($list as $k => $v) {
            $list[$k] = $this->_prepareData($v);
        }

        return $this->ok(['list' => $list, 'num' => $num], 21101, '成功');
    }

    /**
     * 获取某个慕课数据
     *
     * ===============
     * 通过关联ID获取慕课或通过场馆iD,慕课ID获取课程信息
     *
     *
     * @param id 慕课ID
     * @param center_id 场馆ID
     * @param course_id 课程实际ID
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function read()
    {
        $where['c.delete_time'] = 0;
        $id = input('param.id', 2, 'intval');
        $center_id = input('param.center_id', 0, 'intval');
        $course_id = input('param.course_id', 0, 'intval');
        if ($id == 0) {
            $where['c.id'] = $course_id;
            $where['cc.center_id'] = $center_id;
        } else {
            $where['cc.id'] = $id;
        }
        $data = $this->_query($where)->find();
        if ($data) {
            $data = $this->_prepareData($data);
            return ok($data, 21102, '获取成功', 1);
        } else {
            return fail(21003, '获取失败', 1);
        }

    }

    private function _chapter_section(){
        $id = input('id', 26, 'intval');
        if ($id) {
            $course_id = CenterCourse::where('id', $id)->value('course_id', 0);
        } else {
            $course_id = input('course_id', 0, 'intval');
        }
        $chapter_section = (new Chapter())
            ->alias('c')
            ->join('__SECTION__ s', 'c.id = s.chapter_id and s.status = 1', 'left')
            ->where('c.course_id', $course_id)
            ->field('c.*,s.id as section_id,section_title,s.list_order as slist_order')
            ->order(['c.list_order' => 'asc', 's.list_order' => 'asc'])
            ->select();
        if ($chapter_section) {
            $chapter_section = \collection($chapter_section)->toArray();
        } else {
            $chapter_section = [];
        }

        return $chapter_section;
    }

    /**
     * 获取目录（后台）
     */
    public function catalog()
    {
        $chapter_section = $this->_chapter_section();
        $result = [];
        foreach ($chapter_section as $k => $v) {
            $result[$v['id']]['title'] = $v['chapter_title'];
            $result[$v['id']]['id'] = $v['id'];
            $result[$v['id']]['expand'] = true;
            $result[$v['id']]['children'][$v['section_id']] = [
                'id' => $v['section_id'],
                'title' => $v['section_title'],
                'children' => []
            ];
        }
        foreach ($result as $k => $v) {
            $result[$k]['children'] = array_values($v['children']);
        }
        //对象转数组
        return ok(array_values($result), 21113, '获取目录成功');
    }

    /**
     * 获取目录（老师个人中心）
     */
    public function getCatalog()
    {
        $chapter_section = $this->_chapter_section();
        $result = [];
        foreach ($chapter_section as $k => $v) {
               $item = ['name'=>$v['chapter_title'],'id'=>$v['id'],'pid'=>0,'is_parent'=>true];
            if(!in_array($item,$result)){
                $result[] = $item;
            }
            $result[] = ['name'=>$v['section_title'],'id'=>$v['section_id'],'pid'=>$v['id']];
        }

        return ok($result, 21113, '获取目录成功');
    }

    /**
     * 获取目录（课程详情页面,课程播放页面）
     */
    public function getDirectory(){
        $id = input('id', 26, 'intval');
        if ($id) {
            $course_id = CenterCourse::where('id', $id)->value('course_id', 0);
        } else {
            $course_id = input('course_id', 0, 'intval');
        }

        //身份校验
        $msg = verify();
        if ($msg['status'] == 0) {
            return $msg;
        } else {
            $user_id = $msg['data']['user_id'];
        }

//        $user_id = 24;

        //获取此用户所学此课程的进度
        $more = (new Schedule())->where(['user_id'=>$user_id,'course_id'=>$course_id])->value('more');
        $more = json_decode($more);
        $section_ids = [];
        foreach($more as $section=>$current_time){
            $section_ids[] = $section;
        }

        $chapter_section = (new Chapter())
            ->alias('c')
            ->join('__SECTION__ s', 'c.id = s.chapter_id and s.status = 1', 'left')
            ->where('c.course_id', $course_id)
            ->field('c.*,s.id as section_id,section_title,s.video_main,s.list_order as slist_order')
            ->order(['c.list_order' => 'asc', 's.list_order' => 'asc'])
            ->select();
        if ($chapter_section) {
            $chapter_section = \collection($chapter_section)->toArray();
        } else {
            $chapter_section = [];
        }
        $result = [];
        foreach ($chapter_section as $k => $v) {
            $result[$v['id']]['unit'] = $v['chapter_title'];
            if($v['section_id'] !== null){
                if(in_array($v['section_id'],$section_ids)){
                    $result[$v['id']]['node'][$v['section_id']] = [
                        'title' => $v['section_title'],
                        'url'=>$v['video_main'],
                        'status'=>'最近学习'
                    ];
                }else{
                    $result[$v['id']]['node'][$v['section_id']] = [
                        'title' => $v['section_title'],
                        'url'=>$v['video_main'],
                        'status'=>'开始学习'
                    ];
                }

            }
        }

        foreach ($result as $k => $v) {
            if(array_key_exists('node',$result[$k])){
                $result[$k]['node'] = array_values($v['node']);
            }
        }
        //对象转数组
        return ok(array_values($result), 21113, '获取目录成功');
    }

    /**
     * 创建慕课
     * @param course_title 标题
     * @param course_type_id 类型ID
     * @param cover_img 封面图
     * @param content 内容
     * @param open_status 开放状态 0 不开放  1 限时开放  2 开放
     * @param start_time 开始时间【限时开放时必须】
     * @param end_time 结束时间【限时开放时必须】
     * @param total_time 总时长
     * @param chapter 课程章数
     * @param section 每章节数
     */
    public function add()
    {
//        //用户ID 13 测试用例
//        $_GET['user_token'] = $token = 'e041209f65ee41db866c83471f28826550e188be';
//        $_GET['timestamp'] = $timestamp = time();
//        $salt = 'GpV2V';
//        $url = 'v1/course/add';
//        $_GET['sign'] = encrypt_key([$token, $url, $timestamp, $salt], '');
//        $_GET['course_title'] = '测试慕课123456789';
//        $_GET['course_type_id'] = '4';
//        $_GET['cover_img'] = 'http://';
//        $_GET['content'] = '测试慕课123测试慕课123测试慕课123测试慕课123测试慕课123测试慕课123测试慕课123';
//        $_GET['open_status'] = '1';
//        $_GET['start_time'] = strtotime('-1 month');
//        $_GET['end_time'] = strtotime('+1 month');
//        $_GET['chapter'] = 2;
//        $_GET['section'] = 4;
//        //超星馆测试用例
//        $_GET['center_token'] = $token = 'd772d5c2ca2e10a36c6dbb3acae6850538ad0704';
//        $_GET['timestamp'] = $timestamp = time();
//        $salt = '8FrkI';
//        $url = 'v1/course/add';
//        $_GET['sign'] = encrypt_key([$token, $url, $timestamp, $salt], '');
//        $_GET['course_title'] = '测试慕课5';
//        $_GET['center_id'] = 14;
//        $_GET['course_type_id'] = '4';
//        $_GET['cover_img'] = '测试慕课1测试慕课1测试慕课1测试慕课1测试慕课1测试慕课1测试慕课1';
//        $_GET['content'] = '测试慕课1测试慕课1测试慕课1测试慕课1测试慕课1测试慕课1测试慕课1';
//        $_GET['open_status'] = '1';
//        $_GET['start_time'] = strtotime('-1 month');
//        $_GET['end_time'] = strtotime('+1 month');
//        //普通馆测试用例
//        $_GET['center_token'] = $token = 'afbe52d7014168fa8b70f2a6c716a88c3e06c910';
//        $_GET['timestamp'] = $timestamp = time();
//        $salt = 'l5Y2Q';
//        $url = 'v1/course/add';
//        $_GET['sign'] = encrypt_key([$token, $url, $timestamp, $salt], '');
//        $_GET['course_title'] = '测试慕课5';
//        $_GET['center_id'] = 1;
//        $_GET['course_type_id'] = '4';
//        $_GET['cover_img'] = '测试慕课1测试慕课1测试慕课1测试慕课1测试慕课1测试慕课1测试慕课1';
//        $_GET['content'] = '测试慕课1测试慕课1测试慕课1测试慕课1测试慕课1测试慕课1测试慕课1';
//        $_GET['open_status'] = '1';
//        $_GET['start_time'] = strtotime('-1 month');
//        $_GET['end_time'] = strtotime('+1 month');

        //令牌校验
        $identity = verify();
        if ($identity['status'] == 0) {
            return $identity;
        }
        $identity = $identity['data'];

        //所有老师ID
        $teacher_ids = input('param.teacher_ids/a',[25]);
        if (empty($teacher_ids)) {
            return fail(21004, '必须指定老师', 1);
        }

        //获取实际添加到的场馆【超星馆可以指定文化馆】
        $center_id = $identity['center_id'];
        if ($identity['center_id'] == 1) {
            $center_id = input('param.center_id', 1, 'intval');
            $center = MoocCenter::get($center_id);
            if ($center == null) {
                return fail(21005, '设置的场馆不存在', 1);
            }
        }

        //数据初始化
        $param = $this->request->param();
        $param['create_time'] = time();
//        $param['update_time'] = time();
        $param['type'] = $identity['center_id'] == 1 ? 1 : $identity['type'];
//        $param['creator_id'] = $identity['center_id'] == 1 ? 1 : $identity['user_id'];
        $param['creator_id'] =  $identity['user_id'];

        // 分类校验
        $course_type_id = $this->request->param('course_type_id', 0);
        $typeRes = (new CourseType())->existType($course_type_id, $center_id);
        if (true !== $typeRes) {
            return fail(21006, $typeRes, 1);
        }

        /**  验证课程信息 */
        $result = $this->validate($param, 'Course.add');
        if (true !== $result) {
            return $this->fail(1000, $result, 1);
        } else {
            // 启动事务
            Db::startTrans();
            try {
                //添加课程
                $courseModel = new CourseModel();
                $allowFields = ['mooc_id', 'course_title', 'cover_img', 'content', 'total_time', 'create_time', 'type', 'creator_id', 'open_status', 'start_time', 'end_time','update_time'];
                if ($courseModel->allowField($allowFields)->save($param) <= 0) {
                    Db::rollback();
                    Log::error(['identity' => $identity, 'param' => $param, 'msg' => '添加课程失败']);
                    return $this->fail(21007, '添加课程失败', 1);
                }

                //添加场馆关联
                $center_course_data['course_id'] = $courseModel->id;
                $center_course_data['center_id'] = $center_id;
                $center_course_data['create_time'] = time();
                $centerCourseModel = new CenterCourse();
                if ($centerCourseModel->save($center_course_data) <= 0) {
                    Db::rollback();
                    Log::error(['identity' => $identity, 'param' => $param, 'center_course_data' => $center_course_data, 'msg' => '添加课程失败']);
                    return $this->fail(21008, '添加场馆关联失败', 1);
                }

                //添加类型关联
                $courseTypeModel = new CourseRela();
                $course_type_data['type'] = 1;
                $course_type_data['course_id'] = $courseModel->id;
                $course_type_data['other_id'] = $param['course_type_id'];
                $course_type_data['center_id'] = $center_id;
                $course_type_data['create_time'] = time();
                if ($courseTypeModel->save($course_type_data) <= 0) {
                    Db::rollback();
                    Log::error(['identity' => $identity, 'param' => $param, 'course_type_data' => $course_type_data, 'msg' => '添加类型关联失败']);
                    return $this->fail(21009, '添加类型关联失败', 1);
                }

                //添加老师关联
                $res = $this->_batch_teacher_add($teacher_ids, $courseModel->id, $center_id);
                if (empty($res)) {
                    Db::rollback();
                    Log::error(['identity' => $identity, 'param' => $param, 'teacher_ids' => $teacher_ids, 'msg' => '添加老师关联失败']);
                    return $this->fail(21010, '添加老师关联失败', 1);
                }

                //添加课程章节
                $chapter = $this->request->param('chapter', 0, 'intval');
                $chapters = [];
                for ($i = 0; $i < $chapter; $i++) {
                    $chapters[] = ['course_id' => $courseModel->id, 'chapter_title' => '第' . numToWord($i + 1) . '单元'];
                }
                $chapterModel = new Chapter();
                $chapterRes = $chapterModel->saveAll($chapters);

                //添加章节课时
                $section = $this->request->param('section', 0, 'intval');
                $sectionModel = new Section();
                $sections = [];
                foreach ($chapterRes as $v) {
                    for ($j = 0; $j < $section; $j++) {
                        $sections[] = ['chapter_id' => $v->id, 'section_title' => '第' . numToWord($j + 1) . '课时'];
                    }
                }
                $sectionModel->saveAll($sections);

                // 提交事务
                Db::commit();
                return $this->ok('', 21101, '成功', 1);
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                Log::error(['identity' => $identity, 'param' => $param, 'msg' => $e->getMessage()]);
                return $this->fail(21011, $e->getMessage(), 1);

            }
        }
    }

    /**
     * 修改慕课
     * @param id center和course关联id
     * @param course_title 标题
     * @param course_type_id 类型ID
     * @param cover_img 封面图
     * @param content 内容
     * @param open_status 开放状态 0 不开放  1 限时开放  2 开放
     * @param start_time 开始时间【限时开放时必须】
     * @param end_time 结束时间【限时开放时必须】
     * @param total_time 总时长
     */
    public function edit()
    {
//        //测试用例
//        $_GET['user_token'] = $token = 'a99ebc96a437f31656a169c31ef928f7c39d7ef6';
//        $_GET['timestamp'] = $timestamp = time();
//        $salt = 'l5Y2Q';
//        $url = 'v1/course/edit';
//        $_GET['sign'] = encrypt_key([$token, $url, $timestamp, $salt], '');
//        $_GET['id'] = 6;
//        $_GET['course_title'] = '测试慕课3-1';
//        $_GET['course_type_id'] = '3';
//        $_GET['cover_img'] = 'meinvdsdasdsdsdasdadsd';
//        $_GET['content'] = 'xinwen xinwenxinwenxinwen';
//        $_GET['open_status'] = '1';
//        $_GET['start_time'] = strtotime('-1 month');
//        $_GET['end_time'] = strtotime('+1 month');
//        $_GET['total_time'] = '3600';

        //签名校验
        $identity = verify();
        if ($identity['status'] == 0) {
            return $identity;
        }
        $identity = $identity['data'];
        if($identity['center_id'] == 1){
            $center_id = $this->request->param('center_id');
        }else{
            $center_id = $identity['center_id'];
        }

        //参数获取
        $param = $this->request->param();

        //权限校验
        $id = $this->request->param('id', 0, 'intval');
        $courseRes = $this->_verifyCourse($id, $identity);
        if ($courseRes['status'] == 0) {
            return $courseRes;
        }
        $course_id = $courseRes['data']['course_id'];

        //分类校验
        $course_type_id = $this->request->param('course_type_id', 0);
        $typeRes = (new CourseType())->existType($course_type_id, $center_id);
        if (true !== $typeRes) {
            return fail(21006, $typeRes, 1);
        }

        //数据校验
        $result = $this->validate($param, 'Course.edit');
        if ($result !== true) {
            return $this->fail(1000, $result, 1);
        }

        //开启事务
        Db::startTrans();
        try {
            //获取center_course关联

            //类型需要更新类型关联表
            if ($param['course_type_id'] != 0) {
                if (false === CourseRela::where(['type' => 1, 'course_id' => $course_id, 'center_id' => $center_id])->update(['other_id' => $param['course_type_id']])) {
                    Db::rollback();
                    return fail(21010, '修改分类失败', 1);
                }
            }

            //修改老师团队
            $teacher_ids = input('param.teacher_ids/a', []);
            $this->_edit_teacher($teacher_ids, $course_id, $center_id);

            //修改状态
            if (false === CenterCourse::where('id', $param['id'])->update(['status' => $param['status']])) {
                Db::rollback();
                return fail(21011, '修改状态失败', 1);
            }

            //更新数据
            $courseModel = new CourseModel();
            $allowFields = ['course_title', 'cover_img', 'total_time', 'content', 'open_status', 'start_time', 'end_time','update_time'];
            $param['update_time'] = time();
            unset($param['id']);
            if (false !== $courseModel->allowField($allowFields)->save($param, ['id' => $course_id])) {
                Db::commit();
                return $this->ok('', 20101, '编辑成功', 1);
            } else {
                Db::rollback();
                return $this->fail(21012, "编辑失败", 1);
            }
        } catch (Exception $e) {
            Db::rollback();
            Log::error(['param' => $param, 'msg' => $e->getMessage()]);
            return fail(21013, $e->getMessage(), 1);
        }
    }

    /**
     * 删除课程
     * @param id 课程场馆关联ID
     * @param ids 课程场馆关联ID数组
     *
     * @return array|bool
     */
    public function delete()
    {
//        //测试用例
//        $_GET['user_token'] = $token = 'a99ebc96a437f31656a169c31ef928f7c39d7ef6';
//        $_GET['timestamp'] = $timestamp = time();
//        $salt = 'l5Y2Q';
//        $url = 'v1/course/delete';
//        $_GET['sign'] = encrypt_key([$token, $url, $timestamp, $salt], '');
//        $_GET['ids'] = [2,3,4];

        //身份校验
        $identity = verify();
        if ($identity['status'] == 0) {
            return $identity;
        }
        $identity = $identity['data'];

        $ids = $this->request->param('ids/a', []);
        $id = $this->request->param('id', 0);
        if (!empty($ids)) {
            return $this->_doCourseDel($ids, $identity['center_id']);
        }
        if ($id != 0) {
            return $this->_doCourseDel([$id], $identity['center_id']);
        }
        return fail(21020, '删除失败');
    }

    /**
     * 推荐
     * @param id 课程场馆关联ID
     * @param ids 课程场馆关联ID数组
     * @param recommend 是否推荐  1 推荐 0 取消推荐
     */
    public function recommend()
    {
//        //测试用例
//        $_GET['center_token'] = $token = 'afbe52d7014168fa8b70f2a6c716a88c3e06c910';
//        $_GET['timestamp'] = $timestamp = time();
//        $salt = 'l5Y2Q';
//        $url = 'v1/course/recommend';
//        $_GET['sign'] = encrypt_key([$token, $url, $timestamp, $salt], '');
//        $_GET['ids'] = [2,3,4];
        $identity = verify();
        if ($identity['status'] == 0) {
            return $identity;
        }
        $identity = $identity['data'];
        if ($identity['type'] != 1) {
            return fail(21021, '没有操作权限', 1);
        }
        $id = $this->request->param('id', 0, 'intval');
        $ids = $this->request->param('ids/a', []);
        $recommend = $this->request->param('recommend', 1, 'intval');
        if ($id != 0) {
            $where['id'] = $id;
        } elseif (!empty($ids)) {
            $where['id'] = ['in', $ids];
        } else {
            return fail(21022, '条件必须指定', 1);
        }
        $where['center_id'] = $identity['center_id'];
        if (false === CenterCourse::where($where)->update(['recommend' => $recommend])) {
            return fail(21023, '推荐失败', 1);
        } else {
            return ok('', 21123, '推荐成功', 1);
        }
    }

    /**
     * 批量修改慕课分类
     * @param id 慕课场馆关联ID
     * @param ids 慕课场馆关联ID数组
     * @param type_id 类型ID
     *
     * @return array|bool
     */
    public function change_type()
    {
//        //测试用例
//        $_GET['center_token'] = $token = 'afbe52d7014168fa8b70f2a6c716a88c3e06c910';
//        $_GET['timestamp'] = $timestamp = time();
//        $salt = 'l5Y2Q';
//        $url = 'v1/course/change_type';
//        $_GET['sign'] = encrypt_key([$token, $url, $timestamp, $salt], '');
//        $_GET['ids'] = [2,3,4];
//        $_GET['type_id'] = 3;
        //身份校验
        $identity = verify();
        if ($identity['status'] == 0) {
            return $identity;
        }
        $identity = $identity['data'];
        if ($identity['type'] != 1) {
            return fail(21021, '没有操作权限', 1);
        }

        //确定场馆
        if ($identity['center_id'] == 1) {
            $center_id = $this->request->param('center_id', 1, 'intval');
        } else {
            $center_id = $identity['center_id'];
        }

        //验证分类
        $type = $this->request->param('type_id', 0, 'intval');
        $num = CourseType::where(['id' => $type, 'center_id' => $center_id, 'status' => 1])->count(1);
        if ($num == 0) {
            return fail(21023, '分类不存在或不可用');
        }

        //获取可修改课程
        $id = $this->request->param('id', 0, 'intval');
        $ids = $this->request->param('ids/a', []);
        $where['center_id'] = $center_id;
        if ($id != 0) {
            $where['id'] = $id;
            $ids = [$id];
        } elseif (!empty($ids)) {
            $where['id'] = ['in', $ids];
        } else {
            return fail(21022, '条件必须指定', 1);
        }
        //可修改的课程
        $access_ids = CenterCourse::where($where)->column('course_id', 'id');


        //不可修改的课程ID
        $result['no_access'] = array_diff($ids, array_keys($access_ids));
        //所有课程ID
        $result['all_id'] = $ids;

        //修改关联
        $courseTypeModel = new  CourseRela();
        foreach ($access_ids as $id => $course_id) {
            $type_id = $courseTypeModel->where(['type' => 1, 'course_id' => $course_id, 'center_id' => $center_id])->value('id');
            if ($type_id) {
                if (false === $courseTypeModel->where('id', $type_id)->update(['other_id' => $type])) {
                    $result['fail'][] = $id;
                } else {
                    $result['success'][] = $id;
                }
            } else {
                $res = $courseTypeModel->save(['type' => 1, 'course_id' => $course_id, 'center_id' => $center_id, 'other_id' => $type]);
                if ($res > 0) {
                    $result['success'][] = $id;
                } else {
                    $result['fail'][] = $id;
                }
            }
        }

        return ok($result, 21122, '改变分类成功');
    }

    /**
     * 获取某个课程的老师团队
     * @param id 课程场馆关联ID
     * @param course_id 课程ID
     * @param center_id 场馆ID
     * @return array
     */
    public function teacher_index()
    {
        $id = input('param.id', 0, 'intval');
        if ($id != 0) {
            $center_course = CenterCourse::where(['id' => $id])->find();
            if ($center_course == null) {
                return fail(21028, '获取失败', 1);
            }
            $course_id = $center_course['course_id'];
            $center_id = $center_course['center_id'];
        } else {
            $course_id = input('param.course_id', 0, 'intval');
            $center_id = input('param.center_id', 0, 'intval');
        }
        $teachers = (new CourseRela())
            ->alias('cr')
            ->join('__MOOC_USER__ u', 'cr.other_id = u.id  and u.type = 2')
            ->field('u.id,u.nick_name,u.company,u.department,u.avatar,u.profile')
            ->where(['cr.course_id' => $course_id, 'cr.center_id' => $center_id,'cr.type'=>3])
            ->select();
        return ok($teachers, 21123, '获取老师列表成功');
    }

    /**
     * 课程单独添加老师
     * @param teacher_id 老师ID
     * @param id 课程关联ID
     */
    public function add_teacher()
    {
//        //测试用例
//        $_GET['user_token'] = $token = 'a99ebc96a437f31656a169c31ef928f7c39d7ef6';
//        $_GET['timestamp'] = $timestamp = time();
//        $salt = 'l5Y2Q';
//        $url = 'v1/course/add_teacher';
//        $_GET['sign'] = encrypt_key([$token, $url, $timestamp, $salt], '');

        //参数初始化
        $teacher_id = $this->request->param('param.teacher_id', 0, 'intval');
        $id = $this->request->param('param.id', 0, 'intval');

        //老师课程校验
        $res = $this->_verifyTeacherCourse($teacher_id, $id);
        if ($res['status'] == 0) {
            return $res;
        }
        $data = $res['data'];

        //老师校验
        $teacherData = ['type' => 3, 'course_id' => $data['course_id'], 'center_id' => $data['center_id'], 'other_id' => $teacher_id];
        $num = CourseRela::where($teacherData)->count(1);
        if ($num == 0) {
            $courseTeacherModel = new CourseRela();
            $teacherData['create_time'] = time();
            $courseTeacherModel->insert($teacherData);
            return $this->ok('', 21121, '成功');
        } else {
            return $this->fail(21023, '老师已添加，请勿重复操作');
        }
    }

    /**
     * 删除老师
     * @param teacher_id
     * @param course_id
     */
    public function del_teacher()
    {
//        //测试用例
//        $_GET['user_token'] = $token = 'a99ebc96a437f31656a169c31ef928f7c39d7ef6';
//        $_GET['timestamp'] = $timestamp = time();
//        $salt = 'l5Y2Q';
//        $url = 'v1/course/del_teacher';
//        $_GET['sign'] = encrypt_key([$token, $url, $timestamp, $salt], '');

        //参数初始化
        $teacher_id = $this->request->param('param.teacher_id', 0, 'intval');
        $id = $this->request->param('param.id', 0, 'intval');

        //老师课程校验
        $res = $this->_verifyTeacherCourse($teacher_id, $id);
        if ($res['status'] == 0) {
            return $res;
        }
        $data = $res['data'];

        $where = ['type' => 3, 'course_id' => $data['course_id'], 'center_id' => $data['center_id'], 'other_id' => $teacher_id];
        $result = CourseRela::where($where)->delete();
        if ($result > 0) {
            return $this->ok($result, 21101, '成功');
        } else {
            return $this->fail(21001, '失败');
        }
    }

    /**
     * 添加章
     *
     * @param id 课程场馆关联ID
     * @param chapter_title 章节标题
     */
    public function add_chapter()
    {
//        //测试用例
//        $_GET['user_token'] = $token = 'a99ebc96a437f31656a169c31ef928f7c39d7ef6';
//        $_GET['timestamp'] = $timestamp = time();
//        $salt = 'l5Y2Q';
//        $url = 'v1/course/add_chapter';
//        $_GET['sign'] = encrypt_key([$token, $url, $timestamp, $salt], '');

        //身份校验
        $identity = verify();
        if ($identity['status'] == 0) {
            return $identity;
        }
        $identity = $identity['data'];

        //课程校验
        $id = $this->request->param('id', 0, 'intval');
        $courseRes = $this->_verifyCourse($id, $identity);
        if ($courseRes['status'] == 0) {
            return $courseRes;
        }
        $course_id = $courseRes['data']['course_id'];

        //初始化入参
        $data['chapter_title'] = $this->request->param('chapter_title', '', 'trim');
        $data['course_id'] = $course_id;

        //入参校验
        $result = $this->validate($data, 'Chapter.add');
        if ($result !== true) {
            return $this->fail(1000, $result, 1);
        }

        //数据保存
        $chapterModel = new Chapter();
        $result = $chapterModel->insert($data);
        if ($result) {
            $chapter_id = $chapterModel->getLastInsID();
            $chapterModel->where('id', $chapter_id)->update(['list_order' => $chapter_id]);
            $info = $chapterModel->where(['id' => $chapter_id])->find();
            return $this->ok($info, 21101, '成功');
        } else {
            return $this->fail(21001, '失败');
        }
    }

    /**
     * 修改章
     * @param id 课程场馆关联ID
     * @param chapter 章节ID
     * @param chapter_title 章节标题
     * @param list_order 排序
     * @param status 状态
     */
    public function edit_chapter()
    {
//        //测试用例
        $_GET['user_token'] = $token = 'a99ebc96a437f31656a169c31ef928f7c39d7ef6';
        $_GET['timestamp'] = $timestamp = time();
        $salt = 'l5Y2Q';
        $url = 'v1/course/edit_chapter';
        $_GET['sign'] = encrypt_key([$token, $url, $timestamp, $salt], '');
        $_GET['chapter_title'] = '第一单元';
        $_GET['list_order'] = '1';
        $_GET['status'] = 1;

        //身份校验
        $identity = verify();
        if ($identity['status'] == 0) {
            return $identity;
        }
        $identity = $identity['data'];

        //参数初始化
//        $id = $this->request->param('id', 0, 'intval');
        $chapter = $this->request->param('chapter_id', 0, 'intval');

        //课程校验
//        $courseRes = $this->_verifyCourse($id, $identity);
//        if ($courseRes['status'] == 0) {
//            return $courseRes;
//        }
//        $course_id = $courseRes['data']['course_id'];

        //章节所有权校验
//        $chapterRes = $this->_verifyChapter($course_id, $chapter);
//        if ($chapterRes !== true) {
//            return $chapterRes;
//        }

        //初始化入参
        $param = $this->request->param();

        /**  验证章信息 */
        $result = $this->validate($param, 'Chapter.edit');
        if ($result !== true) {
            return $this->fail(1000, $result, 1);
        }

        //修改
        $result = (new Chapter())->allowField(['chapter_title', 'list_order', 'status'])->save($param, ['id' => $chapter]);
        if ($result !== false) {
            return $this->ok('', 21101, '成功');
        } else {
            return $this->fail(21001, '失败');
        }
    }

    /**
     * 删除章
     *
     * @param id 课程场馆关联ID
     * @param chapter 章节ID
     */
    public function del_chapter()
    {
//         //测试用例
//        $_GET['user_token'] = $token = 'a99ebc96a437f31656a169c31ef928f7c39d7ef6';
//        $_GET['timestamp'] = $timestamp = time();
//        $salt = 'l5Y2Q';
//        $url = 'v1/course/del_chapter';
//        $_GET['sign'] = encrypt_key([$token, $url, $timestamp, $salt], '');

        //参数初始化
        $id = $this->request->param('id', 0, 'intval');
        $chapter = $this->request->param('chapter_id', 0, 'intval');

        //身份校验
        $identity = verify();
        if ($identity['status'] == 0) {
            return $identity;
        }
        $identity = $identity['data'];

        //课程校验
        $courseRes = $this->_verifyCourse($id, $identity);
        if ($courseRes['status'] == 0) {
            return $courseRes;
        }
        $course_id = $courseRes['data']['course_id'];

        //章节所有权校验
        $chapterRes = $this->_verifyChapter($course_id, $chapter);
        if ($chapterRes !== true) {
            return $chapterRes;
        }

        //删除数据
        $result = (new Chapter())->where('id', $chapter)->delete();
        if ($result > 0) {
            return $this->ok('', 21101, '成功');
        } else {
            return $this->fail(21001, '失败');
        }
    }

    /**
     * 获取某课时
     * @param id 课时ID
     * @return array
     */
    public function read_section()
    {
        $id = input('id', 0, 'intval');
        $section = Section::where('id', $id)->find();
        if ($section) {
            return ok($section, 21111, '获取课时成功');
        } else {
            return fail(21011, '获取课时失败');
        }
    }

    /**
     * 添加节
     *
     * @param chapter_id 章ID
     * @param section_title 节标题
     * @param list_order 排序
     * @param video_main 主视频地址
     * @param video_backup 备选视频地址(可选)
     * @param video_time 视频时长
     */
    public function add_section()
    {
//        //测试用例
//        $_GET['user_token'] = $token = 'a99ebc96a437f31656a169c31ef928f7c39d7ef6';
//        $_GET['timestamp'] = $timestamp = time();
//        $salt = 'l5Y2Q';
//        $url = 'v1/course/add_section';
//        $_GET['sign'] = encrypt_key([$token, $url, $timestamp, $salt], '');
//        $_GET['chapter_id'] = 3;
//        $_GET['section_title'] = '第一课时';
//        $_GET['video_main'] = '第一课时';
//        $_GET['video_time'] = '3600';
        //身份校验
        $identity = verify();
        if ($identity['status'] == 0) {
            return $identity;
        }
        $identity = $identity['data'];

        //章节校验
        $chapter = $this->request->param('chapter_id', 0, 'intval');
        $secRes = $this->_verifySectionChapter($chapter, $identity['center_id']);
        if ($secRes !== true) {
            return $secRes;
        }

        //请求数据获取
        $param = $this->request->param();

        /**  验证节信息 */
        $result = $this->validate($param, 'Section.add');
        if ($result !== true) {
            return $this->fail(1000, $result, 1);
        } else {
            //添加
            $sectionModel = new Section();
            $allowFields = ['chapter_id', 'section_title', 'list_order', 'video_main', 'video_backup', 'video_time', 'status'];
            unset($param['id']);
            $result = $sectionModel->allowField($allowFields)->save($param);
            if ($result > 0) {
                $section_id = $sectionModel->id;
                $sectionModel->where('id', $section_id)->update(['list_order' => $section_id]);
                $section = $sectionModel->where('id', $section_id)->find();
                return $this->ok($section, 21101, '成功');
            } else {
                return $this->fail(21001, '失败');
            }
        }
    }

    /**
     * 修改节
     * @param id 节ID
     * @param chapter_id 章ID
     * @param section_title 节标题
     * @param list_order 排序
     * @param video_main 主视频地址
     * @param video_backup 备选视频地址(可选)
     * @param video_time 视频时长
     */
    public function edit_section()
    {
//        //测试用例
//        $_GET['user_token'] = $token = 'a99ebc96a437f31656a169c31ef928f7c39d7ef6';
//        $_GET['timestamp'] = $timestamp = time();
//        $salt = 'l5Y2Q';
//        $url = 'v1/course/edit_section';
//        $_GET['sign'] = encrypt_key([$token, $url, $timestamp, $salt], '');
//        $_GET['chapter_id'] = 3;
//        $_GET['id'] = 1;
//        $_GET['section_title'] = '第二课时';
//        $_GET['video_main'] = '第一课时';
//        $_GET['video_time'] = '3600';
        //身份校验
        $identity = verify();
        if ($identity['status'] == 0) {
            return $identity;
        }
        $identity = $identity['data'];

        //章节校验
        $chapter = $this->request->param('chapter_id', 0, 'intval');
        $secRes = $this->_verifySectionChapter($chapter, $identity['center_id']);
        if ($secRes !== true) {
            return $secRes;
        }

        //节校验
        $id = $this->request->param('id', 0, 'intval');
        $sectionModel = new Section();
        $num = $sectionModel->where('id', $id)->count(1);
        if ($num == 0) {
            return fail(26031, '节不存在', 1);
        }


        //参数初始化
        $param = $this->request->param();

        /**  验证节信息 */
        $result = $this->validate($param, 'Section.edit');
        if ($result !== true) {
            return $this->fail(1000, $result, 1);
        } else {
            if($identity['type'] == 2 && !empty($param['video_main'])){  //前台用户上传视频
                $data = [
                    'center_id'=>$identity['center_id'],
                    'title'=>$param['title'],
                    'user_id'=>$identity['user_id'],
                    'thumb'=>$param['thumb'],
                    'url'=>$param['video_main'],
                    'type'=>2,
                    'status'=>1,
                    'create_time'=>time()
                ];
                $videoModel = new Videos();
                $videoModel->allowField(true)->validate(true)->isUpdate(false)->save($data);
            }
            $allowFields = ['section_title', 'list_order', 'video_main', 'video_time', 'status'];
            if (false === $sectionModel->allowField($allowFields)->save($param, ['id' => $id])) {
                return fail(21001, '失败', 1);
            } else {
                return ok('', 21101, '成功', 1);
            }
        }
    }

    /**
     * 收藏课程
     * @param course_id 课程id
     * @param user_token 用户令牌
     */
     public function collect(){
         $_GET['user_token'] = '4800970a86649ca1ce6215864cc3df8cec0319a9';
         $_GET['timestamp'] = time();
         $salt = 'Fkg7e';
         $_GET['sign'] = encrypt_key(['v1/my/mycollect', $_GET['timestamp'], $_GET['user_token'], $salt], '');
         $_GET['id'] = 7;

         $user_token = input('param.user_token','','trim');
         $course_id = input('param.id',0,'intval');

         //令牌校验
         $tokenRes = checkUserToken($user_token);
         if($tokenRes != true){
             return $tokenRes;
         }

         if(empty($course_id)){
             return $this->fail(10002,'课程id必须');
         }

         //数据整理
         $user = (new MoocUser())->where(['user_token'=>$user_token])->find();
         $is_exist = (new CenterCourse())->where(['course_id'=>$course_id,'center_id'=>$user['center_id']])->find();
         if($is_exist == null){
             return $this->fail(20010,'课程不属于此场馆,不能收藏');
         }

         if((new Collect())->where(['user_id'=>$user['id'],'course_id'=>$course_id])->find() != null){
             return $this->fail(20030,'已收藏');
         }
         $data = [
             'center_id'=>$user['center_id'],
             'course_id'=>$course_id,
             'user_id'=>$user['id'],
             'create_time'=>time()
         ];

         $res = (new Collect())->save($data);
         if($res){
             return $this->ok('',10010,'添加收藏成功');
         }else{
             return $this->fail(10001,'添加收藏失败');
         }
     }

    /**
     * 删除节
     * @param ids 节ID数组
     * @param chapter_id 章ID
     */
    public function del_section()
    {
//        //测试用例
//        $_GET['user_token'] = $token = 'a99ebc96a437f31656a169c31ef928f7c39d7ef6';
//        $_GET['timestamp'] = $timestamp = time();
//        $salt = 'l5Y2Q';
//        $url = 'v1/course/del_section';
//        $_GET['sign'] = encrypt_key([$token, $url, $timestamp, $salt], '');
//        $_GET['chapter_id'] = 3;
//        $_GET['id'] = 1;
        //身份校验
        $identity = verify();
        if ($identity['status'] == 0) {
            return $identity;
        }

        //删除
        $ids = $this->request->param('ids/a', []);
        $result = (new Section())->where(['id' => ['in', $ids]])->delete();
        if ($result > 0) {
            return $this->ok('', 21101, '成功');
        } else {
            return $this->fail(21001, '失败');
        }

    }

    /**
     * 获取慕课列表或数量
     * @param bool $isCount true 获取数量 false 获取列表
     * @return array|int|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function _getList($isCount = false)
    {
        $centerId = input('param.center_id',0,'intval');     //真实场馆id
        $center_id = input('param.center_id', 0, 'intval');  //超星馆下筛选条件 场馆id
        $creator_id = input('param.creator_id', 0, 'intval');
        $creator_type = input('param.creator_type', 0, 'intval');
        $other_id = input('param.other_id', 0, 'intval');  //分类id 或者栏目id 或者 专题id
        $type = input('param.type',1,'intval');   //类型   1分类，4栏目，2专题
        $title = input('param.title', '');
        $page = input('param.page', 0, 'intval');
        $len = input('param.len', 10, 'intval');
        $has_delete = input('param.has_delete', 0, 'intval');
        $status = input('param.status', -1, 'intval');  //前台必传1
        $ord = input('param.order',0,'intval');  // 1近期热播  2评分
        $open_status = input('param.open_status',0,'intval'); //不限时间 open_status
        $recommend = input('param.recomment',0,'intval');
        $type_id = input('param.type_id',0,'intval');  //分类，栏目或专题id

        $where = [];
        $order = [];
        $where['ct.type'] = $type;
        $type_ids = [];
        if($type == 1){
            $type_ids = (new CourseType())->where(['parent_id'=>$type_id])->column('id');
            array_push($type_ids,$type_id);
        }else if($type == 2){
            $type_ids = (new Column())->where(['parent_id'=>$type_id])->column('id');
            array_push($type_ids,$type_id);
        }else{
            array_push($type_ids,$type_id);
        }
        if($centerId && $centerId !=1) $where = ['cc.center_id' => $centerId];
        if ($status != -1) $where = ['cc.status' => $status];
        if ($center_id != 0) $where['cc.center_id'] = $center_id;
        if ($creator_id != 0) {
            $where['c.type'] = $creator_type;
            $where['c.creator_id'] = $creator_id;
        }
        if ($other_id != 0) $where['ct.other_id'] = $other_id;
        if ($title != '') $where['c.course_title'] = ['like', '%' . $title . '%'];
        if ($has_delete == 0) $where['c.delete_time'] = 0;
        if($ord == 1) $order = ['cc.play_num','desc'];
        if($ord == 2) $order = ['score','desc'];
        if($open_status != 0) $where['c.open_status'] = 2;
        if($recommend) $where['recomment'] = 1;
        $query = $this->_query($where,$order);
        if ($isCount) {
            return $query->count(1);
        } else {
            $result = $query->order(['cc.recommend' => 'desc', 'cc.create_time' => 'desc'])->page($page, $len)->select();
            if ($result) {
                return \collection($result)->toArray();
            } else {
                return [];
            }
        }
    }

    /**
     * 构造慕课查询器
     * @param $where
     * @return CourseModel
     */
    private function _query($where,$order=[])
    {
        //场馆ID关联时
        return $query = (new CourseModel())
            ->alias('c')
            ->join('__CENTER_COURSE__ cc', 'c.id=cc.course_id', 'left')
//            ->join('__COURSE_RELA__ ct', 'c.id=ct.course_id and ct.type = 1 and ct.center_id = cc.center_id', 'left')
            ->join('__COURSE_RELA__ ct', 'c.id=ct.course_id  and ct.center_id = cc.center_id', 'left')
            ->join('__COMMENT__ cm', 'c.id=cm.course_id and cm.center_id = cc.center_id', 'left')
            ->join('__BAOMING__ b','c.id =b.course_id and b.center_id = cc.center_id ','left')
            ->field('c.*,cc.id as real_id,cc.recommend,cc.create_time as add_time,cc.center_id,cc.play_num,cc.status, ct.other_id,count(cm.id) as comment_num,avg(cm.practical_score+cm.concise_score+cm.clear_score)/3 as score,count(b.id) as baoming_num')
            ->where($where)
            ->order($order)
            ->group('c.id,cc.center_id');
    }

    /**
     * 查询结果整理
     * @param $data
     * @return mixed
     * @throws \think\exception\DbException
     */
    private function _prepareData($data)
    {
        $data['course_id'] = $data['id'];
        $data['id'] = $data['real_id'];
        unset($data['real_id']);
        $data['cover_img'] = get_image_url($data['cover_img']);
        $data['cover_video'] = get_image_url($data['cover_video']);
        $data['create_time'] = $data['add_time'];
        unset($data['add_time']);
        $data['content'] = $data['content'] ? $data['content'] : '';
        $center = MoocCenter::get($data['center_id']);
        $data['center_name'] = $center ? $center['center_name'] : '';
        $data['score'] = $data['score'] == null ? 0 : $data['score'];
        $type = CourseType::get($data['other_id']);
        $data['course_type'] = $type ? $type['course_type'] : '';
        $data['course_type_tree'] = $this->_getTypeTree($data['other_id']);
        if ($data['type'] == 1) {
            $data['creator'] = MoocCenter::where(['id' => $data['creator_id']])->value('center_name', '');
            $data['creator_center_id'] = $data['creator_id'];
        } else {
            $user = MoocUser::where(['id' => $data['creator_id']])->find();
            if($user){
                $data['creator'] = $user['nick_name'];
                $data['creator_center_id'] = $user['center_id'];
            }else{
                $data['creator'] = '';
                $data['creator_center_id'] = 0;
            }
        }
        return $data;
    }


    /**
     * 获取分类树  根据子节点获取分类树
     * @param $id 分类id
     * @return array|bool
     */
    public function _getTypeTree($id,$result = []){
        $type = CourseType::get($id);
        if($type == null){
            return '';
        }
        $type = $type->toArray();
        $result[] = $type;
        if($type['parent_id'] == 0){
            array_reverse($result);
            $count = count($result);
            if($count == 1){
                return $result;
            }else{
                $is_first = true;
                $newResult = [];
                foreach ($result as $key=>$item){
                    if($is_first){
                        $newResult = $item;
                        $is_first = false;
                    }else{
                        $result[$key]['children'] = $newResult;
                        $newResult = $result[$key];
                    }
                }
                return $newResult;
            }
        }else{
            return $this->_getTypeTree($type['parent_id'],$result);
        }
    }

    /**
     * 校验老师课程
     *
     * @param $teacher_id 老师ID
     * @param $id 课程场馆关联ID
     * @return array|bool
     */
    private function _verifyTeacherCourse($teacher_id, $id)
    {
        //身份校验
        $identity = verify();
        if ($identity['status'] == 0) {
            return $identity;
        }
        $identity = $identity['data'];

        //老师校验
        if ($identity['center_id'] != 1) {
            $teacherWhere['center_id'] = $identity['center_id'];
        }
        $teacherWhere['id'] = $teacher_id;
        $teacherWhere['type'] = 2;
        $num = MoocUser::where($teacherWhere)->count(1);
        if ($num == 0) {
            return fail(21021, '老师不存在或没有使用该老师的权限');
        }

        //课程校验
        $courseRes = $this->_verifyCourse($id, $identity);
        if ($courseRes['status'] == 0) {
            return $courseRes;
        }
        $course_id = $courseRes['data']['course_id'];

        return ok(['course_id' => $course_id, 'center_id' => $identity['center_id']], 21122, '验证通过');
    }

    /**
     * 删除课程
     * @param $ids 关联id数组
     * @param $center_id 场馆ID
     * @return array
     */
    private function _doCourseDel($ids, $center_id)
    {
        $where = ['cc.id' => ['in', $ids]];
        if ($center_id != 1) {
            $where['cc.center_id'] = $center_id;
        }
        $res = (new CourseModel())
            ->alias('c')
            ->join('__CENTER_COURSE__ cc', 'c.id=cc.course_id')
            ->where($where)
            ->update(['c.delete_time' => time()]);
        if (false === $res) {
            return fail(21020, '删除失败');
        } else {
            return ok('', 21120, '删除成功');
        }
    }

    /**
     * 课程校验
     * @param $id 课程关联ID
     * @param $identity 身份校验结果
     * @return array
     */
    private function _verifyCourse($id, $identity)
    {
        //此处应根据创建者进行权限验证
        if ($identity['type'] == 1) {
            //后台身份登陆
            $where = ['cc.id' => $id, 'c.type' => 1, 'c.creator_id' => $identity['center_id']];
        } else {
            //前台老师身份登陆·
            $where = ['cc.id' => $id, 'c.type' => 2, 'c.creator_id' => $identity['user_id']];
        }

        $centerCourseModel = new CenterCourse();
        $course = $centerCourseModel
            ->alias('cc')
            ->join('__COURSE__ c', 'c.id = cc.course_id')
            ->where($where)
            ->field('cc.id, cc.course_id, c.type, c.creator_id')
            ->find();
        if ($course) {
            if ($identity['type'] == 1) {
                if ($course['type'] == 1 && $course['creator_id'] != $identity['center_id']) {
                    return fail(21008, '慕课不存在或用户没有操作权限', 1);
                }
            }
            return ok(['course_id' => $course['course_id'], 'id' => $course['id']], 21122, '校验成功');
        } else {
            return fail(21009, '慕课不存在或用户没有操作权限', 1);
        }
    }

    /**
     * 章校验
     * @param $course_id 课程ID
     * @param $chapter 章节ID
     * @return array|bool
     */
    private function _verifyChapter($course_id, $chapter)
    {
        $num = Chapter::where(['course_id' => $course_id, 'id' => $chapter])->count(1);
        if ($num == 0) {
            return fail(21022, '章节不存在或没有操作权限');
        }
        return true;
    }

    /**
     * 节校验
     * @param $chapter 章ID
     * @param $center_id 场馆ID
     * @return array|bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function _verifySectionChapter($chapter, $center_id)
    {
        //章节校验
        $chapterModel = new Chapter();
        $chapter = $chapterModel->where('id', $chapter)->find();
        if ($chapter == null) {
            return fail(26029, '章不存在', 1);
        }

        //课程校验
        if ($center_id != 1) {
            $where['course_id'] = $chapter['course_id'];
            $where['center_id'] = $center_id;
            $num = CenterCourse::where($where)->count(1);
            if ($num == 0) {
                return fail(26030, '没有操作权限', 1);
            }
        }
        return true;
    }

    /**
     * 批量添加老师
     * @param $ids 老师ID
     * @param $course_id 课程ID
     * @param $center_id 场馆ID
     * @return array|false
     * @throws \Exception
     */
    private function _batch_teacher_add($ids, $course_id, $center_id)
    {
        //去重
        $ids = array_keys(array_flip($ids));
        $course_teacher_datas = [];
        $teacherModel = new CourseRela();
        foreach ($ids as $id) {
            $num = $teacherModel->where(['type' => 3, 'course_id' => $course_id, 'other_id' => $id, 'center_id' => $center_id])->count(1);
            if ($num == 0) {
                $course_teacher_data['type'] = 3;
                $course_teacher_data['course_id'] = $course_id;
                $course_teacher_data['other_id'] = $id;
                $course_teacher_data['create_time'] = time();
                $course_teacher_data['center_id'] = $center_id;
                $course_teacher_datas[] = $course_teacher_data;
            }
        }
        return $teacherModel->saveAll($course_teacher_datas);
    }

    /**
     * 编辑老师
     * @param $teacher_ids 老师Id数组
     * @param $course_id 课程ID
     * @param $center_id 场馆Id
     * @return array|false
     * @throws \Exception
     */
    private function _edit_teacher($teacher_ids, $course_id, $center_id)
    {
        //当前老师团队
        $old_teacher_ids = CourseRela::where(['type' => 3, 'course_id' => $course_id, 'center_id' => $center_id])->column('other_id');

        //删除的
        $del_ids = array_diff($old_teacher_ids, $teacher_ids);
        CourseRela::where(['type' => 3, 'course_id' => $course_id, 'center_id' => $center_id, 'other_id' => ['in', $del_ids]])->delete();

        //新增的
        $add_ids = array_diff($teacher_ids, $old_teacher_ids);
        return $this->_batch_teacher_add($add_ids, $course_id, $center_id);
    }
}
