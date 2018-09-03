<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/1
 * Time: 15:26
 */

namespace app\api\validate;


use think\Validate;

class FeedbackValidate extends Validate
{
    protected $rule = [
        'content' => 'require',
        'mobile' =>'require'
    ];

    protected $message = [
        'content.require' => '内容不能为空',
        'mobile.require' => '手机号不能为空',
    ];


}