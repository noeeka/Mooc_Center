<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/12
 * Time: 14:12
 */

namespace app\admin\validate;


use think\Validate;

class PerformTypeValidate extends Validate
{
    protected $rule = [
        'name' => 'require',
    ];
    protected $message = [
        'name.require' => '请指定活动类型！',
    ];
}