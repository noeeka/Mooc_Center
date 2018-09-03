<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/14
 * Time: 10:19
 */

namespace app\admin\validate;


use think\Validate;

class MienValidate extends Validate
{
    protected $rule = [
        'title' => 'require',
        'abstract' => 'require',
        'thumb|缩略图' => 'require',
    ];
    protected $message = [
        'title.require' => '标题不能为空！',
        'abstract.require' => '简介不能为空！',
    ];

}