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

use app\admin\model\SysMessageModel;
use cmf\controller\HomeBaseController;
use token\Token;

class SysmessageController extends HomeBaseController
{
    public function comment(){
        $token = input('param.token');
        $uid = Token::get_user_id($token);
        $id = input('param.id', 0,'intval');
        $sys = SysMessageModel::where(['id'=>$id,'type'=>1,'to_id'=>$uid])->find();
        if($sys == null){
            return '内容不存在';
        }else{
            $sys->content = json_decode($sys->content, true);
            $this->assign('sys', $sys);
            return $this->fetch();
        }
    }

}
