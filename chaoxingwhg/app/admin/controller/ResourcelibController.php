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
use think\Db;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ResourcelibController extends AdminBaseController
{
    const PERFORM_TYPE = 2;
    /**
     * 活动列表
     */
    public function index()
    {
        $param = $this->request->param();
        $where['delete_time']=0;
        $now = time();

        $venue = $this->request->param('venue', 0, 'intval');
        if(!empty($venue)){
            $where['venue']=$venue;
        }else{
            $where['venue'] = ['in', UserModel::getCurrentVenue()];
        }
        $keyword = $this->request->param('keyword');
        if(!empty($keyword)){
            $where['title']= ['like', "%$keyword%"];
        }
        $start_time = $this->request->param('start_time', '');
        $end_time = $this->request->param('end_time', '');
        if ($start_time != '') {
            $where['a.create_time'] = ['>=', strtotime($start_time)];
        }
        if ($end_time != '') {
            $where['a.create_time'] = ['<=', strtotime($end_time)];
        }
        if ($start_time != '' && $end_time != '') {
            $where['a.create_time'] = [['>=', strtotime($start_time)], ['<=', strtotime($end_time)]];
        }
        $venueModel = new VenueModel();
        if($venue != 0 ){
            $where['b.id'] = $venue;
        }
        $where['a.type']=2;
        $where['a.delete_time']=0;
        $venue = $venueModel->where(['status'=>1])->select();
        $performModel = new PerformModel();
        $activity_list    = $performModel
            ->alias('a')
            ->join('venue b', 'a.venue = b.id')
            ->field('a.*,b.name as venue_name')
            ->where($where)
            ->order('id DESC')
            ->paginate(15)
            ->each(function($item, $key){
//                var_dump($item);
//                var_dump($item['typeid']);die;
                $type = PerformTypeModel::get($item['typeid']);
                $item['type'] = $type == null ? '': $type->name;
                $now = time();


                $owner = UserModel::get($item['owner_id']);
                $item['owner'] = $owner == null ? '': $owner->user_nickname;
                return $item;
            });
        // 获取分页显示
        $activity_list->appends($param);
        $page = $activity_list->render();
        //活动类型列表
        $performTypeModel = new PerformTypeModel();
        $activity_type_list    = $performTypeModel->order('id DESC')->select();
        //场馆列表
        $venueModel = new VenueModel();
        $venue_list    = $venueModel->where('id','in',UserModel::getCurrentVenue())->order('id DESC')->select();
        $this->assign('venue_list', $venue);
        $this->assign('venue', isset($param['venue']) ? $param['venue'] : '');
        $this->assign('keyword', isset($keyword) ?$keyword : '');
        $this->assign('list', $activity_list);
        $this->assign('activity_type_list', $activity_type_list);
        $this->assign('venue_list', $venue_list);
        $this->assign('page', $page);

        return $this->fetch();
    }

    /**
     * 活动查看
     */
    public function info()
    {
        $id   = $this->request->param('id',0,'intval');

        $performModel = new PerformModel();
        $activity=$performModel->alias('a')->where('a.id',$id)->join('area ar','a.area =ar.id','left')->join('venue v','a.venue=v.id','left')->field('a.*,ar.name as aname,v.name as vname')->find();

        //已报名人数
        $yibaoming=Db::name('activity_baoming')->where('activity_id',$id)->count();
        //已报名人信息
        $user_list=Db::name('activity_baoming')->alias('a')->join('user u','a.user_id=u.id','left')->field('u.*')->select();
        $this->assign('activity', $activity);
        $this->assign('yibaoming', $yibaoming);
        $this->assign('user_list', $user_list);


        return $this->fetch();
    }
    /**
     * 活动用户导出查看
     */
    public function export()
    {
        $performModel = new PerformModel();
        $user_list = $performModel
            ->alias('a')
            ->where(['a.type'=>2,'delete_time'=>0])
            ->join('venue b', 'a.venue = b.id')
            ->field('a.*,b.name as venue_name')
            ->order('id DESC')
            ->paginate(15)
            ->each(function($item, $key){
//                var_dump($item);
//                var_dump($item['typeid']);die;
                $type = PerformTypeModel::get($item['typeid']);
                $item['type'] = $type == null ? '': $type->name;
                $now = time();


                $owner = UserModel::get($item['owner_id']);
                $item['owner'] = $owner == null ? '': $owner->user_nickname;
                return $item;
            });
        $spreadsheet = new Spreadsheet();
        // Set document properties
        $spreadsheet->getProperties()->setCreator('Maarten Balliauw')
            ->setLastModifiedBy('Maarten Balliauw')
            ->setTitle('Office 2007 XLSX Test Document')
            ->setSubject('Office 2007 XLSX Test Document')
            ->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')
            ->setKeywords('office 2007 openxml php')
            ->setCategory('Test result file');
        // Add some data
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A1','活动名称' )
            ->setCellValue('B1','活动类型' )
            ->setCellValue('C1','开始时间')
            ->setCellValue('D1','结束时间')
            ->setCellValue('E1','所属场馆')
            ->setCellValue('F1','创建账号')
            ->setCellValue('G1','创建时间');

        for($i=0;$i<count($user_list);$i++){

            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A'.($i+2),$user_list[$i]['title'] )
                ->setCellValue('B'.($i+2),$user_list[$i]['type'])
                ->setCellValue('C'.($i+2),date('Y-m-d',$user_list[$i]['start_time']) )
                ->setCellValue('D'.($i+2),date('Y-m-d',$user_list[$i]['end_time']) )
                ->setCellValue('E'.($i+2),$user_list[$i]['venue_name'])
                ->setCellValue('F'.($i+2),$user_list[$i]['owner'])
                ->setCellValue('G'.($i+2),date('Y-m-d',$user_list[$i]['create_time']) );
        }

        // Redirect output to a client’s web browser (Xls)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="文化活动.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
        $writer->save('php://output');
        exit;

    }
    /**
     * 添加活动
     */
    public function add()
    {
        //区域列表
        $areaModel=new AreaModel();
        $areas = $areaModel->adminAreaTree();

        //文化馆列表
        $admin_userid=cmf_get_current_admin_id();
        $venueModel=new VenueModel();

        if($admin_userid==1){
            $venues=$venueModel->select();
        }else{
            $user = DB::name('user')->where(["id" => $admin_userid])->find();
            $venues=$venueModel->where(['id'=>['in',$user['venue']]])->select();
        }

        //活动类型
        $performTypeModel=new PerformTypeModel();
        $type_list=$performTypeModel->where(['type'=>self::PERFORM_TYPE, 'status'=>1])->select();

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
            $data   = $this->request->param();
            $post   = $data['post'];
            $result = $this->validate($post, 'Resourcelib');

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
            $data['post']['type'] = self::PERFORM_TYPE;
            $data['post']['owner_id'] = cmf_get_current_admin_id();
            $performModel->adminAddActivity($data['post']);
            $this->success('添加成功!', url('Resourcelib/index'));
        }
    }

    /**
     * 编辑活动
     */
    public function edit()
    {
        $id        = $this->request->param('id', 0, 'intval');
        $activity = PerformModel::get($id);
        $venueModel = new VenueModel();
        $address = $venueModel->where(['id'=>$activity['venue']])->find();

        //区域列表
        $areaModel=new AreaModel();
        $areas = $areaModel->adminAreaTree($address['address']);
        //文化馆列表
        $admin_userid=cmf_get_current_admin_id();
        $venueModel=new VenueModel();

        if($admin_userid==1){
            $venues=$venueModel->where(['status'=>1])->select();
            $venue=$venueModel->where(['id'=>$activity['venue']])->find();
            $thisvenues = $venueModel->where(['address'=>$venue['address'],'status'=>1])->select();
        }else{
            $user = DB::name('user')->where(["id" => $admin_userid])->find();
            $venue=$venueModel->where(['id'=>['in',$user['venue']],'id'=>$activity['venue']])->find();
            $venues = $venueModel->where(['id'=>['in',$user['venue']],'status'=>1])->select();
            $thisvenues = $venueModel->where(['id'=>['in',$user['venue']],'address'=>$venue['address'],'status'=>1])->select();
        }
        //活动类型
        $performTypeModel=new PerformTypeModel();
        $type_list=$performTypeModel->where(['status'=>1,'type'=>self::PERFORM_TYPE])->select();
        $this->assign('thisvenues',$thisvenues);
        $this->assign('areas', $areas);
        $this->assign('venues', $venues);
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
        $result = $this->validate($post, 'Resourcelib');
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
        $this->success("保存成功！", url("Resourcelib/index"));
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
        $param           = $this->request->param();
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
}