<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 2018/7/2
 * Time: 10:26
 */

namespace app\v1\model;

use think\Model;

class CourseType extends Model
{

    public function existType($course_type_id, $center_id)
    {
        $type = CourseType::where(['center_id' => $center_id, 'id' => $course_type_id])->find();
        if ($type) {
            if ($type['status'] == 0) {
                return '课程类型被禁用';
            }
            return true;
        } else {
            return '课程类型不存在';
        }
    }
}