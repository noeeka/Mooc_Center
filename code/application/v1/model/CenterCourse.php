<?php

/**
 * 文化馆慕课课程
 */
namespace app\v1\model;
use think\Model;


class CenterCourse extends Model{
    protected $auto = [];
    protected $insert = ['status' => 1,'create_time'];
    //protected $update = ['login_ip'];

    protected function setCreateTimeAttr()
    {
        return time();
    }
}