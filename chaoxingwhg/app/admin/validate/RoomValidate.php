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

class RoomValidate extends Validate
{
    protected $rule = [
        'name' => 'require',
        'abstract' => 'require',

        'address' => 'checkEmpty:所在地区',
        'venue' => 'checkEmpty:场馆',
        'venue_type' => 'checkEmpty:场馆类型',
        'open_end_time_am' => 'checkEnd:am',
        'open_end_time_pm' => 'checkEnd:pm',
        'area|面积' => 'float',
        'seat|座位数量' => 'number',
        'audio|音响数量' => 'number',
        'microphone|话筒数量' => 'number',
        'projector|投影仪数量' => 'number',
        'tv|电视机数量' => 'number',
        'computer|电脑数量' => 'number',
        'fee|费用' => 'float',
    ];

    protected $message = [
        'name.require' => '名称不能为空',
        'abstract.require' => '简介不能为空',
        'thumb.require' => '封面图不能为空',
    ];

    function checkEmpty($value,$rule,$data){
        return $value <= 0 ? $rule.'不能为空': true;
    }
    function checkEnd($value,$rule,$data){
        if($rule == 'am'){
            return $data['open_end_time_am'] <= $data['open_start_time_am'] ? '上午关闭时间必须大于开始时间':true;
        }else{
            return $data['open_end_time_pm'] <= $data['open_start_time_pm'] ? '下午关闭时间必须大于开始时间':true;
        }
    }
}