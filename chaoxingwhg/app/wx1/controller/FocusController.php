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

class FocusController extends HomeBaseController
{
    public function index()
    {
        return $this->fetch('index');
    }

    public function signover()
    {
        return $this->fetch('signover');
    }
    public function signsuccess(){
        return $this->fetch('signsuccess');
    }
     public function signerror(){
        return $this->fetch('signerror');
    }
}
