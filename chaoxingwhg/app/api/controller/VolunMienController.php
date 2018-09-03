<?php
/**
 * Created by PhpStorm.
 * User: jxbx
 * Date: 2018/5/7
 * Time: 15:00
 * Remark:志愿者风采展示
 */

namespace app\api\controller;


use app\user\model\UserModel;

class VolunMienController extends Base
{
    //风采展示列表
    public function index(){
        $where = ['user_role' => 2,'volun_skill_imgs'=>['neq',''],'is_show'=>1];//志愿者且已展示且风采图片不为空

        $userModel = new UserModel();
        $user_list = $userModel->where($where)->select();

        foreach ($user_list as $key => $value) {
            //获取真实姓名
            $user_realname = SfzimgModel::where('user_id', $value['id'])->value('realname');
            $value['user_realname'] = $user_realname;

            //获取风采图片
            $user_list[$key]['imgs'] = json_decode($value['volun_skill_imgs'], true);
            if (is_array($user_list[$key]['imgs'])) {
                $imgs = $user_list[$key]['imgs'];
                $imgs1 = [];
                foreach ($imgs as $k => $v) {
                    $imgs1[$k] = cmf_get_image_preview_url($v);
                }
                $user_list[$key]['imgs'] = $imgs1;
            }
        }

        if ($user_list) {
            return $this->output_success(11101, $user_list, '获取风采展示成功');
        } else {
            return $this->output_error(11102, '获取风采展示失败');
        }

    }
    //风采展示详情
    public function read(){
        $id = input('id', 0, 'intval');
        $where = ['user_role' => 2, 'volun_skill_imgs'=>['neq',''],'id' => $id];//单个志愿者且已审核

        $userModel = new UserModel();
        $user_list = $userModel->where($where)->find();
        if(empty($user_list)){ //用户信息未找到
            return $this->output_error(11201,'获取风采失败');
        }

        //获取真实姓名
        unset($user_list['user_realname']);
        $user_realname = SfzimgModel::where('user_id', $id)->value('realname');
        $user_list['user_realname'] = $user_realname;

        if($user_list['volun_skill_imgs'] != ''){
            $imgs = json_decode($user_list['volun_skill_imgs']);
        }else{
            $imgs = "";
        }

        //user图片处理
        $user = [];
        if (is_array($imgs)) {
            foreach ($imgs as $k => $v) {
                $user[] = cmf_get_image_preview_url($v);
            }
            $user_list['img'] = $user;
        }

        if ($user_list) {
            return $this->output_success(11101, $user_list, '获取风采展示详情成功');
        } else {
            return $this->output_error(11102, '获取风采展示失败');
        }

    }
}