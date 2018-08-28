<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 2018/6/28
 * Time: 10:05
 */

namespace app\v1\validate;

use think\Validate;

class Account extends Validate
{

    protected $rule = [
        'user_login' => 'require|min:5|max:20|alphaNum',
    ];

    protected $message = [
        'user_login.require' => ['status' => 0, 'code' => 25012, 'msg' => '用户名不能为空'],
        'user_login.min' => ['status' => 0, 'code' => 25013, 'msg' => '用户名长度在5到20位'],
        'user_login.max' => ['status' => 0, 'code' => 25013, 'msg' => '用户名长度在5到20位'],
        'user_login.alphaNum' => ['status' => 0, 'code' => 25014, 'msg' => '用户名必须是字母数字'],
    ];
}