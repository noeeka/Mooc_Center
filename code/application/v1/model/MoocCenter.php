<?php

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/23
 * Time: 15:17
 */
namespace app\v1\model;

use think\Model;


class MoocCenter extends Model{
    protected $auto = [];
    protected $insert = ['status' => 1,'create_time'];
    //protected $update = ['login_ip'];

    protected function setCreateTimeAttr()
    {
        return time();
    }
}