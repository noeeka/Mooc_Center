<?php

namespace app\v1\validate;

use app\v1\model\CourseType;
use think\Validate;


class Course extends Validate
{
    protected $rule = [
        'id' => 'requireEdit|number',
        'mooc_id' => 'checkMoocId',
        'course_title' => 'requireAdd|max:40',
        'course_type_id' => 'requireAdd|number|existType',
        'cover_img' => 'requireAdd|max:255',
        'cover_video' => 'max:255',
        'course_from' => 'requireAdd|number|between:0,1',
        'content' => 'requireAdd',
        'open_status|开放状态' => 'number|between:0,2',
        'start_time|开始时间' => 'requireif:open_status,1|number',
        'end_time|结束时间' => 'requireif:open_status,1|number',
        'status' => 'number|between:0,1',
        'create_time' => 'requireAdd',


    ];

    protected $message = [
        'id.requireEdit' => '课程ID必须',
        'id.number' => '课程类型必须是数字',

        'course_title.requireAdd' => '课程名必须',
        'course_title.max' => '课程名最多20个汉字',

//        'course_type_id.requireAdd' => '课程类型必须填写',
//        'course_type_id.number' => '课程类型必须是数字',
//        'course_type_id.between' => '课程类型在1-10之间',

        'cover_img.requireAdd' => '课程封面图片必须上传',
        'cover_img.max' => '封面图地址最多255个字符',
        'cover_video.max' => '封面视频地址最多255个字符',

//        'course_from.requireAdd'  =>  '课程来源必须填写',
//        'course_from.number'  =>  '课程来源错误',
//        'course_from.between'  =>  '课程来源错误',

        'content.requireAdd' => '课程内容必须填写',

        'status.number' => '状态必须是数字',
        'status.between' => 'status必须是0或1',

        'create_time.requireAdd' => '创建时间必须填写',
        'open_status.requireAdd' => '开放状态必须'

    ];

    protected $scene = [
        'add' => ['mooc_id', 'course_title', 'cover_img', 'cover_video', 'open_status', 'start_time', 'end_time', 'content', 'status', 'create_time'],
        'edit' => ['id', 'mooc_id', 'course_title', 'cover_img', 'cover_video', 'open_status', 'start_time', 'end_time', 'content', 'status', 'create_time'],
    ];

    // 自定义验证规则
    protected function checkMoocId($value, $rule, $data)
    {
        if (!is_numeric($value)) {
            return '慕课ID必须是数字';
        } elseif ($value > 1000000000 || $value < 100000000) {
            return '慕课ID必须是9位';
        } else {
            return true;
        }

    }

    //场景值是add时验证必须
    protected function requireAdd($value, $rule, $data)
    {
        if ($this->currentScene == 'add') {
            if (empty($value)) {
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

    //场景值是edit时验证必须
    protected function requireEdit($value, $rule, $data)
    {
        if ($this->currentScene == 'edit') {
            if (empty($value)) {
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }
}