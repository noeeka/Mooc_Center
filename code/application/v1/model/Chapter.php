<?php

/**
 * 慕课课程
 */
namespace app\v1\model;
use think\Model;
use think\Db;


class Chapter extends Model{
    protected $auto = [];
    protected $insert = ['status' => 1];
    //protected $update = ['login_ip'];

    protected function setCreateTimeAttr()
    {
        return time();
    }
}