<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/1
 * Time: 15:26
 */

namespace app\api\validate;


use think\Validate;

class OpinionValidate extends Validate
{
    protected $rule = [
        'page' => 'require',
        'size' => 'max:15',
    ];

    protected $message = [
        'page.require' => '页码必须传递',
        'size.max' => '最多一次可以获取15条数据',
    ];


}