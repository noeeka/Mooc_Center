<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/21
 * Time: 14:44
 */

namespace app\admin\validate;


use think\Validate;

class VersionValidate extends Validate
{
    protected $rule = [
        'key' => 'require',
        'value'  => 'require',
    ];

    protected $message = [
        'key.require' => '文件名不能为空',
        'value.require'  => '版本号不能为空',
    ];
}