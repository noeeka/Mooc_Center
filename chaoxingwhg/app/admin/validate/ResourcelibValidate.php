<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/15
 * Time: 16:12
 */

namespace app\admin\validate;


use think\Validate;

class ResourcelibValidate extends Validate
{
     protected $rule = [
        'title|标题' => 'require',
        'typeid' => 'checkNotSelect:活动类型',
         'start_time|开始时间' => 'require|checkStart',
         'end_time|结束时间' => 'require|checkEnd',
         'venue' => 'checkNotSelect:所属场馆',
         'areas' => 'checkNotSelect:演出区域',
         'thumb|缩略图' =>'require',
         'url|专题链接' =>'require',
     ];
    function checkNotSelect($value,$rule,$data){
        return $value == 0 ? $rule.'不能为空' : true;
    }
    function checkStart($value,$rule,$data){
        if(strtotime($value) <= time()){
            return '开始时间必须大于当前时间';
        }
        return true;
    }
    function checkEnd($value,$rule,$data){
        $start_time = strtotime($data['start_time']);
        $end_time = strtotime($data['end_time']);
        if($start_time > $end_time){
            return '结束时间必须大于开始时间';
        }
        return true;
    }
}