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

use app\admin\model\SysMessageModel;
use cmf\controller\AdminBaseController;
use app\admin\model\CollectProductionModel;
use app\admin\model\ProductionCollectAwardModel;
use app\admin\model\ProductionCommentsModel;
use app\admin\model\ProductionCollectModel;
use app\admin\model\ProductionCollectTypeModel;
use app\admin\model\VenueModel;
use app\admin\model\AreaModel;
use app\admin\model\UserModel;
use think\Db;



class ProductionCollectController extends AdminBaseController
{

    /**
     * 征集活动列表
     */
    public function index()
    {

        $where=[];
        $param = $this->request->param();
        $collect_type = $this->request->param('collect_type', 0, 'intval');//征集类型
        $all_status = $this->request->param('all_status', 0, 'intval');

        if($all_status==1){
            $where['v.status'] =1;
        }elseif ($all_status==2){
            $where['v.status'] =0;
        }
        if(!empty($collect_type)){
            $where['type']=$collect_type;
        }
        $venue = $this->request->param('venue', 0, 'intval');
        if(!empty($venue)){
            $where['venue']=$venue;
        }else{
            $where['venue'] = ['in', UserModel::getCurrentVenue()];
        }
        $keyword = $this->request->param('keyword');
        if(!empty($keyword)){
            $where['p.name']= ['like', "%$keyword%"];
        }
        $where['delete_time']=0;

        //征集活动列表
        $collectModel = new ProductionCollectModel();
        $collect_list    = $collectModel
            ->alias('p')
            ->join('collect_production c','c.activity_id=p.id','left')
            ->join('venue v','v.id=p.venue','left')
            ->field('p.*,count(distinct c.userid) as pnum,count(c.id) as cnum,v.name as vname,v.status as vstatus')
            ->group('p.id')
            ->where($where)->order('id DESC')->paginate(15);

        // 获取分页显示
        $collect_list->appends($param);
        $page = $collect_list->render();

        //征集类型列表
        $collectTypeModel = new ProductionCollectTypeModel();
        $collectType_list    = $collectTypeModel->where(['status'=>1])->order('id DESC')->select();

        //区域列表
        $areaModel = new AreaModel();
        $area_id =!empty($param['area_id']) ? $param['area_id'] : 0;
        $areas = $areaModel->adminAreaTree($area_id);

        //场馆列表
        $venueModel = new VenueModel();
        $venue_list    = $venueModel->where(['id'=>['in', UserModel::getCurrentVenue()]])->order('id DESC')->select();

        $this->assign('collect_type', $collect_type);
        $this->assign('all_status', $all_status);
        $this->assign('area_id', isset($param['area_id']) ? $param['area_id'] : 0);
        $this->assign('venue', isset($param['venue']) ? $param['venue'] : 0);
        $this->assign('keyword', isset($keyword) ?$keyword : '');
        $this->assign('list', $collect_list);
        $this->assign('collect_type_list', $collectType_list);
        $this->assign('areas', $areas);
        $this->assign('venue_list', $venue_list);
        $this->assign('page', $page);

        return $this->fetch();
    }


    public function manage()
    {
        $id   = $this->request->param('id',0,'intval');
        $collect = new CollectProductionModel();
        $list=$collect->alias('c')
            ->where('c.activity_id',$id)
            ->join('user u','u.id =c.userid','left')
            ->field('c.*,u.user_login')->select()->toArray();
        $this->assign('list', $list);

        return $this->fetch();
    }


    /**
     * 添加征集
     */
    public function add()
    {
        //区域列表
        $areaModel=new AreaModel();
        $areas = $areaModel->adminAreaTree();

        //当前用户下文化馆列表
        $venueModel=new VenueModel();
        $where['id']= ['in', UserModel::getCurrentVenue()];
        $where['status']=1;
        $venues=$venueModel->where($where)->select();

        //征集类型列表
        $collectTypeModel=new ProductionCollectTypeModel();
        $type_list=$collectTypeModel->where(['status'=>1])->select();

        $this->assign('areas', $areas);
        $this->assign('venues', $venues);
        $this->assign('type_list', $type_list);

        return $this->fetch();
    }

    /**
     * 添加征集提交保存
     */
    public function addPost()
    {
        if ($this->request->isPost()) {
            $data   = $this->request->param();
            $post   = $data['post'];
            $result = $this->validate($post, 'ProductionCollect');

            if ($result !== true) {
                $this->error($result);
            }

            if($post['preview_start_time'] >$post['preview_end_time']){
                $this->error('预告开始时间不能大于预告结束时间');
            }
            if($post['collect_start_time'] >$post['collect_end_time']){
                $this->error('征集开始时间不能大于征集结束时间');
            }
            if($post['choose_start_time'] >$post['choose_end_time']){
                $this->error('评选开始时间不能大于评选结束时间');
            }
            if($post['publish_time'] <$post['choose_end_time']){
                $this->error('公示时间不能小于评选结束时间');
            }

            if (empty($post['thumb'])) {
                $this->error('请上传缩略图');
            }

            $collectModel = new ProductionCollectModel();
            $collectModel->adminAddCollect($post);

            $this->success('添加成功!', url('ProductionCollect/index'));
        }
    }

    /**
     * 编辑征集活动
     */
    public function edit()
    {
        $activity_id        = $this->request->param('id', 0, 'intval');
        $production_collect = ProductionCollectModel::get($activity_id);

        //当前用户下文化馆列表
        $where['id']= ['in', UserModel::getCurrentVenue()];
        $where['status']=1;
        $venueModel = new VenueModel();
        $venues = $venueModel->where($where)->select();

        //区域列表
        $areaModel=new AreaModel();
        $area_id = VenueModel::where(['id' => $production_collect->venue ])->value('address',0);
        $areas = $areaModel->adminAreaTree($area_id);

        //活动类型
        $collectTypeModel=new ProductionCollectTypeModel();
        $type_list=$collectTypeModel->where(['status'=>1])->select();
        $this->assign('areas', $areas);
        $this->assign('venues', $venues);
        $this->assign('type_list', $type_list);
        $this->assign('production_collect',$production_collect);

        return $this->fetch();
    }

    /**
     * 编辑征集活动提交保存
     */
    public function editPost()
    {
        $data   = $this->request->param();
        $post   = $data['post'];
        $result = $this->validate($post, 'ProductionCollect');
        if ($result !== true) {
            $this->error($result);
        }

        if($post['preview_start_time'] >$post['preview_end_time']){
            $this->error('预告开始时间不能大于预告结束时间');
        }
        if($post['collect_start_time'] >$post['collect_end_time']){
            $this->error('征集开始时间不能大于征集结束时间');
        }
        if($post['choose_start_time'] >$post['choose_end_time']){
            $this->error('评选开始时间不能大于评选结束时间');
        }
        if($post['publish_time'] <$post['choose_end_time']){
            $this->error('公示时间不能小于评选结束时间');
        }

        if (empty($post['thumb'])) {
            $this->error('请上传缩略图');
        }

        $collectModel = new ProductionCollectModel();
        $collectModel->adminEditActivity($data['post']);
        $this->success("保存成功！", url("production_collect/index"));
    }

    /**
     * 征集活动删除
     */
    public function delete()
    {
        $param           = $this->request->param();
        $activityModel = new ProductionCollectModel();
        if(isset($param['id'])){
            $id=$param['id'];
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
     * 作品审核
     */
    public function shenhe()
    {
        $id   = $this->request->param('id',0,'intval');
        $status   = $this->request->param('status',0,'intval');
        $collect = new CollectProductionModel();
        $list=$collect->where(['id'=>$id])->Update(['status' => $status]);
        $this->success('修改成功!');
    }
    /**
     * 作品审核
     */
    public function chakan()
    {
        $id = $this->request->param('id',0,'intval');

        $collect = new CollectProductionModel();
        $list=$collect->alias('c')
            ->where('c.id',$id)
            ->join('user u','u.id =c.userid','left')
            ->field('c.*,u.user_login')->find();
        $list['content']=$collect->getPostContentAttr($list['content']);
        $this->assign('list' , $list);

        return $this->fetch();
    }
    /**
     *作品公示
     * $activity_id 征集活动id
     */
    public function publish(){
        $id = $this->request->param('id',0,'intval');
        if($this->request->isPost()){
            $awardModel =new ProductionCollectAwardModel();
            $award_publicity=$this->request->param('content','','string');
            $activity_id= $this->request->param('activity_id','0','intval');
            if(empty($award_publicity)){
                $this->error('获奖公布不能为空');
            }
            $award_name=$_POST['award_name'];
            $award_production=$_POST['award_production'];
            $award_info=[];
            foreach($award_name as $key=>$v){
                $tmp['award_name']=$v;
                $tmp['award_production']=$award_production[$key];
                array_push($award_info,$tmp);
            }
            $data['award_publicity']=$awardModel->setPostContentAttr($award_publicity);
            $data['award_production']=serialize($award_info);
            $data['activity_id']=$activity_id;
            $res=$awardModel->where(['activity_id'=>$activity_id])->find();
            if(!empty($res)){
                $awardModel->where(['activity_id'=>$activity_id])->data($data)->update();
            }else{
                $awardModel->insert($data);
            }
            Db::name('production_collect')->where(['id'=>$activity_id])->update(['publish_time'=>time()]);
            $this->success('奖项公布成功');

        }
        //征集活动下的用户作品
        $collectProductionModel =new CollectProductionModel();
        $collectProductions=$collectProductionModel->where(['activity_id'=>$id,'status'=>1])->select();

        //奖项公示信息
        $awardModel =new ProductionCollectAwardModel();
        $award=$awardModel->where(['activity_id'=>$id])->find();
        $award['award_publicity']=$awardModel->getPostContentAttr($award['award_publicity']);
        $award_info=!empty($award['award_production'])?unserialize($award['award_production']):'';


        $this->assign('collect_production',$collectProductions);
        $this->assign('award_info',$award_info);
        $this->assign('award',$award);
        $this->assign('activity_id',$id);

        return $this->fetch();
    }

    /**
     * 作品评论列表
     */
    public function comments_list(){

        //1.获取父级评论(集合对象）
        $ProductionCommentsModel = new ProductionCommentsModel();
        $parentlist = $ProductionCommentsModel->alias('pc')->where('level_pid', '=', 0)
            ->order('create_time DESC')->join('collect_production cp', 'pc.zuopin_id=cp.id', 'left')
            ->join('user u', 'pc.userid=u.id')
            ->field('pc.*,cp.name as title,u.user_nickname')->paginate(10);

        //将父类转化为二维数组
        $parentArr = $parentlist->toArray();
        $parentArr = $parentArr['data'];

        //获取分页显示
        $parentlist->appends($parentArr);
        $page = $parentlist->render();

        //获取下一级分类
        foreach ($parentArr as $key => $value) {
            $sonlist = Db::name('production_comments')->alias('pc')->where('level_pid', '=', $value['id'])
                ->order('id asc')->join('collect_production cp', 'pc.zuopin_id=cp.id', 'left')
                ->join('user u', 'pc.userid=u.id')
                ->field('pc.*,cp.name as title,u.user_nickname')->select();

            $sonArr = $sonlist->toArray();

            $num = count($sonArr);
            for ($i = 0; $i < $num; $i++) {
                $sonArr[$i]['is_admin'] = $sonArr[$i]['userid'] != $value['userid'] ? 1 : 0;
                if ($i == ($num - 1)) {
                    $sonArr[$i]['comment'] = '&nbsp;&nbsp;&nbsp;&nbsp;└─ ' . $sonArr[$i]['comment'];
                } else {
                    $sonArr[$i]['comment'] = '&nbsp;&nbsp;&nbsp;&nbsp;├─' . $sonArr[$i]['comment'];
                }
            }
            $parentArr[$key]['son'] = $sonArr;

        }

        $endArr = $parentArr;

        $this->assign('endArr', $endArr);
        $this->assign("page",$page);
        return $this->fetch('production_comments');
    }

    /**
     * 评论回复
     */
    public function comments_reply()
    {
        $id = $this->request->param('id');
        $comment_info = Db::name('production_comments')->alias('pc')->where('pc.id', '=', $id)
            ->order('updatetime DESC')->join('collect_production cp', 'pc.zuopin_id=cp.id', 'left')
            ->join('user u', 'pc.userid=u.id')
            ->field('pc.*,cp.name as title,u.user_nickname')
            ->find();
        $this->assign('array', $comment_info);
        return $this->fetch('comments_reply');
    }

    /**
     * 评论回复提交
     */
    public function replyDo()
    {
        //接收数据
        $id = input('param.id1');
        $com = input('param.comment','','trim');
        $content = input('param.content', '', 'trim');
        if($com == ''){
            $this->error('评论不能为空');
        }
        if($content == ''){
            $this->error('回复不能为空');
        }
        $commentModel = new ProductionCommentsModel;
        $comment = $commentModel->where('id', $id)->find();

        $data['zuopin_id'] = $comment['zuopin_id'];
        $data['userid'] = cmf_get_current_admin_id();
        $data['replyid'] = $comment['userid'];
        $data['updatetime'] = time();
        $data['create_time'] = time();
        $data['parentid'] = $id;
        $data['comment'] = $content;
        $pid = $comment['level_pid'];
        if ($pid == 0) {
            $data['level_pid'] = $comment['id'];
        } else {
            $data['level_pid'] = $pid;
        }

        //将数据存到数据库
        $result = $commentModel->validate(true)->allowField(true)->save($data);
        $commentModel->where('id',$id )->update(['status' => 1,'comment'=>$com]);
        if ($result) {
          /*  $sys = new SysMessageModel();
            $id = $commentModel->id;
            $sys->publish($id, 1);*/
            $this->success('保存回复成功', 'production_collect/comments_list');
        } else {
            $this->error($commentModel->getError());
        }
    }

    /**
     * 评论显示或隐藏
     */
    public function toggle()
    {
        $data = $this->request->param();
        $ProductionCommentsModel = new ProductionCommentsModel;
        if (isset($data['ids']) && !empty($data["display"])) {
            $ids = $this->request->param('ids/a');
            $ProductionCommentsModel->where(['id' => ['in', $ids]])->update(['status' => 1]);
            $this->success("更新成功！");
        }

        if (isset($data['ids']) && !empty($data["hide"])) {
            $ids = $this->request->param('ids/a');
            $ProductionCommentsModel->where(['id' => ['in', $ids]])->update(['status' => 0]);
            $this->success("更新成功！");
        }
    }

    //管理员回复内容的修改
    public function reply_comments_edit()
    {
        //接收评论id
        $id = $this->request->param('id');
        $array = Db::name('production_comments')->alias('pc')->where('pc.id', '=', $id)
            ->order('updatetime DESC')->join('collect_production cp', 'pc.zuopin_id=cp.id', 'left')
            ->join('user u', 'pc.userid=u.id')
            ->field('pc.*,cp.name as title,u.user_nickname')
            ->find();
        $parentid = $array['parentid'];

        $commer = Db::name('production_comments')->alias('pc')->where('pc.id', '=', $parentid)
            ->order('updatetime DESC')->join('collect_production cp', 'pc.zuopin_id=cp.id', 'left')
            ->join('user u', 'pc.userid=u.id')
            ->field('pc.*,cp.name as title,u.user_nickname')
            ->find();
        $this->assign('commer', $commer);
        $this->assign('array', $array);
        return $this->fetch();


    }
    //管理员回复内容的修改提交
    public function reply_comments_editDo()
    {
        //接受数据
        $data = $this->request->param();
        $com = input('param.comment', '', 'trim');
        $comment = input('param.reply', '', 'trim');
        $status = input('param.status', 0, 'intval');
        $id = input('param.id', 0, 'intval');
        $status = $status != 0 ? 1 : 0;
        if($com == ''){
            $this->error('评论不能为空');
        }
        if($comment == ''){
            $this->error('回复不能为空');
        }

        $commentsModel = new ProductionCommentsModel;
        $commentpid = $commentsModel->where('id',$id)->find();
        $level_pid = $commentpid['level_pid'];
        //将数据更新到数据库
        $commentsModel->where('id',$level_pid)->update(['comment'=>$com]);
        $commentsModel->where('id', $id)->update(['comment' => $comment, 'status' => $status, 'updatetime' => time()]);

        $this->success('修改成功', 'production_collect/comments_list');


    }
    /**
     * 评论修改
     */
    public function comments_update()
    {
        //接收id
        $id = $this->request->param('id');
        $comment_info = Db::name('production_comments')->alias('pc')->where('pc.id', '=', $id)
            ->order('updatetime DESC')->join('collect_production cp', 'pc.zuopin_id=cp.id', 'left')
            ->join('user u', 'pc.userid=u.id')
            ->field('pc.*,cp.name as title,u.user_nickname')
            ->find();
        $this->assign('comment_info', $comment_info);
        return $this->fetch();

    }
    /**
     * 评论修改提交
     */
    public function comments_updateDo()
    {
        $data = $this->request->param();
        $id = input('param.id', 0, 'intval');
        $status = input('param.status', 0, 'intval');
        $status = $status != 0 ? 1 : 0;
        $comment = input('param.comment', 0, 'trim');
        if($comment == ''){
            $this->error('评论不能为空');
        }
        $commentsModel = new ProductionCommentsModel;
        $result = $commentsModel->where('id', '=', $id)->update(['comment' => $comment, 'status' =>$status]);
        if ($result !== false) {
            $this->success('修改评论成功', 'comments_list');
        } else {
            $this->error($commentsModel->getError());
        }

    }
    /**
     * 评论删除
     */
    public function comments_delete()
    {
        //接受id
        $id = $this->request->param('id');
        $commentsModel = new ProductionCommentsModel();
        $result = $commentsModel->where('id', '=', $id)->delete();

        if ($result) {
            $this->success('回复删除成功', 'comments_list');
        } else {
            $this->error($commentsModel->getError());
        }
    }
}