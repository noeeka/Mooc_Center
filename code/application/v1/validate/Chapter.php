<?php

namespace app\v1\validate;
use think\Validate;


class Chapter extends Validate
{
    protected $rule = [
        'course_id'  =>  'requireAdd|number',
        'chapter_title'  =>  'requireAdd|max:40',
        'list_order'  =>  'number|max:10',
        'status'  =>  'number|between:0,1',

    ];

    protected $message = [
        'id.require'  =>  '章ID必须',
        'id.number'  =>  '章ID必须是数字',
        'course_id.require'  =>  '课程ID必须填写',
        'course_id.number'  =>  '课程ID必须是数字',
        'chapter_title.requireAdd'  =>  '章名称必须填写',
        'chapter_title.max'  =>  '章名称最多20个汉字',

        'list_order.number'  =>  '章排序号必须是数字',
        'list_order.max'  =>  '章排序号最长10位',

        'status.number'  =>  '状态必须是数字',
        'status.between'  =>  'status必须是0或1',



    ];

    protected $scene = [
        'add'   =>  ['course_id','chapter_title','list_order','status'],
        'edit'   =>  ['course_id','chapter_title','list_order','status'],
    ];

    // 自定义验证规则

    //场景值是add时验证必须
    protected function requireAdd($value,$rule,$data)
    {
        if($this->currentScene=='add'){
            if(empty($value)){
                return false;
            }else{
                return true;
            }
        }else{
            return true;
        }
    }

}