<?php

namespace app\v1\validate;
use think\Validate;


class Section extends Validate
{
    protected $rule = [
        'id'  =>  'require|number',
        'chapter_id'  =>  'requireAdd|number',
        'section_title'  =>  'requireAdd|max:40',
        'list_order'  =>  'number|max:10',
        'video_main'  =>  'max:70',
        'video_backup'  =>  'max:70',
        'video_time'  =>  'number',
        'status'  =>  'number|between:0,1',

        //'course_id'  =>  'require|number',

    ];

    protected $message = [
        'id.require'  =>  '节ID必须',
        'id.number'  =>  '节ID必须是数字',
        'chapter_id.require'  =>  '章ID必须填写',
        'chapter_id.number'  =>  '章ID必须是数字',
        'section_title.requireAdd'  =>  '节名称必须填写',
        'section_title.max'  =>  '节名称最多20个汉字',

        'list_order.number'  =>  '章排序号必须是数字',
        'list_order.between'  =>  '章排序号最长10位',

//        'video_main.requireAdd'  =>  '主视频链接必须',
        'video_main.max'  =>  '主视频链接最多70个字符',
        'video_backup.max'  =>  '备份视频链接最多70个字符',

        'video_time.requireAdd'  =>  '视频时长必须',
        'video_time.number'  =>  '视频时长必须是数字',

        'status.number'  =>  '状态必须是数字',
        'status.between'  =>  'status必须是0或1',


//        'course_id.require'  =>  '课程ID必须',
//        'course_id.number'  =>  '课程ID必须是数字',



    ];

    protected $scene = [
        'add'   =>  ['chapter_id','section_title','list_order','video_main','video_backup','video_time','status'],
        'edit'  =>  ['id','chapter_id','section_title','list_order','video_main','video_backup','video_time','status'],
    ];

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

    //场景值是edit时验证必须
    protected function requireEdit($value,$rule,$data)
    {
        if($this->currentScene=='edit'){
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