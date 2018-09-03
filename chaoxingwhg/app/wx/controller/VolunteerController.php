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

class VolunteerController extends HomeBaseController
{
    public function index()
    {
        return $this->fetch('/volunteer/volunteer');
    }
    public function reports()
    {
        return $this->fetch(':volunteer/reports');
    }
    public function reportsinfo()
    {
        return $this->fetch(':volunteer/reportsinfo');
    }
    public function reportsphotos()
    {
        return $this->fetch(':volunteer/reportsphotos');
    }
    public function reportsAddphoto()
    {
       return $this->fetch(':volunteer/reportsAddphoto');
    }
    public function zhaomu()
    {
        return $this->fetch(':volunteer/zhaomu');
    }
    public function zhaomuinfo()
    {
        return $this->fetch(':volunteer/zhaomuinfo');
    }
     public function fengcai()
    {
       return $this->fetch(':volunteer/fengcai');
    }
    public function fengcaiinfo()
    {
        return $this->fetch(':volunteer/fengcaiinfo');
    }
    public function volRegister()
    {
        return $this->fetch(':volunteer/volRegister');
    }
}
