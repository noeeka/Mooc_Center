<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/1
 * Time: 15:26
 */

namespace app\admin\validate;


use think\Validate;

class FeedbackValidate extends Validate
{
    protected $rule = [
        'reply_content' => 'require',
    ];

    protected $message = [
        'reply_content.require' => '回复不能为空',
    ];


}