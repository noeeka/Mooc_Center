<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/7
 * Time: 15:44
 */

namespace app\api\controller;

use app\admin\model\ActivityBaomingModel;
use token\Token;
use think\db;

class EnrollController extends Base
{

    public function check()
    {
        $this->check_sign();
        $token = input('post.token');
        $id = input('id');
        $user_id = Token::get_user_id($token);

        if (empty($user_id)) {
            return $this->output_error(13011, '未登录，请先登录');
        } else {
            if (empty($id)) {
                return $this->output_error(13011, '未选择活动');
            } else {
                $user = db('user')->where(['id' => $user_id, 'user_role' => 1])->find();
                if (!empty($user)) {
                    $data['activity_id'] = $id;
                    $data['created_at'] = time();
                    $data['user_id'] = $user_id;
                    print_r($data);exit;
                    $activityBaomingModel = new ActivityBaomingModel();
                    $activityBaomingModel->data($data)->save();
                    return $this->output_success(13100, '', '报名成功！');
                } else {
                    return $this->output_error(13101, '未认证，请先认证');
                }
            }
        }
    }


}