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
namespace app\portal\controller;

use cmf\controller\HomeBaseController;
use think\Cookie;
use token\Token;
class MyController extends HomeBaseController
{
    public function index()
    {
        $token = Cookie::get('token');
        $user_id = Token::get_user_id($token);
        if ($user_id > 0) {
            return $this->fetch('my');
        } else {
            header('Location:' . config('server_address') . 'portal/login/login');
        }
    }
     public function indexnew()
    {
        $token = Cookie::get('token');
        $user_id = Token::get_user_id($token);
        if ($user_id > 0) {
            return $this->fetch('mynew');
        } else {
            header('Location:' . config('server_address') . 'portal/login/login');
        }
    }


}
