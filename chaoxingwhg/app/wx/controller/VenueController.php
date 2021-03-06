<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\wx\controller;

use cmf\controller\HomeBaseController;

class VenueController extends HomeBaseController
{
    public function index()
    {
        return $this->fetch('venue/venue');
    }

    public function read()
    {
        return $this->fetch('venue/info');
    }

    public function yuyue()
    {
        return $this->fetch('venue/yuyue');
    }

}
