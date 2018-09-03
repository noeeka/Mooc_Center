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

use app\admin\model\ActivityBaoming;
use app\admin\model\ActivityBaomingModel;
use app\admin\model\ActivitySignInModel;
use cmf\controller\AdminBaseController;
use app\admin\model\ActivityModel;
use app\admin\model\ActivityTypeModel;
use app\admin\model\VenueModel;
use app\admin\model\AreaModel;
use think\Db;
use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use app\admin\model\UserModel;
use think\Exception;


class ActivityController extends AdminBaseController
{

    /**
     * 活动列表
     */
    public function index()
    {
        $where=[];
        $param = $this->request->param();
        $activity_type = $this->request->param('activity_type', 0, 'intval');
        if(!empty($activity_type)){
            $where['a.type']=$activity_type;
        }
        $venue = $this->request->param('venue', 0, 'intval');
        if(!empty($venue)){
            $where['a.venue']=$venue;
        }else{
            $where['a.venue'] = ['in', UserModel::getCurrentVenue()];
        }
        $keyword = $this->request->param('keyword');
        if(!empty($keyword)){
            $where['a.title']= ['like', "%$keyword%"];
        }
        $where['a.volun_type']=0;
        $where['a.delete_time']=0;

        if(isset($param['activity_status']) &&  $param['activity_status']){
            if($param['activity_status'] == 1) { //即将征集
                $where['a.start_time'] = [ '>', time() ];
                $where['a.end_time'] = ['>' , time()];
            } else if($param['activity_status'] == 2) { //往期征集
                $where['a.start_time'] = ['<' , time()];
                $where['a.end_time'] =  ['<' , time()];
            } else { //征集中
                $where['a.start_time'] = [ '<=' , time() ];
                $where['a.end_time'] = [ '>' , time() ];
            }
        }
        
        if(isset($param['activity_enable']) &&  $param['activity_enable'] != 999){
            $where['a.status'] = $param['activity_enable'];
        }

        $activityModel = new ActivityModel();

        $activity_list    = $activityModel
            ->alias('a')
            ->where($where)
            ->join('venue v', 'a.venue = v.id', 'left')
            ->order('a.published_time DESC')
            ->field("a.*,v.name")
            ->paginate(15);

       // halt($activityModel->getLastSql());
        // 获取分页显示
        $activity_list->appends($param);
        $page = $activity_list->render();

        //活动类型列表
        $activityTypeModel = new ActivityTypeModel();
        $activity_type_list    = $activityTypeModel->order('id DESC')->select();

        //区域列表
        $areaModel = new AreaModel();
        $area_id =!empty($param['area_id']) ? $param['area_id'] : 0;
        $areas = $areaModel->adminAreaTree($area_id);

        //场馆列表
        $venueModel = new VenueModel();
        $venue_list    = $venueModel->where(['id'=>['in', UserModel::getCurrentVenue()]])->order('id DESC')->select();

        $this->assign('activity_type', $activity_type);
        $this->assign('area_id', isset($param['area_id']) ? $param['area_id'] : 0);
        $this->assign('venue', isset($param['venue']) ? $param['venue'] : 0);
        $this->assign('activity_status', isset($param['activity_status']) ? $param['activity_status'] : 0);
        $this->assign('activity_enable', isset($param['activity_enable']) ? $param['activity_enable'] : 999);


        $this->assign('keyword', isset($keyword) ?$keyword : '');
        $this->assign('list', $activity_list);

        $this->assign('activity_type_list', $activity_type_list);
        $this->assign('areas', $areas);
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

        $activityModel = new ActivityModel();
        $activity=$activityModel->alias('a')->where('a.id',$id)->join('area ar','a.area =ar.id','left')->join('venue v','a.venue=v.id','left')->field('a.*,ar.name as aname,v.name as vname')->find();

        //已报名人数
        $yibaoming=Db::name('activity_baoming')->where('activity_id',$id)->count();
        $where['sign.activity_id'] = $id;
        $signCount = $activity_list = DB::table('cxtj_activity_signIn')
            ->alias('as `sign` ')
            ->where($where)
            ->count();
        //已报名人信息
        $user_list=Db::name('activity_baoming')->alias('a')->where('a.activity_id',$id)->join('user u','a.user_id=u.id','left')->field('u.*')->select();
        $this->assign('activity', $activity);
        $this->assign('yibaoming', $yibaoming);
        $this->assign('signCount' , $signCount );
        $this->assign('user_list', $user_list);


        return $this->fetch();
    }
    /**
     * 活动用户导出查看
     */
    public function export()
    {
        $user_list=Db::name('activity_baoming')->alias('a')->join('user u','a.user_id=u.id','left')->field('u.*')->select();

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
            ->setCellValue('A1','昵称' )
            ->setCellValue('B1','手机号' )
            ->setCellValue('C1','邮箱')
            ->setCellValue('D1','真实姓名')
            ->setCellValue('E1','性别')
            ->setCellValue('F1','生日')
            ->setCellValue('G1','地址');

          for($i=0;$i<count($user_list);$i++){
             if($user_list[$i]['sex']==0){
                 $sex='保密';
             }elseif($user_list[$i]['sex']==1){
                 $sex='男';
             }else{
                 $sex='女';
             }
             $spreadsheet->setActiveSheetIndex(0)
                 ->setCellValue('A'.($i+2),$user_list[$i]['user_nickname'] )
                 ->setCellValue('B'.($i+2),$user_list[$i]['mobile'] )
                 ->setCellValue('C'.($i+2),$user_list[$i]['user_email'] )
                 ->setCellValue('D'.($i+2),$user_list[$i]['user_realname'] )
                 ->setCellValue('E'.($i+2),$sex)
                 ->setCellValue('F'.($i+2),date('Y-m-d',$user_list[$i]['birthday']))
                 ->setCellValue('G'.($i+2),$user_list[$i]['address'] );
         }

        // Redirect output to a client’s web browser (Xls)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="活动用户.xls"');
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
     * 添加活动
     */
    public function add()
    {
        //区域列表
        $areaModel=new AreaModel();
        $areas = $areaModel->adminAreaTree();

        //当前用户下文化馆列表
        $venueModel=new VenueModel();
        $where['id']= ['in', UserModel::getCurrentVenue()];
        $venues=$venueModel->where($where)->select();

        //活动类型
        $activityTypeModel=new ActivityTypeModel();
        $type_list=$activityTypeModel->select();

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
            $result = $this->validate($post, 'Activity');

            if ($result !== true) {
                $this->error($result);
            }

            $need_baoming = intval($post['need_baoming']);
            $need_sign = intval($post['need_sign']);

            if($need_baoming==1){
                if(empty($post['baoming_start_time'])){
                    $this->error('请填写报名开始时间');
                }
                if(empty($post['baoming_end_time'])){
                    $this->error('请填写报名结束时间');
                }
                if(empty($post['max_num'])){
                    $this->error('请填写最大人数');
                }
            }

            if($need_sign == 1) {
                empty($post['sign_start_time']) || empty($post['sign_end_time']) && $this->error('请正确添加签到时间');
                empty($post['sign_type']) && $this->error('签到类型必须选择');
                $post['sign_type'] == 1 && $post['need_baoming'] != 1 && $this->error('报名类型签到必须开启报名');

                strtotime($post['sign_start_time']) >= strtotime($post['sign_end_time']) &&  $this->error('签到结束时间不能早于开始时间');
                strtotime($post['sign_end_time']) >= strtotime($post['end_time']) &&  $this->error('签到结束时间大于活动结束时间');
//                strtotime($post['sign_start_time']) >= strtotime($post['start_time']) &&  $this->error('签到开始时间不能早于活动开始时间');
            }

            if (empty($post['thumb'])) {
                $this->error('请上传缩略图');
            }

            $activityModel = new ActivityModel();
            $result = $activityModel->adminAddActivity($data['post']);

            if($result && $need_sign) {
                $url = config('server_address'). 'wx/focus/index?id='.$result->id;
                $updateQrcode = $activityModel->where('id' , $result->id)
                    ->setField('sign_qrcode' , simpleQrcode($url));
            }

            $this->success('添加成功!', url('Activity/index'));
        }
    }

    /**
     * 编辑活动
     */
    public function edit()
    {
        $areaModel = new AreaModel();
        $venueModel = new VenueModel();
        $activity_id        = $this->request->param('id', 0, 'intval');
        $activity = ActivityModel::get($activity_id);


        //当前用户下文化馆列表


        $address = $venueModel->where('id', $activity->venue)->value('address');

        $where['id']= ['in', UserModel::getCurrentVenue()];
        $where['address'] = $address;
        $where['status'] = 1;
        $venues = $venueModel->where($where)->select();

        //区域列表
        $areaModel=new AreaModel();
        $area_id = VenueModel::where(['id' =>  $activity->venue ])->value('address',0);
        $areas = $areaModel->adminAreaTree($area_id);

        //活动类型
        $activityTypeModel=new ActivityTypeModel();
        $type_list=$activityTypeModel->select();
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
        $result = $this->validate($post, 'Activity');
        if ($result !== true) {
            $this->error($result);
        }

        $need_baoming = $post['need_baoming'];
        $need_sign = intval($post['need_sign']);
        if($need_baoming==1){
            if(empty($post['baoming_start_time'])){
                $this->error('请填写报名开始时间');
            }
            if(empty($post['baoming_end_time'])){
                $this->error('请填写报名结束时间');
            }
            if(empty($post['max_num'])){
                $this->error('请填写最大人数');
            }
        }

        if($need_sign == 1) {
            empty($post['sign_start_time']) || empty($post['sign_end_time']) && $this->error('请正确添加签到时间');
            empty($post['sign_type']) && $this->error('签到类型必须选择');

            $post['sign_type'] == 1 && $post['need_baoming'] != 1 && $this->error('报名类型签到必须开启报名');

            strtotime($post['sign_start_time']) >= strtotime($post['sign_end_time']) &&  $this->error('签到结束时间不能早于开始时间');
            strtotime($post['sign_end_time']) >= strtotime($post['end_time']) &&  $this->error('签到结束时间大于活动结束时间');
//                strtotime($post['sign_start_time']) >= strtotime($post['start_time']) &&  $this->error('签到开始时间不能早于活动开始时间');

            if($post['originSign'] == 0) {
                $url =  config('server_address'). 'wx/focus/index?id='.$post['id'];
                $data['post']['sign_qrcode'] =  simpleQrcode($url);
            }


        }

        if (empty($post['thumb'])) {
            $this->error('请上传缩略图');
        }

        $activityModel = new ActivityModel();

        $activityModel->adminEditActivity($data['post']);
        $this->success("保存成功！", url("activity/index"));
    }

    /**
     * 活动删除
     */
    public function delete()
    {
        $param           = $this->request->param();
        $activityModel = new ActivityModel();
        if(isset($param['id'])){
            $id=$param['id'];
            $activityModel->where(['id' => $id])->update(['delete_time' => time()]);
            //删除活动报名信息

            Db::name('activity_baoming')->where(['activity_id'=>$id])->delete();

            $this->success("删除成功！", '');
        }
        if (isset($param['ids'])) {
            $ids = $this->request->param('ids/a');
            $activityModel->where(['id' => ['in', $ids]])->update(['delete_time' => time()]);
            //删除活动报名信息
            Db::name('activity_baoming')->where(['activity_id'=>['in',$id]])->delete();
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
     * 活动查看
     */
    public function statistics()
    {

        $id = $this->request->param('id',0,'intval');
        $param = $this->request->param();
        $where = [];
        $keyword = $this->request->param('keyword',"",'trim');

        $activityInfo = ActivityModel::where('id' , $id)->find();
        if(empty($activityInfo))
            $this->error('活动不存在');

        $actCount = 0;
        $signCount = 0;


            //报名用户
        $where['baoming.activity_id'] = $id;
        $where['baoming.delete_time'] = 0;
        if(!empty($keyword)) {
                $where['sign.sign_mobile'] =  ['like' , "%".$keyword."%"];
            } //如果选择了关键字添加上

        $activity_list = DB::table('cxtj_activity_baoming')
                ->alias('baoming')
                ->join([
                    ["cxtj_activity_signIn sign", "baoming.id_card = sign.id_card and baoming.activity_id = sign.activity_id", "left"],
                    ['cxtj_contacts contacts' , "contacts.id = baoming.contacts_id"]
                ])
                ->where($where)
                ->order('baoming.id DESC')
                ->field(
                    'baoming.*,sign.create_time,
                            contacts.name as contacts_name,
                            contacts.id_card ,
                            contacts.mobile as contacts_mobile,
                            contacts.guardian as contacts_guardian,
                            contacts.type as contacts_type,
                            contacts.id_card '
                )->paginate(15);

        $actCount =  DB::table('cxtj_activity_baoming')->where('activity_id' , $id)->count();
        $signCount = DB::table('cxtj_activity_signIn')->where('activity_id' , $id)->count();
        // 获取分页显示
        $activity_list->appends($param);
        $page = $activity_list->render();

        $this->assign('keyword', isset($keyword) ?$keyword : '');
        $this->assign('list', $activity_list);
        $this->assign('page', $page);
        $this->assign('id', $id);
        $this->assign('actCount' , $actCount);
        $this->assign('signCount' , $signCount);
        $this->assign('act' , $activityInfo);

        return $this->fetch();
    }

    public function exportStatistics()
    {
        $id   = $this->request->param('id',0,'intval');
        $type = $this->request->param('type',1,'intval');

        $activity_list = [];
        if($type == 1) {
            $ActivityBaomingModel = new ActivityBaomingModel();
            $activity_list = DB::table('cxtj_activity_baoming')
                ->alias('as `baoming` ')
                ->join( [
                    ["user u", "baoming.user_id = u.id", "left"],
                    ["cxtj_activity_signIn sign", "baoming.user_id = sign.user_id and baoming.activity_id = sign.activity_id", "left"],
                    ["cxtj_sfzimg sfz", "baoming.user_id = sfz.user_id", "left"]
                ])
                ->where("baoming.activity_id", $id)
                ->field('sfz.realname,sign.sign_mobile,sign.id,u.user_realname,u.user_login,u.sex,u.birthday,u.address,sign.create_time')
                ->order('baoming.created_at DESC')->select();
        }
        else {
            $ActivitySignInModel = new ActivitySignInModel();
            $activity_list = DB::table('cxtj_activity_signIn')
                ->alias('as `sign` ')
                ->join([
                    ["user u", "sign.user_id = u.id", "left"],
                    ["cxtj_sfzimg sfz", "sign.user_id = sfz.user_id", "left"]
                ])
                ->where("sign.activity_id", $id)
                ->order('sign.create_time DESC')
                ->field('sfz.realname,sign.sign_mobile,sign.id,u.user_realname,u.user_login,u.sex,u.birthday,u.address,sign.create_time')
                ->select();
        }

        if($activity_list->isEmpty())
            $this->error('数据不存在');

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
            ->setCellValue('A1','用户名' )
            ->setCellValue('B1','姓名' )
            ->setCellValue('C1','性别')
            ->setCellValue('D1','出生日期')
            ->setCellValue('E1','家庭住址')
            ->setCellValue('F1','是否签到')
            ->setCellValue('G1','签到时间');

        for($i=0;$i<count($activity_list);$i++){

            if(!empty($activity_list[$i]['sign_mobile']))
                $username = $activity_list[$i]['sign_mobile'];
            else if(!empty($activity_list[$i]['user_login']))
                $username =  $activity_list[$i]['user_login'];
            else
                $username = '';

            $realname = "";
            if(!empty($activity_list[$i]['realname']))
                $realname =  $activity_list[$i]['realname'];
            else if(!empty($activity_list[$i]['user_realname']))
                $realname =  $activity_list[$i]['user_realname'];
            else
                $realname = "";

            switch ($activity_list[$i]['sex'] ) {
                case 0:
                    $sex = "保密";
                    break;
                case 1:
                    $sex = "男";
                    break;
                case 2:
                    $sex = "女";
                    break;
                default:
                    $sex = "未知";
                    break;
            }
            $birthday = "";
            if(!empty( $activity_list[$i]['birthday']))
                $birthday = date('Y-m-d H:i' , $activity_list[$i]['birthday']);

            $create_time = "";
            if(!empty($activity_list[$i]['create_time']))
                $create_time = date('Y-m-d H:i' , $activity_list[$i]['create_time']);

            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A'.($i+2), $username )
                ->setCellValue('B'.($i+2), $realname)
                ->setCellValue('C'.($i+2), $sex )
                ->setCellValue('D'.($i+2), $birthday )
                ->setCellValue('E'.($i+2), $activity_list[$i]['address'])
                ->setCellValue('F'.($i+2),   empty($vo['create_time']) ? "否": "是")
                ->setCellValue('G'.($i+2), $create_time );
        }

        // Redirect output to a client’s web browser (Xls)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="活动用户.xls"');
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


}