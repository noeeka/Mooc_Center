<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/16
 * Time: 11:29
 */

namespace app\portal\controller;


use app\admin\model\PerformTypeModel;
use app\api\controller\Base;

use app\admin\model\VenueModel;
use app\admin\model\AreaModel;

class ResourcelibController extends Base
{
    //获取所有活动
    public function index(){
        //场馆id
        $venue=input('venue',0,'intval');
        //区域id
        $area=input('area',0,'intval');

        //获取场馆
        $venueModel = new VenueModel();
        $v_where =[];
        if(!empty($venue)){
            $v_where['id']=$venue;
        }
        $v_where['status']=1;
        $venues=$venueModel->where($v_where)->select();

        //获取区域
        $areaModel = new AreaModel();
        $a_where =[];
        if(!empty($area)){
            $a_where['id']=$area;
        }
        $a_where['status']=1;
        $a_where['parent_id']=0;
        //获取顶级区域
        $area1=$areaModel->where($a_where)->select();
        $area_arr=[];
        //获取二级区域
        foreach($area1 as $v){
            $son_area=$areaModel->where(['status'=>1,'parent_id'=>$v['id']])->select();
            $v['son']=$son_area;
            $area_arr[]=$v;
        }

        //获取活动类型
        $perform=new PerformTypeModel();
        $performType=$perform->where(['status'=>1,'type'=>2])->select();

        $result['venue']=$venues;
        $result['area']=$area_arr;
        $result['perform_type']=$performType;

        if($result){
            return $this->output_success('13101',$result,'资源库获取成功');
        }else{
            return $this->output_error('13100','','资源库获取失败');
        }
    }

}