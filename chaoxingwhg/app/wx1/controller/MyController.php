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
namespace app\wx1\controller;

use cmf\controller\HomeBaseController;

class MyController extends HomeBaseController
{
    public function index()
    {
        return $this->fetch();
    }

    public function collect()
    {
        return $this->fetch();
    }

    public function about()
    {
        return $this->fetch();
    }

    public function feedback()
    {
        return $this->fetch();
    }

    public function set()
    {
        return $this->fetch();
    }

    public function personalinfo()
    {
        return $this->fetch();
    }

    public function password()
    {
        return $this->fetch();
    }

    public function attestation()
    {
        return $this->fetch();
    }
    public function tidings()
    {
        return $this->fetch();
    }
    public function tidingsinfo()
    {
        return $this->fetch();
    }
    public function infoset()
    {
     return $this->fetch();
    }
    public function nameset()
    {
    return $this->fetch();
    }
    public function addressset()
    {
   return $this->fetch();
    }

    public function  myBaoming(){
        return $this->fetch('myBaoming');
    }
}