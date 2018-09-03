<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\admin\model;

use think\Db;
use think\Model;

class UserModel extends Model
{

    static function getCurrentVenue(){
        $uid = cmf_get_current_admin_id();
        if($uid == 1){
            $venueModel = new VenueModel();
            return $venueModel->where('status', 1)->column('id');
        }else {
            $user = UserModel::get($uid);
            if ($user == null) {
                return [];
            } else {
                return array_filter(explode(',', $user->venue));
            }
        }
    }
    static function getCurrentVenue2(){
        $uid = cmf_get_current_admin_id();
        if($uid == 1){
            $venueModel = new VenueModel();
            return $venueModel->column('id');
        }else {
            $user = UserModel::get($uid);
            if ($user == null) {
                return [];
            } else {
                return array_filter(explode(',', $user->venue));
            }
        }
    }
    static function getCurrentArea(){
        $uid = cmf_get_current_admin_id();
        if($uid == 1){
            $areaModel = new AreaModel();
            return $areaModel->where('status', 1)->column('id');
        }else {
            $user = UserModel::get($uid);
            if ($user == null) {
                return [];
            } else {
                return array_filter(explode(',', $user->area));
            }
        }
    }
    /**
     * 获取所有顶级区域
     */
    static function get_parent_areas(){
        $areaModel=new AreaModel();
//        $parent_areas=$areaModel->where("path REGEXP '^0-[0-9]*$'")->where(['status'=>1])->select();
        $parent_areas=$areaModel->where('parent_id',0)->where(['status'=>1])->select();
        return $parent_areas;

    }

    /**
     * 获取子区域
     * $area_id
     */
    static function children_areas($area_id){
        $areaModel=new AreaModel();
        $parent_areas=$areaModel->where(['status'=>1,'parent_id'=>['in',$area_id]])->select();
        return $parent_areas;
    }

    /**
     * 获取区域下的场馆
     * $area_id
     */
    static function area_venues($area_id){
        $areaModel=new AreaModel();
        $paths=$areaModel->where(['id'=>['in',$area_id]])->column('path');
        $area_ids=[];
        foreach ($paths as $path){
            $res=$areaModel->where(['path'=>['like','%'.$path.'%'],'status'=>1])->column('id');
            $area_ids=array_merge($res,$area_ids);
        }

        if(!empty($area_ids)){
           $venues= Db::name('venue')->where(['address'=>['in',$area_ids]])->select()->toArray();
           return $venues;
        }
    }

}