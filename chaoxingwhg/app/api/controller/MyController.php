<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/7
 * Time: 15:44
 */

namespace app\api\controller;


use app\admin\model\UserModel;
use code\Code;
use think\Config;
use think\Loader;
use token\Token;
use think\Db;

class MyController extends Base
{
    //获取账号信息
    public function index()
    {
        $this->check_sign();
        $token=input('param.token');

        $token_info = db('token')->where('token', $token)->field('user_id')->find();
        $user_id = $token_info['user_id'];

        $user_info = Db::name('user')->find($user_id);
        $user_info['format_birthday']=date('Y-m-d',$user_info['birthday']);
        if ($user_info) {
            $user_info['avatar_url'] = cmf_get_image_preview_url($user_info['avatar']);
            return $this->output_success(13101, $user_info, '信息获取成功');
        } else {
            return $this->output_error(13001, '信息不存在');
        }
    }
    //更新账号信息
    public function save()
    {
        //登录检查
        $this->check_sign();
        $token = input('post.token');

        $nickname = input('nickname', '');
        $realname = input('realname','');

        $sex = input('sex',0);
        $birthday = input('birthday','');
        $address = input('address', '');
        $avatar = input('avatar', '');
        $user_id = Token::get_user_id($token);

        $data=[];

        /*if(!empty($nickname)){
          $data['user_nickname']=$nickname;
        }

        if(!empty($realname)){
          $data['user_realname']=$realname;
        }
        if(!empty($birthday)){
          $data['birthday']=strtotime($birthday);
        }
        if(!empty($address)){
          $data['address']=$address;
        }

        if($sex==1||$sex==2||$sex==0){
          $data['sex']=$sex;
        }*/

        if(!empty($avatar)){
            $data['avatar']=$avatar;
        }

        if (!empty($nickname)) {
            if ($this->check_name($nickname)) {
                $data['user_nickname'] = $nickname;
            } else {
                return $this->output_error(13003, '昵称包含敏感词');
            }
        }
        if (!empty($realname)) {
            if ($this->check_name($realname)) {
                $data['user_realname'] = $realname;
            } else {
                return $this->output_error(13003, '真实名称包含敏感词');
            }
        }
        if (!empty($address)) {
            if ($this->check_name($address)) {
                $data['address'] = $address;
            } else {
                return $this->output_error(13003, '地址包含敏感词');
            }
        }

        if (!empty($sex)) {
            if (in_array($sex, [0, 1, 2])) {
                $data['sex'] = $sex;
            } else {
                return $this->output_error(13004, '性别设置错误');
            }
        }

        if (!empty($birthday)) {
            $start_time = strtotime('-100 year');
            $birthday = strtotime($birthday);
            if ($birthday <= mktime(0, 0, 0) && $birthday > $start_time) {
                $data['birthday'] = $birthday;
            } else {
                return $this->output_error(13005, '年龄设置错误');
            }
        }

        $data['id'] = $user_id;
        $res = UserModel::update($data,$data['id']);
        return $this->output_success(12101, '', '修改成功');
    }

    /**
     * 检查名称是否合法
     * @param $name
     * @return bool
     */
    private function check_name($name) {
        if (empty($name)) {
            return false;
        }
        //检查内容是否合法
        if (check_word($name)) {
            return $name;
        } else {
            return false;
        }

    }

}