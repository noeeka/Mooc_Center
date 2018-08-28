<?php
/**
 * Created by PhpStorm.
 * User: zhangchun
 * Date: 2018/7/2
 * Time: 17:32
 */

namespace app\v1\controller;

use app\v1\model\CenterCourse;
use app\v1\model\Column as ColumnModel;
use app\v1\model\CourseRela;
use tree\Tree;
use think\Db;

class column extends Core
{
    /**
     * 栏目列表
     * @param center_token 场馆令牌
     * @param title  标题
     * @param create_time  时间
     * @param page 页数
     * @param len  长度
     * @return array|bool 树形数组
     */
    public function index()
    {
        $_GET['center_token'] = 'd772d5c2ca2e10a36c6dbb3acae6850538ad0704';
//        $_GET['center_token'] = 'efd03d526338e056882f4543a23d38107633ced9';
        $_GET['timestamp'] = time();
        $salt = '8FrkI';
        $_GET['sign'] = encrypt_key(['v1/column/index', $_GET['timestamp'], $_GET['center_token'], $salt], '');

        //验证token sign
        $msg = verify();
        if ($msg['status'] == 0) {
            return $msg;
        } else {
            $center_id = $msg['data']['center_id'];
        }

        //获取栏目的树形结构
        $column_tree = $this->adminColumnTree($center_id);
        if($column_tree['status'] == 0){
            return $column_tree;
        }

        if ($column_tree) {
            return $this->ok($column_tree, 11111, '获取栏目成功');
        } else {
            return $this->fail(11000, '获取栏目失败');
        }
    }

    public function adminColumnTree($center_id)
    {
        $page = input('param.page',1,'intval');
        $len = input('param.len',10,'intval');
        $all = input('param.all',-1,'intval');

        $tree = new Tree();
        $columnModel = new ColumnModel();

        if($all != -1){
            if($center_id != 1){
                $where['center_id'] = $center_id;
            }
        }else{
            $whrer['center_id'] = $center_id;
        }

        //获取父级进行分页
        $columnList = $columnModel->alias('c')->join('mooc_center mc','mc.id=c.center_id')->where(['c.parent_id'=>0])->order(['c.center_id asc','c.list_order asc'])->field('c.*,mc.center_name')->page($page,$len)->select();
        $num = $columnModel->alias('c')->join('mooc_center mc','mc.id=c.center_id')->where(['c.parent_id'=>0])->order(['c.center_id asc','c.list_order asc'])->count(1);

        if ($columnList) {
            $columnList = \collection($columnList)->toArray();
            $AllColumnList = $columnModel->alias('c')->join('mooc_center mc','mc.id=c.center_id')->order(['c.center_id asc','c.list_order asc'])->field('c.*,mc.center_name')->select();
            $AllColumnList = \collection($AllColumnList)->toArray();
        } else {
            return $this->fail(11001, '获取栏目失败');
        }

        $tree->init($AllColumnList);

        foreach($columnList as $key =>$item){
            $columnArray = $tree->getTreeArray($item['id']);
            $columnList[$key]['children'] = $columnArray;
        }

        return $this->ok(['list'=>$columnList,'num'=>$num],11102,'获取栏目成功');
    }

    /**
     * 栏目table
     * @param center_token 场馆令牌
     * @param title  标题
     * @param create_time  时间
     * @param page 页数
     * @param len  长度
     * @param all  超星文化馆下获取所有需要传all
     * @return array|bool 树形数组
     */
    public function getColumnTable(){
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
        $column_tree = $this->TableTree($center_id);

        if($column_tree){
            return $column_tree;
        }else{
            return $this->fail(10001,'获取栏目失败');
        }


    }

    public function TableTree($center_id){
        $page = input('param.page',1,'intval');
        $len = input('param.len',10,'intval');
        $all = input('param.all',-1,'intval');

        //获取数据
        $columnModel = new ColumnModel();
        $where = [];
        if($all != -1){
            if($center_id != 1){
                $where['center_id'] = $center_id;
            }
        }else{
            $where['center_id'] = $center_id;
        }

        $columnList = $columnModel->alias('c')->join('mooc_center mc','mc.id=c.center_id')->where($where)->order('c.center_id asc')->field('c.*,mc.center_name')->page($page,$len)->select();
        $num = $columnModel->alias('c')->join('mooc_center mc','mc.id=c.center_id')->where($where)->count(1);
        if ($columnList) {
            $columnList = \collection($columnList)->toArray();
        } else {
            return $this->fail(11001, '获取栏目失败');
        }

        //生成树型结构数组
        $tree = new Tree();
        $tree->icon = ['&nbsp;&nbsp;│', '&nbsp;&nbsp;├─', '&nbsp;&nbsp;└─'];
        $tree->nbsp = '&nbsp;&nbsp;';
        $tree->init($columnList);

        $tableTree = $tree->getTable(0,'title','');

        return $this->ok(['list'=>array_values($tableTree),'num'=>$num],11101,'获取栏目成功');

    }


    /**
     * 栏目（首页栏目）
     * @param user_token 场馆令牌
     * @return array|bool
     */
    public function column_index(){
        $_GET['center_token'] = 'eb8bf7f7bad2a2dceb7e511fce50a7fad5a677ba';
        $_GET['timestamp'] = time();
        $salt = 'o2fSA';
        $_GET['sign'] = encrypt_key(['v1/column/column_index', $_GET['timestamp'], $_GET['center_token'], $salt], '');
        $type = input('param.type',1,'intval');  //1最新课程  2 经典课程
        $page = input('param.page',1,'intval');
        $len = input('param.len',10,'intval');

        //签名校验
        $msg = verify();
        if ($msg['status'] == 0) {
            return $msg;
        } else {
            $center_id = $msg['data']['center_id'];
        }


        //获取首页栏目
        $columnModel = new ColumnModel();
        $columnList = $columnModel->where(['center_id'=>$center_id,'parent_id'=>['neq',0]])->field('id,title,parent_id')->order('create_time asc')->select();

        if($type == 1){
            $column = $columnList[0];
        }else{
            $column = $columnList[1];
        }


        //获取栏目下的课程，课程浏览量及评价分数
        $column_mk = new CourseRela();
        $column_mkList = $column_mk->alias('cm')
            ->join('course c', 'c.id=cm.course_id','left')
            ->join('center_course cc','cc.course_id=c.id and cc.center_id=cm.center_id','left')
            ->join('comment ct','ct.course_id=c.id and cm.center_id=ct.center_id','left')
            ->where(['cm.other_id' => $column['id'], 'cm.type' => 4, 'cm.status' => 1,'cm.center_id'=>$center_id])
            ->field('c.id,c.course_title,c.cover_img,cc.play_num,avg(ct.practical_score+ct.concise_score+ct.clear_score) as score')
            ->page($page,$len)
            ->group('c.id')
            ->select();

        if($column_mkList){
            $column_mkList = \collection($column_mkList);
        }else{
            $column_mkList = [];
        }

        foreach($column_mkList as $key=>$item){
            if($item['score'] == null){
                $column_mkList[$key]['score'] = 0;
            }
        }

        return $this->ok(['column'=>$column,'mooc'=>$column_mkList],12111,'获取栏目成功');


    }


    /**
     * 添加栏目
     * @param center_token 场馆令牌
     * @param title  标题
     * @param remark  描述
     * @return array|bool
     */
    public function save()
    {
        $_GET['center_token'] = 'd772d5c2ca2e10a36c6dbb3acae6850538ad0704';
        $_GET['timestamp'] = time();
        $salt = '8FrkI';
        $_GET['sign'] = encrypt_key(['v1/column/save', $_GET['timestamp'], $_GET['center_token'], $salt], '');
        $_GET['title'] = '栏目栏目';
        $_GET['remark'] = '超星';
        $_GET['parent_id'] = 1;

        $title = input('param.title');
        $remark = input('param.remark', '');
        $parent_id = input('param.parent_id', 0, 'intval');

        //验证token sign
        $msg = verify();
        if ($msg['status'] == 0) {
            return $msg;
        } else {
            $center_id = $msg['data']['center_id'];
        }

        //验证父级id是否属于该场馆
        $column = new ColumnModel();
        if($parent_id != 0) {
            $res = $column->where(['center_id'=>$center_id,'id'=>$parent_id])->find();
            if(!$res){
                return $this->fail(12012,'父级栏目不存在');
            }
        }

        $data = [
            'center_id' => $center_id,
            'title' => $title,
            'remark' => $remark,
            'parent_id' => $parent_id,
            'create_time' => time()
        ];

        $result = $column->allowField(true)->validate(true)->isUpdate(false)->save($data);
        if ($result) {
            return $this->ok([], 11111, '添加栏目成功');
        } else {
            return $this->fail(11000, '添加栏目失败');
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
//        $_GET['center_token'] = 'd772d5c2ca2e10a36c6dbb3acae6850538ad0704';
//        $_GET['timestamp'] = time();
//        $salt = '8FrkI';
//        $_GET['sign'] = encrypt_key(['v1/column/read', $_GET['timestamp'], $_GET['center_token'], $salt], '');
//        $_GET['id'] = 1;

        $column_id = input('param.id');

        $msg = verify();
        if ($msg['status'] == 0) {
            return $msg;
        } else {
            $center_id = $msg['data']['center_id'];
        }

        //验证此栏目是否属于该场馆
        if($center_id != 1){
            $column = new ColumnModel();
            $res = $column->where(['center_id'=>$center_id,'id'=>$column_id])->find();
            if(!$res){
                return $this->fail(12012,'此栏目不存在');
            }
        }

        $columnModel = new ColumnModel();
        $columnInfo = $columnModel->alias('c')->join('mooc_center mc', 'mc.id=c.center_id')->where(['c.id' => $column_id])->field('c.*,mc.center_name as creator')->find();
        if ($columnInfo) {
            return $this->ok($columnInfo, 11111, '栏目获取成功');
        } else {
            return $this->fail(11001, '栏目获取失败');
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
//        $_GET['center_token'] = 'd772d5c2ca2e10a36c6dbb3acae6850538ad0704';
//        $_GET['timestamp'] = time();
//        $salt = '8FrkI';
//        $_GET['sign'] = encrypt_key(['v1/column/update', $_GET['timestamp'], $_GET['center_token'], $salt], '');
//        $_GET['title'] = '栏目2';
//        $_GET['remark'] = '栏目2';
//        $_GET['id'] = 2;
//        $_GET['status'] = 0;

        $title = input('param.title');
        $remark = input('param.remark', '');
        $status = input('param.status', 1, 'intval');
        $column_id = input('param.id', 0, 'intval');
        $parent_id = input('param.parent_id',0,'intval');

        $columnModel = new ColumnModel();
        $msg = verify();
        if ($msg['status'] == 0) {
            return $msg;
        } else {
            $center_id = $msg['data']['center_id'];
        }

        //验证栏目是否属于此文化馆
        $exist = $columnModel->where(['id' => $column_id,'center_id'=>$center_id])->find(); //栏目对应的场馆
        if (!$exist) {
            return $this->fail(12001, '此栏目不属于文化馆，没有权限修改');
        }
        //验证父级栏目是否属于此文化馆
        if($parent_id != 0){
            $parent_exist = $columnModel->where(['id' => $parent_id,'center_id'=>$center_id])->find(); //栏目对应的场馆
            if (!$parent_exist) {
                return $this->fail(12001, '父级栏目不属于文化馆，没有权限修改');
            }
        }

        $data = [
            'title' => $title,
            'status' => $status,
            'remark' => $remark,
            'id' => $column_id,
            'parent_id'=>$parent_id
        ];

        $result = $columnModel->allowField(true)->validate(true)->update($data);
        if ($result) {
            return $this->ok([], 12111, '修改栏目成功');
        } else {
            return $this->fail(12011, $columnModel->getError());
        }
    }

    /**
     * 栏目删除
     * @param center_token 场馆令牌
     * @param id  专题id
     * @return array|bool
     */
    public function delete()
    {
//        $_GET['center_token'] = 'd772d5c2ca2e10a36c6dbb3acae6850538ad0704';
//        $_GET['timestamp'] = time();
//        $salt = '8FrkI';
//        $_GET['sign'] = encrypt_key(['v1/column/delete', $_GET['timestamp'], $_GET['center_token'], $salt], '');
//        $_GET['id'] = 3;

        $column_id = input('param.id');

        $msg = verify();
        if ($msg['status'] == 0) {
            return $msg;
        } else {
            $center_id = $msg['data']['center_id'];
        }

        //验证栏目是否属于此文化馆
        $columnModel = new ColumnModel();
        $exist = $columnModel->where(['id' => $column_id,'center_id'=>$center_id])->find();
        if (!$exist) {
            return $this->fail(12001, '此栏目不属于文化馆，没有权限删除');
        }
        //验证此栏目下是否有子集
        $haschild = $columnModel->where(['parent_id' => $column_id,'center_id'=>$center_id])->select();
        if ($haschild) {
            return $this->fail(12011, '有子栏目，请先删除子栏目');
        }
        //验证此栏目下是否有慕课
        $has_mk = (new CourseRela())->where(['other_id' => $column_id, 'type' => 4,'center_id'=>$center_id])->select();
        if ($has_mk) {
            return $this->fail(12012, '栏目下有课程，请先删除课程');
        }

        $result = $columnModel->where(['id' => $column_id])->delete();
        if ($result) {
            return $this->ok([], 11222, '删除栏目成功');
        } else {
            return $this->fail(11200, '删除栏目失败');
        }
    }

    /**
     * 专题慕课管理
     * @param center_token 场馆令牌
     * @param id  专题id
     * @param page 页数
     * @param len  长度
     * @return array|bool
     */
    public function column_muke()
    {
        $_GET['center_token'] = 'd772d5c2ca2e10a36c6dbb3acae6850538ad0704';
        $_GET['timestamp'] = time();
        $salt = '8FrkI';
        $_GET['sign'] = encrypt_key(['v1/column/column_muke', $_GET['timestamp'], $_GET['center_token'], $salt], '');
        $_GET['id'] = 2;

        $column_id = input('param.id', 0, 'intval');
        $course_title = input('param.course_title');

        $msg = verify();
        if ($msg['status'] == 0) {
            return $msg;
        } else {
            $center_id = $msg['data']['center_id'];
        }


        $column = (new ColumnModel())->where(['id' => $column_id,'center_id'=>$center_id])->find();
        if (!$column) {
            return $this->fail(12001, '此栏目不存在');
        }
        if ($column->center_id !== $center_id) {
            return $this->fail(13001, '文化馆没有此栏目');
        }

        $column_mk = new CourseRela();

        //此栏目所有的慕课
        $column_mkList = $column_mk->alias('cm')->join('course c', 'c.id=cm.course_id')->where(['cm.other_id' => $column_id, 'cm.type' => 4, 'cm.status' => 1,'center_id'=>$center_id])->field('cm.*,c.course_title')->select();

        if (!empty($course_title)) {
            $where['c.course_title'] = ['like', "%$course_title%"];
        }

        //所有此文化馆下的课程
        $allMk = (new CenterCourse())->alias('cc')->join('course c', 'c.id=cc.course_id')->where('cc.center_id=1 or cc.center_id = '.$center_id.' and cc.status= 1')->field('cc.*,c.course_title')->select();

        return $this->ok(['col_mk' => $column_mkList, 'all_mk' => $allMk], 12311, '获取栏目课程成功');
    }

    public function updateRela(){
        $column_id = input('param.id', 1, 'intval');//栏目id
        $course_ids = input('param.course_ids/a',[]);//课程id []
        $type = input('param.type', 4, 'intval'); //类型 栏目课程关联type:4

        $msg = verify();
        if ($msg['status'] == 0) {
            return $msg;
        } else {
            $center_id = $msg['data']['center_id'];
        }


        if (!empty($column_id)) {
            $column = ColumnModel::get($column_id);
            if (!$column) {
                return fail(12001, '栏目不存在');
            }
        } else {
            return fail(12002, 'id必须');
        }

        if (empty($type)) {
            return fail(12003, '类型必须');
        }


        //此栏目下所有的慕课ids
        $column_mkModel = new CourseRela();
        $column_mkList = $column_mkModel->alias('cm')->join('course c', 'c.id=cm.course_id')->where(['cm.other_id' => $column_id, 'cm.type' => 4, 'cm.status' => 1,'cm.center_id'=>$center_id])->column('cm.course_id');

        //获取要添加的慕课的ids
        $addIds = array_diff($course_ids,$column_mkList);
        $connect_ids = array_intersect($course_ids,$column_mkList);
        $is_delete = count($connect_ids) === count($column_mkList)? 0 : 1;

        //只有添加课程
        if(count($addIds) != 0 && !$is_delete){
            if (empty($course_ids)) {
                return fail(12004, '课程必须');
            }
            $saveRes = $this->savemk($column_id,$addIds,$type,$center_id);
            if ($saveRes) {
                return $this->ok([], 12112, '添加课程成功');
            } else {
                return $this->fail(12002, '添加课程失败');
            }
        }

        //仅删除课程
        if($is_delete && (count($addIds) == 0)){
            $deleteIds = array_diff($column_mkList,$connect_ids);
            $column_mkModel->where(['course_id' => ['in', $deleteIds], 'other_id' => $column_id, 'type' => $type,'center_id'=>$center_id])->delete();
            return $this->ok([], 12112, '删除课程成功');
        }

        //删除栏目下的课程和添加课程
        if($is_delete && count($addIds) != 0){
            $deleteIds = array_diff($column_mkList,$connect_ids);
            $saveRes = $this->savemk($column_id,$addIds,$type,$center_id);
            $result = $column_mkModel->where(['course_id' => ['in', $deleteIds], 'other_id' => $column_id, 'type' => $type,'center_id'=>$center_id])->delete();
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

    public function savemk($column_id,$course_ids,$type,$center_id)
    {
        $column_mkModel = new CourseRela();
        if (!empty($course_ids)) {
            foreach ($course_ids as $id) {
                $data['course_id'] = $id;
                $data['other_id'] = $column_id;
                $data['create_time'] = time();
                $data['type'] = $type;
                $data['center_id'] = $center_id;
                $savedata[] = $data;
            }
        }

        $result = $column_mkModel->allowField(true)->insertAll($savedata);
        return $result;
    }

}