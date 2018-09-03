<?php

namespace app\admin\model;

use think\Model;
use tree\Tree;

class HomePageGlobalModel extends Model
{
    protected $insert = ['create_time', 'update_time'];
    protected $update = ['update_time'];

    protected function setCreateTimeAttr()
    {
        return time();
    }

    protected function setUpdateTimeAttr()
    {
        return time();
    }
}