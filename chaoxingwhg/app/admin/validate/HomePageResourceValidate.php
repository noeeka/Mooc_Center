<?php
/**
 * Created by PhpStorm.
 * User: jxbx
 * Date: 2018/5/22
 * Time: 17:09
 */

namespace app\admin\validate;

use think\Validate;

class HomePageResourceValidate extends Validate{


    protected $rule = [
        'name' => 'require',
        'url'  => 'require',
    ];
    protected $message = [
        'name.require'=>'资源名称不能为空',
        'url.require' =>'url不能为空',
    ];
}