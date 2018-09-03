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
namespace app\admin\validate;

use think\Validate;

class PerformValidate extends Validate
{
    protected $rule = [
        'title|标题' => 'require',
        'typeid' => 'checkNotSelect:节目类型',
        'org|演出单位' => 'require',
        'start_time|开始时间' => 'require|checkStart',
        'end_time|结束时间' => 'require|checkEnd',
        'venue' => 'checkNotSelect:所属场馆',
        'areas' => 'checkNotSelect:演出区域',
        'content|内容' => 'require',
        'thumb|缩略图' =>'require',
    ];

    function checkNotSelect($value,$rule,$data){
        return $value == 0 ? $rule.'不能为空' : true;
    }
    function checkStart($value,$rule,$data){
//        if(strtotime($value) <= time()){
//            return '开始时间必须大于当前时间';
//        }
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
//    protected $message = [
//        'title.require' => '请指定标题！',
//        'venue.require' => '请指定场馆！',
//        'typeid.require' => '请指定类型！',
//        'thumb.require' => '请指定缩略图！',
//    ];

}