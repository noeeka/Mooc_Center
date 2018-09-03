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
namespace app\portal2\controller;

use cmf\controller\HomeBaseController;

class LoginController extends HomeBaseController
{
    public function login()
    {
        return $this->fetch('login');
    }

    public function protocol()
    {
        return $this->fetch('protocol');
    }

    public function password()
    {
        return $this->fetch('password');
    }

}
