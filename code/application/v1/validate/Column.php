<?php
/**
 * Created by PhpStorm.
 * User: zhangchun
 * Date: 2018/7/2
 * Time: 17:48
 */

namespace app\v1\validate;

use think\Validate;

class Column extends Validate
{
    protected $rule=[
        'title'=>'require',
    ];

    protected $message = [
        'title.require'  =>  '标题不能为空',
    ];
}