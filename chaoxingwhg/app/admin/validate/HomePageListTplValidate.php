<?php
/**
 * Created by PhpStorm.
 * User: jxbx
 * Date: 2018/5/22
 * Time: 15:30
 */

namespace app\admin\validate;

use think\Validate;

class HomePageListTplValidate extends Validate{
    protected $rule = [
        'name' => 'require',
    ];

    protected $message = [
        'name.require' => '模板名称名称不能为空',
    ];
}