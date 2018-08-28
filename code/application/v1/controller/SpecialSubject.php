<?php
/**
 * Created by PhpStorm.
 * User: zhangchun
 * Date: 2018/6/28
 * Time: 14:07
 */

namespace app\v1\controller;

use app\v1\model\CenterCourse;
use app\v1\model\CourseRela;
use app\v1\model\MoocCenter;
use app\v1\model\SpecialSubject as SpeSubModel;
use app\v1\model\Course;
use app\v1\model\Baoming;
class SpecialSubject extends Core
{
    /**
     * 专题列表
     * @param center_token 场馆令牌
     * @param title  标题
     * @param create_time  时间
     * @param page 页数
     * @param len  长度
     * @return array|bool
     */
    public function index()
    {
        $_GET['user_token'] = 'cd69089bea5af15192e10ce17be7d4939eb02bb7';
        $_GET['timestamp'] = time();
        $salt = 'ttVm5';
        $_GET['sign'] = encrypt_key(['v1/specialsubject/index', $_GET['timestamp'], $_GET['user_token'], $salt], '');

        $title = input('param.title');
        $create_time = input('param.create_time');
        $page = input('param.page', 1, 'intval');
        $len = input('param.len', 10, 'intval');
        $all = input('param.all',-1,'intval');     //超星馆后台获取包括其他管的全部数据需要传all  其他馆可不传

        //身份校验
        $msg = verify();
        if ($msg['status'] == 0) {
            return $msg;
        } else {
            $center_id = $msg['data']['center_id'];
        }

        $where = [];
        if (!empty($title)) {
            $where['s.title'] = ['like', "%$title%"];
        }

        if (!empty($create_time)) {
            $where['s.create_time'] = ['>=', $create_time];
        }

        if($all != -1){
            if($center_id != 1){
                $where['s.center_id'] = $center_id;
            }
        }else{
            $where['s.center_id'] = $center_id;
        }

        //数据获取
        $spe_sub = new SpeSubModel();
        $join[] = ['mooc_center mc', 'mc.id=s.center_id'];
        $field = 's.*,mc.center_name';
        $speList = $spe_sub->alias('s')->join($join)->where($where)->field($field)->order(['mc.id asc','s.list_order asc'])->page($page, $len)->select();
        $num = $spe_sub->alias('s')->join($join)->where($where)->count(1);

        foreach($speList as $key=>$item){
//            $speList[$key]['uploder'] = (new MoocCenter())->where(['id' =>$item['user_id']])->value('center_name');
            //获取此专题下的课程数量以及学生数量
            $course = (new CourseRela())->where(['other_id'=>$item['id'],'type'=>2,'center_id'=>$center_id])->column('course_id');
//            var_dump($course);die;
            $stu_num = (new Baoming())->where(['center_id'=>$center_id,'course_id'=>['in',$course]])->count(1);
            $course_num = count($course);
            $speList[$key]['course_num'] = $course_num;
            $speList[$key]['stu_num'] = $stu_num;
        }
        return $this->ok(['list'=>$speList,'num'=>$num], 11111, '获取专题成功');

    }

    /**
     * 添加专题
     * @param center_token 场馆令牌
     * @param title  标题
     * @param remark  描述
     * @param center_id  来源(要为那个馆创建专题[下拉框])
     * @return array|bool
     */
    public function save()
    {
        $_GET['center_token'] = 'afbe52d7014168fa8b70f2a6c716a88c3e06c910';
        $_GET['timestamp'] = time();
        $salt = 'l5Y2Q';
        $_GET['sign'] = encrypt_key(['v1/specialsubject/save', $_GET['timestamp'], $_GET['center_token'], $salt], '');
        $_GET['title'] = '天津1';
        $_GET['remark'] = '天津馆';

        $title = input('param.title');
        $remark = input('param.remark', '');
        $status = input('param.status',0,'intval');
//        $center_id = input('param.center_id',0,'intval');

        $msg = verify();
        if ($msg['status'] == 0) {
            return $msg;
        } else {
//            $user_id = $msg['data']['user_id'];
            $center_id = $msg['data']['center_id'];
        }

        $data = [
            'title' => $title,
            'remark' => $remark,
            'center_id' => $center_id,
//            'user_id' => $user_id,
            'status'=>$status,
            'create_time' => time()
        ];

        $result = (new SpeSubModel())->allowField(true)->validate(true)->isUpdate(false)->save($data);
        if ($result) {
            return $this->ok([], 11111, '专题添加成功');
        } else {
            return $this->fail(11000, '添加专题失败');
        }
    }

    /**
     * 专题编辑详情页
     * @param center_token 场馆令牌
     * @param id 专题id
     * @return array|bool
     */
    public function read()
    {
        $_GET['center_token'] = 'd772d5c2ca2e10a36c6dbb3acae6850538ad0704';
        $_GET['timestamp'] = time();
        $salt = '8FrkI';
        $_GET['sign'] = encrypt_key(['v1/specialsubject/read', $_GET['timestamp'], $_GET['center_token'], $salt], '');
        $_GET['id'] = 3;

        $spe_id = input('param.id');

        //验证token sign
        $msg = verify();
        if ($msg['status'] == 0) {
            return $msg;
        } else {
            $center_id = $msg['data']['center_id'];
        }

        $where = [];
        if($center_id != 1){
            $where['center_id'] = $center_id;
        }

        $speModel = new SpeSubModel();
        $speInfo = $speModel->alias('s')->join('mooc_center mc', 'mc.id=s.center_id')->where(['s.id' => $spe_id])->where($where)->field('s.*,mc.center_name as creator')->find();
        if ($speInfo) {
            $speInfo['source'] = (new MoocCenter())->where(['id' => $speInfo->user_id])->value('center_name');
            return $this->ok($speInfo, 11111, '专题获取成功');
        } else {
            return $this->fail(11001, '专题获取失败');
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
        $title = input('param.title');
        $remark = input('param.remark');
        $spe_id = input('param.id');

        $speModel = new SpeSubModel();
        $msg = verify();
        if ($msg['status'] == 0) {
            return $msg;
        } else {
            $center_id = $msg['data']['center_id'];
            $center = $speModel->where(['id' => $spe_id])->value('center_id'); //专题对应的场馆
            if ($center !== $center_id) {
                return $this->fail(12001, '此专题不属于文化馆，没有权限修改');
            }
        }

        $data = [
            'title' => $title,
            'remark' => $remark,
            'update_time' => time(),
            'id' => $spe_id
        ];

        $result = $speModel->allowField(true)->validate(true)->update($data);
        if ($result) {
            return $this->ok([], 12111, '修改专题成功');
        } else {
            return $this->fail(12011, $speModel->getError());
        }
    }

    /**
     * 专题编辑
     * @param center_token 场馆令牌
     * @param id  专题id
     * @return array|bool
     */
    public function delete()
    {
        $spe_id = input('param.id',0);
        $spe_ids = input('param.ids/a',[]);

        $msg = verify();
        if ($msg['status'] == 0) {
            return $msg;
        }else{
            $center_id = $msg['data']['center_id'];
        }


        $speModel = new SpeSubModel();
        if(!empty($spe_id)){
            //此专题是否属于该文化馆
            $is_exist = $speModel->where(['center_id'=>$center_id,'id'=>$spe_id])->find();
            if(!$is_exist){
                return $this->fail(12012, '没有权限删除此专题');
            }

            $has_mk = (new CourseRela())->where(['other_id' => $spe_id, 'type' => 4])->select();
            if ($has_mk) {
                return $this->fail(12012, '专题下有课程，请先删除课程');
            }

            $result = $speModel->where(['id' => $spe_id])->delete();
            if ($result) {
                return $this->ok([], 11222, '删除专题成功');
            } else {
                return $this->fail(11200, '删除专题失败');
            }
        }

        if(!empty($spe_ids)){
            //专题是否属于该文化馆
            $del_ids = [];
            foreach($spe_ids as $item){
                $is_exist = $speModel->where(['center_id'=>$center_id,'id'=>$item])->find();
                $has_mk = (new CourseRela())->where(['other_id' => $item, 'type' => 4])->select();
                if($is_exist && !$has_mk){
                    $del_ids[] = $item;
                }
            }
            $res = $speModel->where(['id' => ['in',$spe_ids]])->delete();
            if($res){
                return $this->ok([], 11222, '删除专题成功');
            }else{
                return $this->fail(11200, '删除专题失败');
            }

        }

    }

    /**
     * 专题慕课管理   专题添加慕课或删除慕课
     * @param center_token 场馆令牌
     * @param id  专题id
     * @param page 页数
     * @param len  长度
     * @return array|bool
     */
    public function spe_muke()
    {
        $spe_id = input('param.id', 0, 'intval');
        $course_title = input('param.course_title');

        //验证签名 token
        $msg = verify();
        if ($msg['status'] == 0) {
            return $msg;
        } else {
            $center_id = $msg['data']['center_id'];
        }

        $spe = (new SpeSubModel())->where(['id' => $spe_id])->find();
        if (!$spe) {
            return $this->fail(12001, '此专题不存在');
        }
        if ($spe->center_id !== $center_id) {
            return $this->fail(13001, '文化馆没有此专题');
        }

        //此专题所有的慕课
        $spe_mk = new CourseRela();
        $spe_mk = $spe_mk->alias('s')->join('course c', 'c.id=s.course_id')->where(['s.other_id' => $spe_id, 's.type' => 2])->field('c.id')->select();

        $where = ['cc.center_id' => $center_id];
        if (!empty($course_title)) {
            $where['c.course_title'] = ['like', "%$course_title%"];
        }
        $allMk = (new CenterCourse())->alias('cc')->join('course c', 'c.id=cc.course_id')->where($where)->field('cc.*,c.course_title')->select();
        return $this->ok(['spe_mk' => $spe_mk, 'all_mk' => $allMk], 12311, '获取专题库成功');
    }

    /**
     * 专题课程
     * @param center_id 场馆id
     * @param page 页数
     * @param len  长度
     * @return array|bool
     */
    public function spe_courses(){
        $center_id = input('param.center_id',0,'intval');
        $page = input('param.page', 1, 'intval');
        $len = input('param.len', 10, 'intval');

        $courseModel = new Course();
        $spe_courses = $courseModel
            ->alias('c')
            ->join('__COURSE_RELA__ cr','cr.course_id=c.id')
            ->join('__CENTER_COURSE__ cc','cc.course_id=c.id and cc.center_id = cr.center_id')
            ->join('__COMMENT__ cm','cm.course_id=c.id','left')
            ->where(['cr.status'=>1,'cr.center_id'=>$center_id,'cr.type'=>2])
            ->page($page,$len)
            ->field('c.*,cc.play_num,avg(cm.practical_score+cm.concise_score+cm.clear_score)/3 as score')
            ->group('c.id')
            ->select();

        return $this->ok($spe_courses,12345,'获取专题课程成功');
    }

    public function updateRela(){
        $spe_id = input('param.id', 1, 'intval');//专题id
        $course_ids = input('param.course_ids/a',[]);//课程id []
        $type = input('param.type', 2, 'intval'); //类型 专题课程关联type:2

        $msg = verify();
        if ($msg['status'] == 0) {
            return $msg;
        } else {
            $center_id = $msg['data']['center_id'];
        }


        if (!empty($spe_id)) {
            $spe_sub = SpeSubModel::get($spe_id);
            if (!$spe_sub) {
                return fail(12001, '专题不存在');
            }
        } else {
            return fail(12002, 'id必须');
        }

        if (empty($type)) {
            return fail(12003, '类型必须');
        }


        //此专题下所有的慕课ids
        $spe_mkModel = new CourseRela();
        $spe_mkList = $spe_mkModel->alias('sm')->join('course c', 'c.id=sm.course_id')->where(['sm.other_id' => $spe_id, 'sm.type' => 2,'sm.center_id'=>$center_id])->column('sm.course_id');

        //获取要添加的慕课的ids
        $addIds = array_diff($course_ids,$spe_mkList);
        $connect_ids = array_intersect($course_ids,$spe_mkList);
        $is_delete = count($connect_ids) === count($spe_mkList)? 0 : 1;

        //只有添加课程
        if(count($addIds) != 0 && !$is_delete){
            if (empty($course_ids)) {
                return fail(12004, '课程必须');
            }
            $saveRes = $this->savemk($spe_id,$addIds,$type,$center_id);
            if ($saveRes) {
                return $this->ok([], 12112, '添加课程成功');
            } else {
                return $this->fail(12002, '添加课程失败');
            }
        }

        //仅删除课程
        if($is_delete && (count($addIds) == 0)){
            $deleteIds = array_diff($spe_mkList,$connect_ids);
            $spe_mkModel->where(['course_id' => ['in', $deleteIds], 'other_id' => $spe_id, 'type' => $type,'center_id'=>$center_id])->delete();
            return $this->ok([], 12112, '删除课程成功');
        }

        //删除专题下的课程和添加课程
        if($is_delete && count($addIds) != 0){
            $deleteIds = array_diff($spe_mkList,$connect_ids);
            $saveRes = $this->savemk($spe_id,$addIds,$type,$center_id);
            $result = $spe_mkModel->where(['course_id' => ['in', $deleteIds], 'other_id' => $spe_id, 'type' => $type,'center_id'=>$center_id])->delete();
            if($saveRes && $result ){
                return $this->ok([], 12112, '保存成功');
            }else{
                return $this->fail(12002, '保存失败');
            }

        }
        //不删除不增加
        if(!$is_delete && (count($addIds) == 0) ){
            return $this->ok([], 12102, '保存成功');
        }


    }


    public function savemk($spe_id,$course_ids,$type,$center_id)
    {
        $column_mkModel = new CourseRela();
        if (!empty($course_ids)) {
            foreach ($course_ids as $id) {
                $data['course_id'] = $id;
                $data['other_id'] = $spe_id;
                $data['create_time'] = time();
                $data['type'] = $type;
                $data['center_id'] = $center_id;
                $savedata[] = $data;
            }
        }
        $result = $column_mkModel->allowField(true)->insertAll($savedata);
        return $result;
    }

    /**
     * 专题详情页(前台)
     * @param center_token 场馆令牌
     * @param id 专题id
     * @return array|bool
     */
    public function read_spe()
    {
        $_GET['id'] = 4;

//        $user_token = input('param.user_token');
        $center_id = input('param.center_id',1,'intval');
        $spe_id = input('param.id',0,'intval');

        //用户令牌
//        $tokenRes = checkUserToken($user_token);
//        if ($tokenRes !== true) {
//            return $tokenRes;
//        }


        $speModel = new SpeSubModel();
        $speInfo = $speModel->where(['id' => $spe_id,'center_id'=>$center_id])->find();

        //获取专题下所有的慕课
        $rela = new CourseRela();
        $spe_mk = $rela->alias('r')
            ->join('course c', 'c.id=r.course_id')
            ->join('center_course cc','cc.course_id = c.id','left')
            ->join('comment cm','cm.course_id = c.id','left')
            ->where(['r.other_id' => $spe_id, 'r.center_id'=>$center_id,'r.type' => 2])
            ->field('c.*,cc.play_num,avg(cm.practical_score+cm.concise_score+cm.clear_score) as score')
            ->group('c.id')
            ->select();

        if ($speInfo) {
            return $this->ok(['spe'=>$speInfo,'spe_mk'=>$spe_mk], 11111, '专题获取成功');
        } else {
            return $this->fail(11001, '专题获取失败');
        }

    }

}