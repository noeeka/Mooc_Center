<?php
/**
 * Created by PhpStorm.
 * User: jxbx
 * Date: 2018/5/9
 * Time: 17:48
 */

namespace app\api\controller;


use app\admin\model\Sfzimg;
use app\portal\model\UserModel;

class VolunRegisterController extends Base{

    public function index(){
        $user_id = $this->getuid();
        $user_id = 72;
        if(!empty($user_id)){
            $userModel = new UserModel();

            $user_msg = Sfzimg::get(['user_id'=>$user_id]);
            var_dump($user_msg);
            if($user_msg){
                //已进行实名认证


            }else{
                //未进行实名认证

            }
        }else{
            return $this->output_error(12011,'请先登录');
        }
    }
}