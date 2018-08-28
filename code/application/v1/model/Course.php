<?php

/**
 * 慕课课程
 */
namespace app\v1\model;
use think\Model;
use think\Db;


class Course extends Model{
    protected $auto = [];
    protected $insert = ['status' => 1,'create_time','course_from'=>0];
    //protected $update = ['login_ip'];

    protected function setCreateTimeAttr()
    {
        return time();
    }
}