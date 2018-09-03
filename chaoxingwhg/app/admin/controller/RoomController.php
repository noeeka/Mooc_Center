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

use app\admin\model\AreaModel;
use app\admin\model\RoomTypeModel;
use app\admin\model\UserModel;
use app\admin\model\VenueModel;
use cmf\controller\AdminBaseController;
use app\admin\model\RoomModel;
use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use think\Db;

class RoomController extends AdminBaseController
{
    protected $targets = ["_blank" => "新标签页打开", "_self" => "本窗口打开"];

    /**
     * 友情链接管理
     * @adminMenu(
     *     'name'   => '友情链接',
     *     'parent' => 'admin/Setting/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 50,
     *     'icon'   => '',
     *     'remark' => '友情链接管理',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $param = $this->request->param();
        $all_status = $this->request->param('all_status', 0, 'intval');
        $where=[];
        if($all_status==1){
            $where['v.status'] =1;
        }elseif ($all_status==2){
            $where['v.status'] =0;
        }


        $area = Db::name('area')
            ->where('status', 1)
            ->field('name,id')
            ->select();

        $area && $area = array_column( $area->toArray() , 'name', 'id');

        $roomModel = new RoomModel();
        $links = $roomModel
            ->alias('r')
            ->where(['r.venue'=>['in', UserModel::getCurrentVenue2()]])
            ->join('venue v','r.venue=v.id')
            ->where($where)
            ->order('r.publish_time desc')
            ->field('r.*,v.name as venueName')->select();

        $this->assign('links', $links);
        $this->assign('all_status', $all_status);

        return $this->fetch();
    }

    /**
     * 添加友情链接
     * @adminMenu(
     *     'name'   => '添加友情链接',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加友情链接',
     *     'param'  => ''
     * )
     */
    public function add()
    {
        $areaModel = new AreaModel();
        $areas = $areaModel->adminAreaTree();
        $venueModel = new VenueModel();
        $venues = $venueModel->where(['status' => 1,'id'=>['in', UserModel::getCurrentVenue()]])->order(['list_order' => 'asc'])->select()->toArray();
        $roomTypeModel = new RoomTypeModel();
        $roomTypes = $roomTypeModel->where('status', 1)->select()->toArray();
        $this->assign('areas', $areas);
        $this->assign('venues', json_encode($venues));
        $this->assign('room_types', $roomTypes);
        return $this->fetch();
    }

    /**
     * 添加友情链接提交保存
     * @adminMenu(
     *     'name'   => '添加友情链接提交保存',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加友情链接提交保存',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        $data = $this->request->param();
        $roomModel = new RoomModel();
        $data['custom_preset_time'] = implode(',', $data['custom_preset_time']);

        if (!empty($data['photo_names']) && !empty($data['photo_urls'])) {
            $data['banner'] = [];
            foreach ($data['photo_urls'] as $key => $url) {
                $photoUrl = cmf_asset_relative_url($url);
                array_push($data['banner'], ["url" => $photoUrl, "name" => $data['photo_names'][$key]]);
            }

            $data['banner'] = json_encode($data['banner']);
        }
        else {
            $this->error("封面图必须上传");
        }

        $result = $roomModel->validate(true)->allowField(true)->save($data);
        if ($result === false) {
            $this->error($roomModel->getError());
        }


        $this->success("添加成功！", url('Room/edit', ['id' => $roomModel->id]));
    }

    /**
     * 编辑友情链接
     * @adminMenu(
     *     'name'   => '编辑友情链接',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑友情链接',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
        $id = $this->request->param('id', 0, 'intval');
        $room = RoomModel::get($id);
        if ($room['banner']) {
            $room['banner'] = json_decode($room['banner'] , 1);
        }
        $venueModel = new VenueModel();
        $areaModel = new AreaModel();
        $address = $venueModel->where('id', $room->venue)->value('address');
        $areas = $areaModel->adminAreaTree($address, 0);
        $venues = $venueModel->where(['status' => 1, 'id'=>['in', UserModel::getCurrentVenue()]])->order(['list_order' => 'asc'])->select()->toArray();
        $myVenues = $venueModel->where(['status' => 1,'id'=>['in', UserModel::getCurrentVenue()], 'address' => $address])->order(['list_order' => 'asc'])->select()->toArray();
        $selectVenue = $this->createOptions($myVenues, $room->venue);
        $roomTypeModel = new RoomTypeModel();
        $roomTypes = $roomTypeModel->where('status', 1)->select()->toArray();
        $selectType = $this->createOptions($roomTypes, $room->venue_type);
        $open_start_time_am_str = $this->createTimeOptions(range(1, 12), $room->open_start_time_am);
        $open_end_time_am_str = $this->createTimeOptions(range(1, 12), $room->open_end_time_am);
        $open_start_time_pm_str = $this->createTimeOptions(range(12, 24), $room->open_start_time_pm);
        $open_end_time_pm_str = $this->createTimeOptions(range(12, 24), $room->open_end_time_pm);
        $room->custom_preset_time = explode(',', $room->custom_preset_time);
        $this->assign('areas', $areas);
        $this->assign('venues', json_encode($venues));
        $this->assign('select_venue', $selectVenue);
        $this->assign('room', $room);
        $this->assign('room_types', $selectType);
        $this->assign('open_start_time_am_str', $open_start_time_am_str);
        $this->assign('open_end_time_am_str', $open_end_time_am_str);
        $this->assign('open_start_time_pm_str', $open_start_time_pm_str);
        $this->assign('open_end_time_pm_str', $open_end_time_pm_str);
        return $this->fetch();
    }

    public function view()
    {
        $id = $this->request->param('id', 0, 'intval');
        $room = RoomModel::get($id);
        $venueModel = new VenueModel();
        $areaModel = new AreaModel();
        $address = $venueModel->where('id', $room->venue)->value('address');
        $areas = $areaModel->adminAreaTree($address, 0);
        $venues = $venueModel->where(['status' => 1])->order(['list_order' => 'asc'])->select()->toArray();
        $myVenues = $venueModel->where(['status' => 1, 'address' => $address])->order(['list_order' => 'asc'])->select()->toArray();
        $selectVenue = $this->createOptions($myVenues, $room->venue);
        $roomTypeModel = new RoomTypeModel();
        $roomTypes = $roomTypeModel->where('status', 1)->select()->toArray();
        $selectType = $this->createOptions($roomTypes, $room->venue_type);
        $open_start_time_am_str = $this->createTimeOptions(range(0, 12), $room->open_start_time_am);
        $open_end_time_am_str = $this->createTimeOptions(range(0, 12), $room->open_end_time_am);
        $open_start_time_pm_str = $this->createTimeOptions(range(12, 24), $room->open_start_time_pm);
        $open_end_time_pm_str = $this->createTimeOptions(range(12, 24), $room->open_end_time_pm);
        //预约信息
        $apply_list = db('room_apply')->where('room_id', $id)->alias('r')->join('__USER__ u','r.user_id = u.id')->field('u.*,r.id as apply_id,r.start_time,r.end_time,r.create_time as apply_time')->select()->toArray();
        foreach ($apply_list as &$v){
            $v['durdate'] = date('Y年m月d日',$v['start_time']);
            $v['durtime'] = date('H:00',$v['start_time']).'~'. date('H:00',$v['end_time']);
        }
        $this->assign('areas', $areas);
        $this->assign('venues', json_encode($venues));
        $this->assign('select_venue', $selectVenue);
        $this->assign('room', $room);
        $this->assign('room_types', $selectType);
        $this->assign('open_start_time_am_str', $open_start_time_am_str);
        $this->assign('open_end_time_am_str', $open_end_time_am_str);
        $this->assign('open_start_time_pm_str', $open_start_time_pm_str);
        $this->assign('open_end_time_pm_str', $open_end_time_pm_str);
        $this->assign('apply_list', $apply_list);
        return $this->fetch();
    }

    public function export($id){
        $title = '场馆预定信息'.$id;
        $helper = new Sample();
        if ($helper->isCli()) {
            $helper->log('This example should only be run from a Web Browser' . PHP_EOL);
            return;
        }
        $apply_list = db('room_apply')->where('room_id', $id)->alias('r')->join('__USER__ u','r.user_id = u.id')->field('u.*,r.id as apply_id,r.start_time,r.end_time,r.create_time as apply_time')->select()->toArray();
        $row_num = count($apply_list);
        $tableField = ['apply_id'=>'id','durdate'=> '日期', 'durtime'=>'时间段','user_nickname'=>'昵称','mobile'=>'手机号','user_email'=>'邮箱','user_realname'=>'真实姓名','sex'=>'性别','birthday'=>'生日','address'=>'地址','apply_time'=>'预约时间'];
        $spreadsheet = new Spreadsheet();
        //设置表头
        $startChar = 'A';
        for($i = 0;$i<$row_num;$i++){
            $j = 0;
            foreach ($tableField as $key => $v){
                $coor = chr(ord($startChar)+$j).($i+1);
                if($i == 0){
                    $value = $v;
                }else{
                    $av = $apply_list[$i-1];
                    if($key == 'durdate'){
                        $value = date('Y年m月d日',$av['start_time']);
                    }elseif($key == 'durtime'){
                        $value = date('H:00',$av['start_time']).'~'. date('H:00',$av['end_time']);
                    }elseif($key == 'sex'){
                        if($av['sex'] == 1){
                            $value = '男';
                        }elseif($av['sex'] == 2){
                            $value = '女';
                        }else{
                            $value = '保密';
                        }
                    }elseif($key == 'birthday'){
                        $value = $av['birthday'] == 0 ? '':date('Y-m-d', $av['birthday']);
                    }elseif($key == 'apply_time'){
                        $value = $av['apply_time'] == 0 ? '':date('Y-m-d H:i', $av['apply_time']);
                    }else{
                        $value = $av[$key];
                    }
                }
                $spreadsheet->setActiveSheetIndex(0)->setCellValue($coor, $value);
                $j++;
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$title.'.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    private function createOptions($data, $selectId, $name = 'name', $id = 'id')
    {
        $selectType = '';
        foreach ($data as $v) {
            $selected = $v[$id] == $selectId ? 'selected' : '';
            $selectType .= '<option value="' . $v[$id] . '" ' . $selected . '>' . $v[$name] . '</option>';
        }
        return $selectType;
    }

    private function createTimeOptions($data, $selectId)
    {
        $selectType = '';
        foreach ($data as $v) {
            $selected = $v == $selectId ? 'selected' : '';
            $selectType .= '<option value="' . $v . '" ' . $selected . '>' . $v . '</option>';
        }
        return $selectType;
    }

    /**
     * 编辑友情链接提交保存
     * @adminMenu(
     *     'name'   => '编辑友情链接提交保存',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑友情链接提交保存',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {
        $data = $this->request->param();
        $roomModel = new RoomModel();
        if (!empty($data['photo_names']) && !empty($data['photo_urls'])) {
            $data['banner'] = [];
            foreach ($data['photo_urls'] as $key => $url) {
                $photoUrl = cmf_asset_relative_url($url);
                array_push($data['banner'], ["url" => $photoUrl, "name" => $data['photo_names'][$key]]);
            }
            $data['banner'] = json_encode($data['banner']);
        }  else {
            $this->error("封面图必须上传");
        }
        $data['custom_preset_time'] = implode(',', $data['custom_preset_time']);
        $result = $roomModel->validate(true)->allowField(true)->isUpdate(true)->save($data);
        if ($result === false) {
            $this->error($roomModel->getError());
        }

        $this->success("保存成功！");
    }

    /**
     * 删除友情链接
     * @adminMenu(
     *     'name'   => '删除友情链接',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '删除友情链接',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $id = $this->request->param('id', 0, 'intval');
        RoomModel::destroy($id);

        $this->success("删除成功！", url("Room/index"));
    }

    /**
     * 友情链接排序
     * @adminMenu(
     *     'name'   => '友情链接排序',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '友情链接排序',
     *     'param'  => ''
     * )
     */
    public function listOrder()
    {
        $roomModel = new  RoomModel();
        parent::listOrders($roomModel);
        $this->success("排序更新成功！");
    }

    /**
     * 友情链接显示隐藏
     * @adminMenu(
     *     'name'   => '友情链接显示隐藏',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '友情链接显示隐藏',
     *     'param'  => ''
     * )
     */
    public function toggle()
    {
        $data = $this->request->param();
        $roomModel = new RoomModel();

        if (isset($data['ids']) && !empty($data["display"])) {
            $ids = $this->request->param('ids/a');
            $roomModel->where(['id' => ['in', $ids]])->update(['status' => 1]);
            $this->success("更新成功！");
        }

        if (isset($data['ids']) && !empty($data["hide"])) {
            $ids = $this->request->param('ids/a');
            $roomModel->where(['id' => ['in', $ids]])->update(['status' => 0]);
            $this->success("更新成功！");
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