<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use app\admin\model\ActivityModel;
use app\admin\model\VolunTypeModel;
use app\admin\model\VenueModel;
use app\admin\model\AreaModel;
use app\admin\model\ActivityBaomingModel;
use app\admin\model\UserModel;
//use Crontab\Crontab;
//use Crontab\Job;
use think\Db;


class VolunRecruitController extends AdminBaseController
{

    /**
     * 活动列表
     */
    public function index()
    {
        $where=[];
        $where['a.volun_type'] =['neq',0];
        $param = $this->request->param();
        $all_status = $this->request->param('all_status', 0, 'intval');

        if($all_status==1){
            $where['v.status'] =1;
        }elseif ($all_status==2){
            $where['v.status'] =0;
        }
        $activity_type = $this->request->param('activity_type', 0, 'intval');
        if(!empty($activity_type)){
            $where['a.volun_type']=$activity_type;
        }

        $venue = $this->request->param('venue', 0, 'intval');
        $all = UserModel::getCurrentVenue();
        if(!empty($venue) && in_array($venue, $all)){
            $where['a.venue']= $venue;
        }else{
            $where['a.venue'] = ['in', $all];
        }
        $keyword = $this->request->param('keyword');
        if(!empty($keyword)){
            $where['a.title']= ['like', "%$keyword%"];
        }
        $where['a.type'] = 0;
        $where['a.delete_time']=0;
        $where['a.status'] = 1;

        $activityModel = new ActivityModel();

        $activity_list    = $activityModel->alias('a')->where($where)->join('venue v','a.venue =v.id','left')->join('volun_type vt','a.volun_type=vt.id','left')->join('activity_baoming ab','a.id=ab.activity_id','left')->field('a.*,v.name as venue_name,vt.name as activity_name,count(ab.id) as baoming,sum(ab.status) as yibaoming,v.status as vstatus')->group('a.id')->order('id DESC')->paginate(15);
        // 获取分页显示
        $activity_list->appends($param);
        $page = $activity_list->render();
        //活动类型列表
        $activityModel = new VolunTypeModel();
        $activity_type_list    = $activityModel->where(['status'=>1])->order('id DESC')->select();

        //区域列表
        $areaModel = new AreaModel();
        $area_id =!empty($param['area_id']) ? $param['area_id'] : 0;
        $areas = $areaModel->adminAreaTree($area_id);

        //场馆列表
        $venueModel = new VenueModel();
        $venue_where['id']=['in', UserModel::getCurrentVenue()];
        $venue_list    = $venueModel->where($venue_where)->order('id DESC')->select();

        $this->assign('activity_type', $activity_type);
        $this->assign('all_status', $all_status);
        $this->assign('venue', isset($param['venue']) ? $param['venue'] : '');
        $this->assign('keyword', isset($keyword) ?$keyword : '');
        $this->assign('list', $activity_list);
        $this->assign('areas', $areas);
        $this->assign('activity_type_list', $activity_type_list);
        $this->assign('venue_list', $venue_list);
        $this->assign('page', $page);

        return $this->fetch();
    }

    /**
     * 活动招募名单查看
     */
    public function user_list()
    {
        $param = $this->request->param();
        $activity_id   = $this->request->param('id',0,'intval');
        $where['ab.status']=1;
        if(!empty($activity_id)){
            $where['ab.activity_id']=$activity_id;
        }
        $keyword = $this->request->param('keyword');
        if(!empty($keyword)){
            $where['u.user_realname']= ['like', "%$keyword%"];
        }

        $baomingModel = new ActivityBaomingModel();
        $user_list=$baomingModel->alias('ab')->join('user u','ab.user_id =u.id')->join('sfzimg s','ab.user_id=s.user_id','left')->where($where)->field('ab.*,u.mobile,u.sex,s.realname')->order('id desc')->paginate(15);
        $user_list->appends($param);
        $page = $user_list->render();

        //活动区域
        $acvitityModel =new ActivityModel();
        $activity_area=$acvitityModel->alias('a')->join('venue v','a.venue =v.id','left')->where(['a.id'=>$activity_id])->value('v.name');

        $this->assign('activity_id', $activity_id);
        $this->assign('activity_area', $activity_area);
        $this->assign('user_list', $user_list);
        $this->assign('page', $page);

        return $this->fetch();
    }
    /**
     * 用户审核
     */
    public function verify(){
        $where=[];
        $param = $this->request->param();
        $activity_id   = $this->request->param('id',0,'intval');
        if(!empty($activity_id)){
            $where['ab.activity_id']=$activity_id;
        }

        $keyword = $this->request->param('keyword');
        if(!empty($keyword)){
            $where['s.realname']= ['like', "%$keyword%"];
        }

        $baomingModel = new ActivityBaomingModel();
        $user_list=$baomingModel->alias('ab')->join('user u','ab.user_id =u.id','left')->join('sfzimg s','u.id=s.user_id','left')->where($where)->field('ab.*,u.mobile,u.sex,u.user_nickname,s.realname')->order('id desc')->paginate(15);

        $user_list->appends($param);
        $page = $user_list->render();
        //活动区域
        $acvitityModel =new ActivityModel();
        $activity_area=$acvitityModel->alias('a')->join('venue v','a.venue =v.id','left')->where(['a.id'=>$activity_id])->value('v.name');

        $this->assign('keyword', isset($keyword)?$keyword:'');
        $this->assign('activity_id', $activity_id);
        $this->assign('activity_area', $activity_area);
        $this->assign('user_list', $user_list);
        $this->assign('page', $page);

        return $this->fetch();
    }

    /**
     * 发放积分
     */
    public function score()
    {
        $user_id   = $this->request->param('user_id',0,'intval');
        $score   = $this->request->param('score',0,'intval');
        $activity_id   = $this->request->param('activity_id',0,'intval');
        $baomingModel=new ActivityBaomingModel();
        if(!empty($user_id) && !empty($activity_id)){
            $current_score= $baomingModel->where(['user_id'=>$user_id,'activity_id'=>$activity_id,'status'=>1])->value('score');
            $baomingModel->where(['user_id'=>$user_id,'activity_id'=>$activity_id,'status'=>1])->update(['score'=>$score]);
            //更改用户总积分
            Db::name('user')->where(['id'=>$user_id])->setInc('score',$score-$current_score);
            $result=array('status'=>1,'msg'=>'发放成功！');
            echo json_encode($result);exit;
        }else{
            $result=array('status'=>0,'msg'=>'发放失败');
            echo json_encode($result);exit;
        }
    }
    /**
     * 统一发放积分
     */
    public  function tyscore(){
        $param= $this->request->param();
        $tyscore   = $this->request->param('tyscore',0,'intval');
        $activity_id   =$param['activity_id'];
        $baomingModel = new ActivityBaomingModel();
        if(!empty( $param['ids'])){
            $ids = $this->request->param('ids/a');
            //更改用户总积分

            $sql="update cxtj_user a,cxtj_activity_baoming b set a.score=(
            if((a.score-b.score+$tyscore)>0,a.score-b.score+$tyscore,0)) where a.id = b.user_id and b.activity_id=$activity_id";
            Db::name('user')->query($sql);

            //更改用户活动积分
            $baomingModel->where(['id' => ['in', $ids],'status'=>1])->update(['score'=>$tyscore]);

            $this->success("发放成功！", '/admin/volun_recruit/user_list/id/'.$activity_id);
        }
    }

    /**
     * 定时任务
     * 活动结束三天后如果未派发积分自动加上活动活跃积分
     */
    public function actionthreedays(){
        $baomingModel = new ActivityBaomingModel();
        $baomingList = $baomingModel->alias('b')->join('activity a','a.id=b.activity_id','left')->field('b.*,a.score as activity_score,a.end_time')->where('b.score',0)->select();

        $user = new UserModel();
        foreach($baomingList as $value){
            $timestamp = 3600*24*3;
            if(time()>($value['end_time']+$timestamp) && $value['score'] == 0){
                $baomingModel->where('id',$value['id'])->update(['score'=>$value['activity_score']]);
                $activity_id = $value['id'];
                $activity_score = $value['activity_score'];
                //给用户加总积分
                $sql="update cxtj_user u,cxtj_activity_baoming b set u.score=(
            if((u.score-b.score+$activity_score)>0,u.score-b.score+$activity_score,0)) where u.id = b.user_id and b.activity_id=$activity_id";
                Db::name('user')->query($sql);
            }
        }

    }
    /**
     * 活动志愿者个人详情
     */
    public function info(){
            $activity_id= $this->request->param('activity_id',0,'intval');
            $user_id= $this->request->param('user_id',0,'intval');
            //来源 type=1  名单 type=2 审核
            $type= $this->request->param('type',1,'intval');

            $areaModel = new AreaModel();
            $area_list = $areaModel->where('status',1)->select()->toArray();

            $user_info=Db::name('user')->alias('u')->join('sfzimg s','s.user_id=u.id')->field('u.*,s.realname')->where('u.id',$user_id)->find();

            $volun_skill_imgs_array =json_decode($user_info['volun_skill_imgs'],true);

            $this->assign('activity_id', $activity_id);
            $this->assign('type', $type);
            $this->assign('area_list',array_column($area_list,null,'id'));
            $this->assign('user_info', $user_info);
            $this->assign('volun_skill_imgs',$volun_skill_imgs_array);
            return $this->fetch();



    }
    /**
     * 活动志愿者个人详情审核
     */
    public function shenhePost(){
        $activity_id= $this->request->param('activity_id',0,'intval');
        $user_id= $this->request->param('user_id',0,'intval');
        $type= $this->request->param('type',0,'intval');

        $baomingModel=new ActivityBaomingModel();
        if(isset($_POST['pass'])){//同意
            $baomingModel->where('user_id',$user_id)->update(['status'=>1]);
        }else{//不同意
            $baomingModel->where('user_id',$user_id)->update(['status'=>0]);
        }
        if($type ==1){
            $this->success("修改成功！", '/admin/volun_recruit/user_list/id/'.$activity_id);
        }else{
            $this->success("修改成功！", '/admin/volun_recruit/verify/id/'.$activity_id);
        }
    }

    /**
     * 添加活动
     */
    public function add()
    {
        //当前用户下文化馆列表
        $venueModel=new VenueModel();
        $where['id']= ['in', UserModel::getCurrentVenue()];
        $venues=$venueModel->where($where)->select();

        //区域列表
        $areaModel=new AreaModel();
        $areas = $areaModel->adminAreaTree();

        //表演类型
        $volunTypeModel=new VolunTypeModel();
        $type_list=$volunTypeModel->where(['status'=>1])->select();

        $this->assign('areas', $areas);
        $this->assign('venues', $venues);
        $this->assign('type_list', $type_list);

        return $this->fetch();
    }
    /**
     * 获取区域下的场馆
     */
    public function getVenue(){
        $area_id=$this->request->param('area_id','0','intval');
         //获取该场馆及其子场馆
        $areaModel=new AreaModel();
        $area_path=$areaModel->where('id',$area_id)->value('path');
        $areas= $areaModel->where(['path'=>['like',"%$area_path%"]])->column('id');
        //获取该区域及其子区域下的所有场馆
        $venueModel=new VenueModel();
        $venues=$venueModel->where(['address'=>['in',$areas]])->field('id,name')->select();
        if(!empty($venues)){
            echo json_encode(array('status'=>1,'data'=>$venues,'msg'=>''));exit;
        }else{
            echo json_encode(array('status'=>0,'msg'=>''));exit;
        }
    }

    /**
     * 添加活动提交保存
     */
    public function addPost()
    {
        if ($this->request->isPost()) {
            $data   = $this->request->param();
            $post   = $data['post'];
            $result = $this->validate($post, 'Activity');

            if ($result !== true) {
                $this->error($result);
            }

            if(empty($post['start_time'])){
                $this->error('请填写活动开始时间');
            }
            if(empty($post['end_time'])){
                $this->error('请填写活动结束时间');
            }
            if(empty($post['baoming_start_time'])){
                $this->error('请填写报名开始时间');
            }
            if(empty($post['baoming_end_time'])){
                $this->error('请填写报名结束时间');
            }
           /* if(empty($post['max_num'])){
                $this->error('请填写最大人数');
            }*/

            if(empty($post['thumb'])){
                $this->error('请上传缩略图');
            }

            $activityModel = new ActivityModel();

            if (!empty($data['photo_names']) && !empty($data['photo_urls'])) {
                $data['post']['thumb'] = [];
                foreach ($data['photo_urls'] as $key => $url) {
                    $photoUrl = cmf_asset_relative_url($url);
                    array_push($data['post']['thumb'], ["url" => $photoUrl, "name" => $data['photo_names'][$key]]);
                }
            }
            $activityModel->adminAddActivity($data['post']);
            $this->success('添加成功!', url('VolunRecruit/index'));
        }
    }

    /**
     * 编辑活动
     */
    public function edit()
    {
        //活动id
        $activity_id        = $this->request->param('id', 0, 'intval');
        $activity=ActivityModel::get($activity_id);

        //当前用户下文化馆列表
        $venueModel=new VenueModel();
        $where['id']= ['in', UserModel::getCurrentVenue()];
        $venues=$venueModel->where($where)->select();

        //区域列表
        $areaModel=new AreaModel();
        $area_id = VenueModel::where('id', $activity->venue)->value('address',0);
        $areas = $areaModel->adminAreaTree($area_id);

        //表演类型
        $volunTypeModel=new VolunTypeModel();
        $type_list=$volunTypeModel->where(['status'=>1])->select();

        //活动区域
        $areaModel=new AreaModel();
        $activity_areas = $areaModel->adminAreaTree($activity['area']);

        $this->assign('venues', $venues);
        $this->assign('areas', $areas);
        $this->assign('activity_areas', $activity_areas);
        $this->assign('type_list', $type_list);
        $this->assign('activity',$activity);
        return $this->fetch();
    }

    /**
     * 编辑活动提交保存
     */
    public function editPost()
    {
        $data   = $this->request->param();
        $post   = $data['post'];
        $result = $this->validate($post, 'Activity');
        if ($result !== true) {
            $this->error($result);
        }

        if(empty($post['start_time'])){
            $this->error('请填写活动开始时间');
        }
        if(empty($post['end_time'])){
            $this->error('请填写活动结束时间');
        }
        if(empty($post['baoming_start_time'])){
            $this->error('请填写报名开始时间');
        }
        if(empty($post['baoming_end_time'])){
            $this->error('请填写报名结束时间');
        }

        $activityModel = new ActivityModel();
        if (!empty($data['photo_names']) && !empty($data['photo_urls'])) {
            $data['post']['thumb'] = [];
            foreach ($data['photo_urls'] as $key => $url) {
                $photoUrl = cmf_asset_relative_url($url);
                array_push($data['post']['thumb'], ["url" => $photoUrl, "name" => $data['photo_names'][$key]]);
            }
        }

        $activityModel->adminEditActivity($data['post']);
        $this->success("修改成功！",'VolunRecruit/index');
    }

    /**
     * 活动删除
     */
    public function delete()
    {
        $param           = $this->request->param();
        $activityModel = new ActivityModel();
        if(isset($param['id'])){
            $id = $this->request->param('id',0,'intval');
            $activityModel->where(['id' => $id])->update(['delete_time' => time()]);
            $this->success("删除成功！", '');
        }
        if (isset($param['ids'])) {
            $ids = $this->request->param('ids/a');
            $activityModel->where(['id' => ['in', $ids]])->update(['delete_time' => time()]);
            $this->success("删除成功！", '');
        }
    }

    /**
     * 活动发布
     */
    public function publish()
    {
        $param           = $this->request->param();
        $activityModel = new ActivityModel();
        if (isset($param['ids']) && isset($param["yes"])) {
            $ids = $this->request->param('ids/a');

            $activityModel->where(['id' => ['in', $ids]])->update(['status' => 1, 'published_time' => time()]);
            $this->success("发布成功！", '');
        }

        if (isset($param['ids']) && isset($param["no"])) {
            $ids = $this->request->param('ids/a');

            $activityModel->where(['id' => ['in', $ids]])->update(['status' => 0]);

            $this->success("取消发布成功！", '');
        }

    }
    /**
     * 活动志愿者审核
     */
    public function volun_verify()
    {
        $param           = $this->request->param();
        $activityBaomingModel = new ActivityBaomingModel();
        if (isset($param['ids']) && isset($param["yes"])) {

            $ids = $this->request->param('ids/a');

            $activityBaomingModel->where(['id' => ['in', $ids]])->update(['status' => 1]);
            $this->success("操作成功", '');
        }

        if (isset($param['ids']) && isset($param["no"])) {
            $ids = $this->request->param('ids/a');

            $activityBaomingModel->where(['id' => ['in', $ids]])->update(['status' => 2]);

            $this->success("操作成功！", '');
        }
    }
    /**
     * 活动志愿者删除
     */
    public function volun_delete()
    {
        $param           = $this->request->param();
        $activityBaomingModel = new ActivityBaomingModel();
        if(isset($param['id'])){
            ActivityBaomingModel::destroy($param['id']);
            $this->success("删除成功！", '');
        }
        if (isset($param['ids'])) {
            $ids = $this->request->param('ids/a');
            $activityBaomingModel->where(['id' => ['in', $ids]])->delete();

            $this->success("删除成功！", '');
        }
    }
    /**
     * 活动置顶 取消置顶
     */
    public function top(){
        $param = $this->request->param();

        $activityModel =new ActivityModel();

        if(isset($param['id']) && isset($param['top'])){
            $activity_id=$this->request->param('id','0','intval');
            $res=$activityModel->where('id',$activity_id)->update(['is_top'=>1]);
            if($res !== false){
                $this->success("置顶成功！", 'index');
            }else{
                $this->error("置顶失败！", 'index');
            }
        }

        if(isset($param['id']) && isset($param['cancel'])){
            $activity_id=$this->request->param('id','0','intval');
            $res=$activityModel->where('id',$activity_id)->update(['is_top'=>0]);
            if($res !== false){
                $this->success("取消置顶成功！", 'index');
            }else{
                $this->error("取消置顶失败！", 'index');
            }
        }


    }

}