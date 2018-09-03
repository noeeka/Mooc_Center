<?php

namespace app\admin\validate;

use think\Validate;

class HomePageLinkValidate extends Validate
{
    protected $rule = [
        'type|类型' => 'require|gt:0',
        'url|链接' => 'require',
        'thumb|图片' => 'require',
    ];

    protected $message = [
        'type.gt' => '类型必须'
    ];
}