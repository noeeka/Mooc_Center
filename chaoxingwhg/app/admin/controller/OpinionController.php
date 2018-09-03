<?php

/**
 * Created by PhpStorm.
 * User: 李德顺
 * Date: 2018/3/13
 * Time: 14:42
 */
namespace app\admin\controller;

use app\admin\model\ActivityTypeModel;
use app\admin\model\AreaModel;
use app\admin\model\OpinionIdeaModel;
use app\admin\model\UserModel;
use app\admin\model\DiscussionModel;
use app\admin\model\OptionModel;
use app\admin\model\PortalCategoryModel;
use app\admin\model\VenueModel;
use cmf\controller\AdminBaseController;
use think\Controller;
use think\Request;
use tree\Tree;
use think\Db;
class OpinionController extends AdminBaseController
{
    private $allCid = 32;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {

        $param = $this->request->param();
        $all_status = $this->request->param('all_status', 0, 'intval');
        //区域列表
        $areaModel=new AreaModel();
        $areas = $areaModel->adminAreaTree();

        //文化馆列表
        $admin_userid=cmf_get_current_admin_id();
        $venueModel = new VenueModel();
        if($admin_userid==1){
            $venues = $venueModel->select();
        }else{
            $user = DB::name('user')->where(["id" => $admin_userid])->find();
            $venues = $venueModel->where(['id'=>['in',$user['venue']]])->select();
        }

        $opinions = $this->getOpinionList();
        $opinions->appends($param);


        $opi = new OpinionIdeaModel();
        foreach ($opinions->items() as &$item) {
            $item->num = $opi->where(['opinion_id'=> $item->id , 'is_delete'=>1 ,'status' => 1])->count();
        }

        $this->assign('areas', $areas);
        $this->assign('venues', $venues);

        $this->assign('venue_id' , empty($param['venue_id']) ? 0 : $param['venue_id']);
        $this->assign('area_id' , empty($param['area_id']) ? 0: $param["area_id"] );
        $this->assign('all_status',$all_status);
        $this->assign('venue', array_column($venues->toArray() , null , 'id'));
        $this->assign('page', $opinions->render());
        $this->assign('statusID', isset($param['status']) ? $param['status'] : 0);
        $this->assign('list',  $opinions->items() );
        $this->assign('start_time',isset($param['start_time']) ? $param['start_time'] : 'up' );
        $this->assign('end_time',isset($param['end_time']) ? $param['end_time'] : 'up' );
        $this->assign('create_time',isset($param['create_time']) ? $param['create_time'] : 'down' );

       // var_dump(isset($param['start_time']) ? $param['start_time'] : 'down');
        return $this->fetch();
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //区域列表
        $areaModel=new AreaModel();
        $areas = $areaModel->adminAreaTree();

        //文化馆列表
        $admin_userid=cmf_get_current_admin_id();
        $venueModel = new VenueModel();

        if($admin_userid==1){
            $venues = $venueModel->select();
        }else{
            $user = DB::name('user')->where(["id" => $admin_userid])->find();
            $venues = $venueModel->where(['id'=>['in',$user['venue']]])->select();
        }

        //活动类型
        $activityTypeModel = new ActivityTypeModel();
        $type_list = $activityTypeModel->select();

        $this->assign('areas', $areas);
        $this->assign('venues', $venues);
        $this->assign('type_list', $type_list);

        return $this->fetch();
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        if ($this->request->isPost()) {
            $data   = $this->request->param();
            $validate = validate('Opinion');

            if(!$validate->check($data)){
                $this->error($validate->getError());
            }

            $data['start_time'] = strtotime( $data['start_time'] );
            $data['end_time'] = strtotime( $data['end_time'] );
            $data['create_time'] = time();
            $data['publish_time'] = strtotime($data['publish_time']);
            $data['is_delete'] = 1;

            if(
                $data['start_time'] >=  $data['end_time']
            ) {
                $this->error('结束时间不能小于开始时间');
            }

            $discussionModel = new DiscussionModel();
            $saveResult = $discussionModel->save($data);

            if($saveResult)
                $this->success('添加成功!', url('opinion/index'));
            else
                $this->error('添加失败');
        }
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $venueModel = new VenueModel();
        $areaModel = new AreaModel();
        $opinionModel = new DiscussionModel();
        $id = $this->request->param('id', 0, 'intval');
        $opinion = $opinionModel->find($id);
        if(empty($opinion))
            $this->error('option 不存在');

        $opinion = $opinion->toArray();

        //区域列表
        $address = $venueModel->where('id', $opinion['venue_id'])->value('address');
        $areas = $areaModel->adminAreaTree($address);
        $myVenue = $venueModel->where(['status'=>1, 'address'=>$address, 'id'=>['in', UserModel::getCurrentVenue()]])->select()->toArray();
        $myv = '';
        foreach ($myVenue as $v){
            $select = $v['id'] == $opinion['venue_id'] ? 'selected' : '';
            $myv .= "<option value='".$v['id']."' ".$select.">".$v['name']."</option>";
        }

        $venues = $venueModel->where(['status'=>1,'id'=>['in', UserModel::getCurrentVenue()]])->order(['list_order'=>'asc'])->select()->toArray();

        //活动类型
        $this->assign('areas', $areas);
        $this->assign('venues', json_encode($venues));
        $this->assign('my_venue', $myv);
        $this->assign('opinion' , $opinion);

        return $this->fetch();
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update()
    {
        $data   = $this->request->param();
        $id = $this->request->param('id' , '0' ,'intval');
        $validate = validate('Opinion');

        if(!$validate->check($data)){
            $this->error($validate->getError());
        }

        $data['start_time'] = strtotime( $data['start_time'] );
        $data['end_time'] = strtotime( $data['end_time'] );
        $data['publish_time'] = strtotime($data['publish_time']);

        if ( $data['publish_time'] < 0 || $data['publish_time'] > 2147483640 )
            $this->error('发布时间有误');
        if(
            $data['start_time'] >=  $data['end_time']
        ) {
            $this->error('结束时间不能小于开始时间');
        }

        $discussionModel = new DiscussionModel();
        $updateResult = $discussionModel->where('id' , $id )->update($data);
        $this->success("保存成功！", url("opinion/index"));
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $id = $this->request->param('id' , '0' , 'intval');
        $discussionModel = new DiscussionModel();

        if(!empty($id) ){
            $updateResult = $discussionModel->where(['id' => $id])->update( ['is_delete' => 0] );
            $this->success("删除成功！", url('opinion/index'));
        }
        $this->error('删除失败');
    }


    private function getOpinionList( $paginate = 10)
    {
        $where = [];
        $where['o.is_delete'] = 1;
        $order = "o.create_time DESC";
        $opinionModel = new DiscussionModel();
        $all_status = $this->request->param('all_status', 0, 'intval');

        if($all_status==1){
            $where['v.status'] =1;
        }elseif ($all_status==2){
            $where['v.status'] =0;
        }
        $keyword = $this->request->param('keyword', 0, 'trim');
        $venue   = $this->request->param('venue_id', 0, 'intval');
        $status   = $this->request->param('status', 0, 'intval');
        $start_time   = $this->request->param('start_time', 0, 'trim');
        $end_time   = $this->request->param('end_time', 0, 'trim');
        $create_time   = $this->request->param('create_time', 0, 'trim');

        if(!empty($keyword)){
            $where['o.title']= ['like', "%$keyword%"];
        }

        if(!empty($venue)) {
            $where['o.venue_id'] = $venue;
        }

        if(!empty($status)){
            if($status == 1) { //即将征集
                $where['o.start_time'] = [ '>', time() ];
                $where['o.end_time'] = ['>' , time()];
            } else if($status == 2) { //往期征集
                $where['o.start_time'] = ['<' , time()];
                $where['o.end_time'] =  ['<' , time()];
            } else { //征集中
                $where['o.start_time'] = [ '<=' , time() ];
                $where['o.end_time'] = [ '>' , time() ];
            }
        }

        $order = "o.publish_time desc";

        if(!empty($start_time) && $start_time == "up") {
            $order = "o.start_time asc";
        } else if (!empty($start_time) && $start_time == "down") {
            $order = "o.start_time desc";
        }

        if(!empty($end_time) && $end_time == "up") {
            $order = "o.end_time asc";
        } else if (!empty($end_time) && $end_time == "down") {
            $order = "o.end_time desc";
        }

        if(!empty($create_time) && $create_time == "up") {
            $order = "o.publish_time asc";
        } else if (!empty($create_time) && $create_time == "down") {
            $order = "o.publish_time desc";
        }

        $opinion_list  = $opinionModel
            ->alias('o')
            ->join('venue v','o.venue_id=v.id')
            ->where($where)->field('o.*,v.status as vstatus')
            ->order($order)->paginate($paginate);
        // 获取分页显示
        //var_dump($opinionModel->getLastSql());
        return $opinion_list;
    }

    public function getTree($whe= [],$selectId = 0, $currentCid = 0, $parent_id = 0)
    {
        $where = [];
        if(!empty($whe)){
            $where = $whe;
        }
        $where['status'] =1;

        if (!empty($currentCid)) {
            $where['id'] = ['neq', $currentCid];
        }
        $areasModel = new  AreaModel();
        $areas = $areasModel->where($where)->select()->toArray();

        $tree = new Tree();
        $tree->icon = ['&nbsp;&nbsp;│', '&nbsp;&nbsp;├─', '&nbsp;&nbsp;└─'];
        $tree->nbsp = '&nbsp;&nbsp;';

        $newAreas = [];
        foreach ($areas as $item) {
            $item['selected'] = $selectId == $item['id'] ? "selected" : "";
            array_push($newAreas, $item);
        }

        $tree->init($newAreas);
        $str = '<option value=\"{$id}\" {$selected}>{$spacer}{$name}</option>';
        $treeStr = $tree->getTree($parent_id, $str);

        return $treeStr;
    }

    public function top()
    {
        $param           = $this->request->param();
        $discussionModel = new DiscussionModel();

        if (isset($param['ids']) && isset($param["yes"])) {
            $ids = $this->request->param('ids/a');
            $where['is_delete'] = 1;
            $where['is_top'] = 1;
            $isTopCount = [];
            $queryTopResult = $discussionModel->where($where)->select();
            $queryTopResult->isEmpty() ? : $isTopCount = $queryTopResult->toArray();

            if(count($isTopCount) >= 2 || count($ids) > 2){
                $this->error("最多置顶两条！", '');
            }else{
                $discussionModel->where(['id' => ['in', $ids]])->update(['is_top' => 1]);
                $this->success("置顶成功！", '');
            }
        }

        if (isset($_POST['ids']) && isset($param["no"])) {
            $ids = $this->request->param('ids/a');
            $discussionModel->where(['id' => ['in', $ids]])->update(['is_top' => 0]);

            $this->success("取消置顶成功！", '');
        }
    }

}
