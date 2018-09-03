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

use app\admin\model\UserModel;
use cmf\controller\AdminBaseController;
use app\admin\model\PerformModel;
use app\admin\model\PerformTypeModel;
use app\admin\model\VenueModel;
use app\admin\model\AreaModel;
use lib\ExcelOutput;
use think\Db;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class CultureController extends AdminBaseController
{
    const PERFORM_TYPE = 1;

    /**
     * 活动列表
     */
    public function index()
    {
        $param = $this->request->param();
        $all_status = $this->request->param('all_status', 0, 'intval');
        if($all_status==1){
            $where['b.status'] =1;
        }elseif ($all_status==2){
            $where['b.status'] =0;
        }
        $address = isset($param['address']) ? intval($param['address']) : 0;
        $venue = isset($param['venue']) ? intval($param['venue']) : 0;
        $areas = (new AreaModel())->adminAreaTree($address);
        $where = $this->getWhere();
        $performModel = new PerformModel();
        $activity_list = $performModel
            ->alias('a')
            ->join('venue b', 'a.venue = b.id')
            ->where($where)
            ->field('a.*,b.address,b.name as venue_name,b.status as vstatus')
            ->order('id DESC')
            ->paginate(15)
            ->each(function ($item) {
                $type = PerformTypeModel::get($item['typeid']);
                $item['type'] = $type == null ? '' : $type->name;
                $now = time();
                if ($item['start_time'] > $now) {
                    $time_status = '即将开始';
                } elseif ($item['start_time'] <= $now && $now <= $item['end_time']) {
                    $time_status = '正在进行';
                } else {
                    $time_status = '已过期';
                }
                $item['time_status'] = $time_status;
                $address = AreaModel::get($item['address']);
                $item['address'] = $address == null ? '' : $address->name;
                $owner = UserModel::get($item['owner_id']);
                $item['owner'] = $owner == null ? '' : $owner->user_nickname;
                return $item;
            });
        // 获取分页显示
        $activity_list->appends($param);
        $page = $activity_list->render();
        //活动类型列表
        $performTypeModel = new PerformTypeModel();
        $activity_type_list = $performTypeModel->order('id DESC')->select();

        //场馆列表
        $venueModel = new VenueModel();
        $venue_list    = $venueModel
            ->where(['id'=>['in', UserModel::getCurrentVenue()]])
            ->order('id DESC')
            ->select();

        $this->assign('areas', $areas);
        $this->assign('all_status', $all_status);
        $this->assign('venue', $venue);
        $this->assign('venue_list', $venue_list);
        $this->assign('param', $param);
        $this->assign('list', $activity_list);
        $this->assign('activity_type_list', $activity_type_list);
        $this->assign('page', $page);

        return $this->fetch();
    }

    public function output()
    {
        $where = $this->getWhere();
        $list = (new PerformModel())
            ->alias('a')
            ->join('venue b', 'a.venue = b.id')
            ->field('a.id,a.title,a.typeid,a.org,a.start_time,a.end_time,b.name as venue_name,b.address,a.start_time as status,a.owner_id,a.create_time,a.vote_num')
            ->where($where)
            ->order('id DESC')
            ->select()
            ->toArray();
        $field = ['ID', '节目名称', '节目类型','演出单位','开始时间','结束时间','所属场馆','所属区域','状态','创建账户','创建时间','总投票数'];
        $performTypeModel = new PerformTypeModel();
        $areaModel = new AreaModel();
        $userModel = new UserModel();
        $now = time();
        foreach ($list as &$v){
            $v['typeid'] = $performTypeModel->where('id', $v['typeid'])->value('name', '');
            if($v['end_time'] < $now){
                $v['status'] = '已过期';
            }elseif($v['start_time'] > $now ){
                $v['status'] = '即将开始';
            }else{
                $v['status'] = '正在进行';
            }
            $v['start_time'] = date('Y-m-d H:i',$v['start_time']);
            $v['end_time'] = date('Y-m-d H:i',$v['end_time']);
            $v['address'] = $areaModel->where('id', $v['address'])->value('name', '');
            $v['owner_id'] = $userModel->where('id', $v['owner_id'])->value('user_nickname', '');
            $v['create_time'] = date('Y-m-d H:i',$v['create_time']);
        }
        $excelOutput = new ExcelOutput('文化点单统计', $field, $list);
        $excelOutput->outputBrowser();
    }

    function getWhere()
    {
        $param = $this->request->param();
        $status = isset($param['status']) ? intval($param['status']) : 0;
        $where = ['delete_time' => 0, 'type' => self::PERFORM_TYPE];
        $now = time();
        if ($status == 1) {
            //未开始
            $where['a.start_time'] = ['gt', $now];
        } elseif ($status == 2) {
            //进行中
            $where['a.start_time'] = ['elt', $now];
            $where['a.end_time'] = ['egt', $now];
        } elseif ($status == 3) {
            //已过期
            $where['a.end_time'] = ['lt', $now];
        }
        $keyword = $this->request->param('keyword');
        if (!empty($keyword)) {
            $where['title'] = ['like', "%$keyword%"];
        }
        if (isset($param['start_time'])) {
            $start_time = strtotime($param['start_time']);
            if ($start_time !== false) $where['start_time'] = ['egt', strtotime($param['start_time'])];
        }
        if (isset($param['end_time'])) {
            $end_time = strtotime($param['end_time']);
            if ($end_time !== false) $where['end_time'] = ['elt', $end_time];
        }
        $areaModel = new AreaModel();
        $address = isset($param['address']) ? intval($param['address']) : 0;
        if ($address != 0) {
            $address_arr = $areaModel->getTreeIds($address);
            array_unshift($address_arr, $address);
            $where['b.address'] = ['in', $address_arr];
        }
        return $where;
    }

    /**
     * 活动查看
     */
    public function info()
    {
        $id = $this->request->param('id', 0, 'intval');

        $performModel = new PerformModel();
        $activity = $performModel->alias('a')->where('a.id', $id)->join('area ar', 'a.area =ar.id', 'left')->join('venue v', 'a.venue=v.id', 'left')->field('a.*,ar.name as aname,v.name as vname')->find();

        //已报名人数
        $yibaoming = Db::name('activity_baoming')->where('activity_id', $id)->count();
        //已报名人信息
        $user_list = Db::name('activity_baoming')->alias('a')->join('user u', 'a.user_id=u.id', 'left')->field('u.*')->select();
        $this->assign('activity', $activity);
        $this->assign('yibaoming', $yibaoming);
        $this->assign('user_list', $user_list);


        return $this->fetch();
    }

    /**
     * 添加活动
     */
    public function add()
    {
        //区域列表
        $areaModel = new AreaModel();
        $areas = $areaModel->adminAreaTree();

        //文化馆列表
        $admin_userid = cmf_get_current_admin_id();
        $venueModel = new VenueModel();

        if ($admin_userid == 1) {
            $venues = $venueModel->select();
        } else {
            $user = DB::name('user')->where(["id" => $admin_userid])->find();
            $venues = $venueModel->where(['id' => ['in', $user['venue']]])->select();
        }

        //活动类型
        $performTypeModel = new PerformTypeModel();
        $type_list = $performTypeModel->where(['type' => self::PERFORM_TYPE, 'status' => 1])->select();

        $this->assign('areas', $areas);
        $this->assign('venues', $venues);
        $this->assign('type_list', $type_list);

        return $this->fetch();
    }

    /**
     * 添加活动提交保存
     */
    public function addPost()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $post = $data['post'];
            $result = $this->validate($post, 'Perform');

            if ($result !== true) {
                $this->error($result);
            }

            $performModel = new PerformModel();

            if (!empty($data['photo_names']) && !empty($data['photo_urls'])) {
                $data['post']['thumb'] = [];
                foreach ($data['photo_urls'] as $key => $url) {
                    $photoUrl = cmf_asset_relative_url($url);
                    array_push($data['post']['thumb'], ["url" => $photoUrl, "name" => $data['photo_names'][$key]]);
                }
            }
            $data['post']['owner_id'] = cmf_get_current_admin_id();
            $data['post']['status'] = 1;
            $performModel->adminAddActivity($data['post']);
            $this->success('添加成功!', url('Culture/index'));
        }
    }

    /**
     * 编辑活动
     */
    public function edit()
    {
        $areaModel = new AreaModel();
        $venueModel = new VenueModel();

        $id = $this->request->param('id', 0, 'intval');
        $activity = PerformModel::get($id);

        //区域列表
        $area = VenueModel::where('id', $activity->venue)->value('address', 0);
        $areas = $areaModel->adminAreaTree($area);
        $yanchu_areas = $areaModel->adminAreaTree($activity->areas);
        $area_names = $areaModel->where(['id' => ['in', explode(',', $activity->areas)]])->column('name');
        //文化馆列表
        $address = $venueModel->where('id', $activity->venue)->value('address');
        $where['id']= ['in', UserModel::getCurrentVenue()];
        $where['address'] = $address;
        $where['status'] = 1;
        $venues = $venueModel->where($where)->select();


        //活动类型
        $performTypeModel = new PerformTypeModel();
        $type_list = $performTypeModel->where(['status' => 1, 'type' => self::PERFORM_TYPE])->select();
        $this->assign('areas', $areas);
        $this->assign('yanchu_areas', $yanchu_areas);
        $this->assign('area_names', $area_names);
        $this->assign('venues', $venues);
        $this->assign('type_list', $type_list);
        $this->assign('activity', $activity);
        return $this->fetch();
    }

    /**
     * 编辑活动
     */
    public function view()
    {
        //区域列表
        $areaModel = new AreaModel();

        $id = $this->request->param('id', 0, 'intval');
        $activity = PerformModel::get($id);
        //文化馆列表
        $admin_userid = cmf_get_current_admin_id();
        $venueModel = new VenueModel();

        if ($admin_userid == 1) {
            $venues = $venueModel->select();
        } else {
            $user = DB::name('user')->where(["id" => $admin_userid])->find();
            $venues = $venueModel->where(['id' => ['in', $user['venue']]])->select();
        }
        //点单统计
        $diandan = $areaModel->alias('a')
            ->join('__PERFORM_DIANDAN__ p', 'p.area_id = a.id')
            ->field('p.area_id,a.name,count(p.id) as num , a.path')
            ->where('perform_id', $id)
            ->group('area_id')
            ->select();

        $dianCountSum = 0;

        if (!$diandan->isEmpty()) {
            $area = Db::name('area')->where('status', 1)->field('name,id')->select();
            $area && $area = array_column( $area->toArray() , 'name', 'id');
            $dianNums = array_column($diandan->toArray() , 'num' ,null);
            $dianCountSum = array_sum($dianNums);
            $diandan->each(function ($item) use ($area , $dianCountSum) {
                if (empty($item['path']))
                    return $item;
                $paths = explode('-' , $item['path']);
                if (count($paths) <= 1) {
                    return $item;
                }
                if ($paths[0] == 0)
                    array_shift($paths);

                $parsePath = [];
                foreach ($paths as $path) {
                    array_push($parsePath , isset($area[$path]) ? $area[$path]: "未知" );
                }
                $item['parsePath'] = implode('-' , $parsePath);

                //计算百分比
                $item['rate'] = number_format(($item['num'] / $dianCountSum) * 100,2) . "%";

                return $item;
            });
        }

        //活动类型
        $this->assign('venues', $venues);
        $this->assign('activity', $activity);
        $this->assign('diandan', $diandan);
        $this->assign('ratesum', $dianCountSum);
        return $this->fetch();
    }

    public function statistics()
    {
        $address = input('param.address', 0, 'intval');
        $keyword = input('param.keyword', '');
        $status = input('param.status', 0, 'intval');
        $start_time = input('param.start_time', '');
        $end_time = input('param.end_time', '');
        $areaModel = new AreaModel();
        $areas = $areaModel->getLeafsOption(0, $address);
        $where = ['p.type' => self::PERFORM_TYPE, 'delete_time' => 0];
        $now = time();
        if ($status == 1) {
            //未开始
            $where['p.start_time'] = ['gt', $now];
        } elseif ($status == 2) {
            //进行中
            $where['p.start_time'] = ['elt', $now];
            $where['p.end_time'] = ['egt', $now];
        } elseif ($status == 3) {
            //已过期
            $where['p.end_time'] = ['lt', $now];
        }
        if ($start_time != '') {
            $stime = strtotime($start_time);
            if ($stime !== false) {
                $where['p.start_time'] = ['egt', $stime];
            }
        }
        if ($end_time != '') {
            $etime = strtotime($end_time);
            if ($etime !== false) {
                $where['p.end_time'] = ['egt', $etime];
            }
        }
        if ($keyword != '') {
            $where['p.title'] = ['like', '%' . $keyword . '%'];
        }
        if ($address != 0) {
            $where['d.area_id'] = $address;
        }
        $performModel = new PerformModel();
        $list = $performModel
            ->alias('p')
            ->join('__PERFORM_DIANDAN__ d', 'p.id=d.perform_id')
            ->field('p.id,title,typeid,count(p.id) as num')
            ->where($where)
            ->group('p.id')
            ->order(['num' => 'desc'])
            ->select()
            ->toArray();
        $option1 = ['left' => [], 'right' => ['data1' => [], 'data2' => []]];
        $option2 = ['dataAxis' => [], 'data' => []];
        $performTypeModel = new PerformTypeModel();
        $num = 0;
        foreach ($list as $v) {
            //option  1
            if ($num >= 10) {
                $option1['left'][] = '其他';
                if (isset($option1['right']['data1'][-1])) {
                    $option1['right']['data1'][-1]['value'] += $v['num'];
                } else {
                    $typename = $performTypeModel->where('id', $v['typeid'])->value('name');
                    $option1['right']['data1'][-1] = ['name' => '其他', 'value' => $v['num']];
                }
                if (isset($option1['right']['data2'][-1])) {
                    $option1['right']['data2'][-1]['value'] += $v['num'];
                } else {
                    $option1['right']['data2'][-1] = ['value' => $v['num'], 'name' => '其他'];
                }
                //option 2
                $option2['dataAxis'][-1] = '其他';
                $option2['data'][-1] = isset($option2['data'][-1]) ? $option2['data'][-1] + $v['num'] : $v['num'];
            } else {
                $option1['left'][] = $v['title'];
                if (isset($option1['right']['data1'][$v['typeid']])) {
                    $option1['right']['data1'][$v['typeid']]['value'] += $v['num'];
                } else {
                    $typename = $performTypeModel->where('id', $v['typeid'])->value('name');
                    $option1['right']['data1'][$v['typeid']] = ['name' => $typename, 'value' => $v['num']];
                }
                $option1['right']['data2'][] = ['value' => $v['num'], 'name' => $v['title']];
                //option 2
                $option2['dataAxis'][] = $v['title'];
                $option2['data'][] = $v['num'];
            }
            $num++;
        }
        $option1['right']['data1'] = array_values($option1['right']['data1']);
        $option1['right']['data2'] = array_values($option1['right']['data2']);
        $option2['dataAxis'] = array_values($option2['dataAxis']);
        $option2['data'] = array_values($option2['data']);
        $param = $this->request->param();
        $this->assign('areas', $areas);
        $this->assign('status', $status);
        $this->assign('keyword', $keyword);
        $this->assign('start_time', isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('end_time', isset($param['end_time']) ? $param['end_time'] : '');
        $this->assign('option1', json_encode($option1));
        $this->assign('option2', json_encode($option2));
        return $this->fetch();
    }

    /**
     * 编辑活动提交保存
     */
    public function editPost()
    {
        $data = $this->request->param();
        $post = $data['post'];
        $result = $this->validate($post, 'Perform');
        if ($result !== true) {
            $this->error($result);
        }

        $performModel = new PerformModel();
        if (!empty($data['photo_names']) && !empty($data['photo_urls'])) {
            $data['post']['thumb'] = [];
            foreach ($data['photo_urls'] as $key => $url) {
                $photoUrl = cmf_asset_relative_url($url);
                array_push($data['post']['thumb'], ["url" => $photoUrl, "name" => $data['photo_names'][$key]]);
            }
        }

        $performModel->adminEditActivity($data['post']);
        $this->success("保存成功！", url("Culture/index"));
    }

    /**
     * 活动删除
     */
    public function delete()
    {
        $param = $this->request->param();
        $performModel = new PerformModel();

        if (isset($param['ids'])) {
            $ids = $this->request->param('ids/a');
            $performModel->where(['id' => ['in', $ids]])->update(['delete_time' => time()]);

            $this->success("删除成功！", '');
        }

        if (isset($param['id'])) {
            $id = intval($param['id']);
            $performModel->where('id', $id)->update(['delete_time' => time()]);
            $this->success("删除成功！", '');
        }
    }

    /**
     * 活动发布
     */
    public function publish()
    {
        $param = $this->request->param();
        $performModel = new PerformModel();
        if (isset($param['ids']) && isset($param["yes"])) {

            $ids = $this->request->param('ids/a');

            $performModel->where(['id' => ['in', $ids]])->update(['status' => 1, 'published_time' => time()]);
            $this->success("发布成功！", '');
        }

        if (isset($param['ids']) && isset($param["no"])) {
            $ids = $this->request->param('ids/a');

            $performModel->where(['id' => ['in', $ids]])->update(['status' => 0]);

            $this->success("取消发布成功！", '');
        }

    }
    /**
     * 获取当前地区下的场馆
     */
    public function area_venues(){
        $area_id=$this->request->param('area_id',0,'intval');
        $userModel= new UserModel();
        $venues=$userModel->area_venues($area_id);
        if(!empty($venues)){
            echo json_encode(['status'=>1,'data'=>$venues,'message'=>'场馆获取成功']);exit;
        }else{
            echo json_encode(['status'=>1,'data'=>'','message'=>'无场馆']);exit;
        }
    }
}