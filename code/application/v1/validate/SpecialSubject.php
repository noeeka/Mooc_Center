<?php
/**
 * Created by PhpStorm.
 * User: zhangchun
 * Date: 2018/6/28
 * Time: 15:03
 */

namespace app\v1\validate;

use think\Validate;

class SpecialSubject extends Validate
{
    protected $rule=[
        'title'=>'require',
    ];

    protected $message = [
        'title.require'  =>  '标题不能为空',
    ];
}