<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/2/28
 * Time: 16:04
 */

namespace app\admin\controller;


use app\admin\model\ActivityModel;
use app\admin\model\DiscussionModel;
use app\admin\model\LiveBroadcastModel;
use app\admin\model\PerformModel;
use app\admin\model\PortalCategoryModel;
use app\admin\model\PortalPostModel;
use app\admin\model\RoomModel;
use app\admin\model\SfzimgModel;
use app\admin\model\UserModel;
use app\admin\model\VenueModel;
use app\admin\service\PostService;
use cmf\controller\AdminBaseController;
use app\admin\model\AreaModel;
use think\Db;

class AdminVenueController extends AdminBaseController
{


    public function index()
    {

        $venues = Db::name('venue')->alias('v')
            ->join('area a', 'v.address=a.id', 'left')
            ->join('venue_type vt','v.venue_type =vt.id','left')
            ->order('publish_time desc')
            ->field('v.*,a.name as aname,vt.name as vtname , a.path')
            ->select();

        $area = Db::name('area')
            ->where('status', 1)
            ->field('name,id')
            ->select();

        $area && $area = array_column( $area->toArray() , 'name', 'id');

        $venues->each(function ($venue) use ($area) {
            if (empty($venue['path']))
               return $venue;

            $paths = explode('-' , $venue['path']);
            if (count($paths) <= 1) {
                return $venue;
            }

            if ($paths[0] == 0)
                array_shift($paths);

            $parsePath = [];
            foreach ($paths as $path) {
                array_push($parsePath , isset($area[$path]) ? $area[$path]: "未知" );
            }

            $venue['parsePath'] = implode('-' , $parsePath);
            return $venue;
        });

        $this->assign('venue', $venues);
        return $this->fetch();
    }


    public function add()
    {
        $areaModel = new AreaModel();
        $areasTree = $areaModel->adminAreaTree(0, 0);
        $this->assign('areas_tree', $areasTree);

        //总分管类型
        $venue_type = Db::name('venue_type')->select();
        $this->assign('venue_type', $venue_type);
        return $this->fetch();
    }


    public function addPost()
    {
        $data = $this->request->param();
        $venueModel = new VenueModel();
        $data['publish_time'] =  strtotime( $data['publish_time']);
        $result = $venueModel->validate(true)->allowField(true)->save($data);
        if ($result === false) {
            $this->error($venueModel->getError());
        }

        $this->success("添加成功！", url("AdminVenue/index"));
    }


    public function edit()
    {
        $id = $this->request->param('id', 0, 'intval');
        $venueModel = VenueModel::get($id);
        $areaModel = new AreaModel();
        $areasTree = $areaModel->adminAreaTree($venueModel['address'], 0);
        //总分管类型
        $venue_type = Db::name('venue_type')->select();
        $this->assign('venue_type', $venue_type);
        $this->assign('areas_tree', $areasTree);
        $this->assign('venue', $venueModel);
        return $this->fetch();
    }


    public function editPost()
    {
        $data = $this->request->param();
        $venueModel = new VenueModel();
        $data['publish_time'] =  strtotime( $data['publish_time']);
        $result = $venueModel->validate(true)->allowField(true)->isUpdate(true)->save($data);
        if ($result === false) {
            $this->error($venueModel->getError());
        }

        $this->success("保存成功！", url("AdminVenue/index"));
    }


    public function delete()
    {
        $id = $this->request->param('id', 0, 'intval');
        VenueModel::destroy($id);

        $this->success("删除成功！", url("AdminVenue/index"));
    }


    public function listOrder()
    {
        $venueModel = new  VenueModel();
        parent::listOrders($venueModel);
        $this->success("排序更新成功！");
    }

    public function datasource()
    {
        $data = [];
        $data['area_id'] = input('area_id', '0', 'intval');
        $data['venue_id'] = input('venue_id', '0', 'intval');
        //区域列表
        $areaModel = new AreaModel();
        $areas = $areaModel->adminAreaTree();
        //文化馆列表

        $venueModel = new VenueModel();
        $address = $venueModel->where('id', $data['venue_id'])->value('address');
        $areas = $areaModel->adminAreaTree($address);
        $myVenue = $venueModel->where(['status' => 1, 'address' => $address, 'id' => ['in', UserModel::getCurrentVenue()]])->select()->toArray();
        $myv = '';
        foreach ($myVenue as $v) {
            $select = $v['id'] == $data['venue_id'] ? 'selected' : '';
            $myv .= "<option value='" . $v['id'] . "' " . $select . ">" . $v['name'] . "</option>";
        }

        $venues = $venueModel->where(['status' => 1, 'id' => ['in', UserModel::getCurrentVenue()]])->order(['list_order' => 'asc'])->select()->toArray();

        $userModel = new UserModel();
        $sfzimg = new SfzimgModel();
        $sys_num = [];
        //用户数量
        $sys_num['user'] = $userModel->where(['user_type'=>2])->count(1);
        $sys_num['syfUser'] = $sfzimg->where(['status' => 2])->count(1);
        //管理员数量
        $sys_num['admin'] = $userModel->where(['user_type'=>1])->count(1);
        //黑名单
        $sys_num['blacklist'] = $userModel->where(['user_type'=>2, 'user_status'=>0])->count(1);
        //文化馆数量
        $sys_num['wenhuaguan'] = (new VenueModel())->count(1);

        $result = $this->getDataInfo($data);
//        halt($result);
//        var_dump($result);die;
//        var_dump($result['tabel_data']);
        $this->assign('masCount', max($result['num_arr']));
        $this->assign('sys_num', $sys_num);
        $this->assign('counts', json_encode($result['num_arr']));
        $this->assign('infos', $result['tabel_data']);


        $this->assign('dataAxis', json_encode($result['dataAxis']));
        $this->assign('areas', $areas);
        $this->assign('areas_id', $data['area_id']);
        $this->assign('venues', json_encode($venues));
        $this->assign('my_venue', $myv);

        return $this->fetch();
    }

    private function getDataInfo($data)
    {
        $liveBroadcast = new LiveBroadcastModel();
        $performModel = new PerformModel();
        $result = [];

        //信息咨询
        $this->addToResult($result, ['name'=>'信息资讯', 'num'=>$this->getArticleCount(8, $data['venue_id'])]);

        //资源展示
        $this->addToResult($result, ['name'=>'资源展示', 'num'=>$this->getArticleCount(12, $data['venue_id'])]);

        //活动信息
        $param = ['delete_time'=>0, 'volun_type'=>0];
        !empty($data['venue_id']) && $param['venue'] = $data['venue_id'];
        $activityCount = ActivityModel::where($param)->count(1);
        $this->addToResult($result, ['name'=>'活动报名', 'num'=>$activityCount]);

        //场馆预约
        $roomModel = new RoomModel();
        $param = [];
        if($data['venue_id'] != 0)$param['venue'] = $data['venue_id'];
        $roomCount = $roomModel->where($param)->count(1);
        $this->addToResult($result, ['name'=>'场馆预约', 'num'=>$roomCount]);

        //慕课信息
        $this->addToResult($result, ['name'=>'慕课', 'num'=>$this->getArticleCount(19, $data['venue_id'])]);

        //直播信息
        $param = [];
        !empty($data['venue_id']) && $param['venueid'] = $data['venue_id'];
        !empty($data['area_id']) && $param['areaid'] = $data['area_id'];
        $liveCount = $liveBroadcast->where($param)->field('id')->count();
        $this->addToResult($result, ['name'=>'直播', 'num'=>$liveCount]);

        //文化点单
        $param = [];
        $param['delete_time'] = 0;
        $param['type'] = 1;
        !empty($data['venue_id']) && $param['venue'] = $data['venue_id'];
        $cultureCount = $performModel->field('id')->where($param)->count();
        $this->addToResult($result, ['name'=>'文化点单', 'num'=>$cultureCount]);

        //民意征集
        $discussionModel = new DiscussionModel();
        $param = ['is_delete' => 1];
        !empty($data['venue_id']) && $param['venue_id'] = $data['venue_id'];
        $discussionCount = $discussionModel->where($param)->count(1);
        $this->addToResult($result, ['name'=>'民意征集', 'num'=>$discussionCount]);

        return $result;
    }

    function addToResult(&$result, $data){
        $result['num_arr'][] = $data['num'];
        $result['tabel_data'][] = $data;
        $result['dataAxis'][] = $data['name'];
    }

    function getArticleCount($cid, $venue_id){
        $param = [];
        $portalPostModel = new PortalPostModel();
        $portalCategoryModel = new PortalCategoryModel();
        $categoryArr = $portalCategoryModel->getTreeIds($cid);
        array_unshift($categoryArr, $cid);
        $param['category_id'] = ['in', $categoryArr];
        !empty($venue_id) && $param['venue'] = $venue_id;
        $param['post_type'] = 1;
        $param['create_time'] = ['>=', 0];
        $param['delete_time'] = 0;
        $join = [
            ['__PORTAL_CATEGORY_POST__ b', 'a.id = b.post_id']
        ];

        return $portalPostModel->alias('a')->join($join)->where($param)->group('a.id')->count();
    }


    public function toggle()
    {
        $data = $this->request->param();
        $venueModel = new VenueModel();

        if (isset($data['ids']) && !empty($data["display"])) {
            $ids = $this->request->param('ids/a');
            $venueModel->where(['id' => ['in', $ids]])->update(['status' => 1]);
            $this->success("更新成功！");
        }

        if (isset($data['ids']) && !empty($data["hide"])) {
            $ids = $this->request->param('ids/a');
            $venueModel->where(['id' => ['in', $ids]])->update(['status' => 0]);
            $this->success("更新成功！");
        }

        if (isset($data['ids']) && !empty($data["del"])) {
            $ids = $this->request->param('ids/a');
            $venueModel->where(['id' => ['in', $ids]])->delete();
            $this->success("删除成功！");
        }



    }

}