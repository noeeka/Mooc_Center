<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2018/3/9
 * Time: 15:50
 */

namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use app\admin\model\LiveBroadcastModel;
use app\admin\model\RoomModel;
use app\admin\model\AreaModel;
use app\admin\model\VenueModel;
use app\admin\model\UserModel;
use think\Db;

class LiveBroadcastController extends AdminBaseController
{
    /*
     * 直播列表
     */
    public function index()
    {
        $where = [];
        $param = $this->request->param();
        $all_status = $this->request->param('all_status', 0, 'intval');

        if($all_status==1){
            $where['v.status'] =1;
        }elseif ($all_status==2){
            $where['v.status'] =0;
        }

        $start_time1 = $this->request->param('start_time1', '');
        $start_time2 = $this->request->param('start_time2', '');
        if ($start_time1 != '') {
            $where['l.start_time'] = ['>=', strtotime($start_time1)];
        }
        if ($start_time2 != '') {
            $where['l.start_time'] = ['<=', strtotime($start_time2)];
        }
        if ($start_time1 != '' && $start_time2 != '') {
            $where['l.start_time'] = [['>=', strtotime($start_time1)], ['<=', strtotime($start_time2)]];
        }

        $end_time1 = $this->request->param('end_time1', '');
        $end_time2 = $this->request->param('end_time2', '');
        if ($end_time1 != '') {
            $where['l.end_time'] = ['>=', strtotime($end_time1)];
        }
        if ($end_time2 != '') {
            $where['l.end_time'] = ['<=', strtotime($end_time2)];
        }
        if ($end_time1 != '' && $end_time2 != '') {
            $where['l.end_time'] = [['>=', strtotime($end_time1)], ['<=', strtotime($end_time2)]];
        }

        $keyword = $this->request->param('keyword');
        if (!empty($keyword)) {
            $where['l.live_name'] = ['like', "%$keyword%"];
        }

        $venue = $this->request->param('venue_id');
        if (!empty($venue)) {
            $where['venueid'] = $venue;
        }

        $status = $this->request->param('status');

        if (!empty($status)) {
            if ($status == 1) {
                //即将开始
                $where['l.start_time'] = ['>', time()];
            } else if ($status == 2) {
                //进行中
                $where['l.start_time'] = ['<=', time()];
                $where['l.end_time'] = ['>=', time()];
            } else {
                //直播结束
                $where['l.end_time'] = ['<', time()];
            }
        }
        $where['l.type']=0;
        //直播列表
        $liveBroadcast = new LiveBroadcastModel();
        $live = $liveBroadcast->alias('l')
            ->order('start_time ASC')->join('venue v', 'l.venueid=v.id', 'left')->where($where)
            ->field('l.*,v.name,v.status as vstatus')->paginate(10);

        //获取分页显示
        $live -> appends($param);
        $page = $live->render();


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

        //区域列表
        $venueModel = new VenueModel();
        $venue_list = $venueModel->where(['status' => 1,'id'=>['in', UserModel::getCurrentVenue()]])->select()->toArray();
        $this->assign('start_time1', isset($param['start_time1']) ? $param['start_time1'] : '');
        $this->assign('start_time2', isset($param['start_time2']) ? $param['start_time2'] : '');
        $this->assign('end_time1', isset($param['end_time1']) ? $param['end_time1'] : '');
        $this->assign('end_time2', isset($param['end_time2']) ? $param['end_time2'] : '');
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');
        $this->assign('status', isset($param['status']) ? $param['status'] : '');
        $this->assign('all_status',$all_status);
        $this->assign('areas', $areas);
        $this->assign('venues', $venues);

        $this->assign('venue_id' , empty($param['venue_id']) ? 0 : $param['venue_id']);
        $this->assign('area_id' , empty($param['area_id']) ? 0: $param["area_id"] );

        $this->assign('live', $live);
        $this->assign("page",$page);
        return $this->fetch();
    }

    public function add()
    {
        $areaModel = new AreaModel();
        $areas = $areaModel->adminAreaTree();
        $venueModel = new VenueModel();
        $venues = $venueModel->where(['status' => 1, 'id' => ['in', UserModel::getCurrentVenue()]])->order(['list_order' => 'asc'])->select()->toArray();
        $this->assign('venues', json_encode($venues));
        $this->assign('areas', $areas);
        return $this->fetch();
    }


    public function addPost()
    {
        $liveBroadcast = new LiveBroadcastModel;
        $result = $liveBroadcast->validate(true)->allowField(true)->save($this->request->param());
        if ($result) {
            $this->success('添加直播成功', 'LiveBroadcast/index');
        } else {
            $this->error($liveBroadcast->getError());
        }

    }

    public function edit()
    {
        $id = $this->request->param('id');
        $liveBroadcastLModel = new LiveBroadcastModel;
        $lives = $liveBroadcastLModel->alias('l')->where('l.id', '=', $id)
            ->order('start_time ASC')->join('venue v', 'l.venueid=v.id', 'left')
            ->field('l.*,v.name,v.address')->find();

        //区域列表
        $areaModel = new AreaModel();
        $areas = $areaModel->adminAreaTree($lives['address']);

        //文化馆列表
        $admin_userid = cmf_get_current_admin_id();
        $venueModel = new VenueModel();
        if ($admin_userid == 1) {
            $venues = $venueModel->select();
            $venue_inaddress = $venueModel->where('address', $lives['address'])->select();
        } else {
            $user = DB::name('user')->where(["id" => $admin_userid])->find();
            $venues = $venueModel->where(['id' => ['in', $user['venue']]])->select();
            $venue_inaddress = $venueModel->where(['id' => ['in', $user['venue']], 'address' => $lives['address']])->select();
        }

        $this->assign('id', $id);
        $this->assign('venues', $venues);
        $this->assign('venue_inaddress', $venue_inaddress);
        $this->assign('lives', $lives);
        $this->assign('areas', $areas);
        return $this->fetch();
    }

    public function editPost()
    {
        $data = $this->request->param();
        $result = $this->validate($data, 'LiveBroadcast');
        if ($result !== true) {
            $this->error($result);
        }
        $data['start_time'] = $this->request->param('start_time');
        $data['end_time'] = $this->request->param('end_time');
        $liveBroadcastLModel = new LiveBroadcastModel;
        $result = $liveBroadcastLModel->allowField(true)->update($data);
        if ($result) {
            $this->success("修改成功！", url("LiveBroadcast/index"));
        } else {
            $this->error($liveBroadcastLModel->getError());
        }


    }

    public function delete()
    {
        $id = $this->request->param('id', 0, 'intval');
        LiveBroadcastModel::destroy($id);

        $this->success("删除成功！", url("LiveBroadcast/index"));
    }

}