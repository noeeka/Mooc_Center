<?php

namespace app\v1\validate;

use think\Validate;

class MoocUser extends Validate
{
    protected $rule = [
        'center_user_id|文化馆用户ID' => 'require|number|max:10',
        'center_id|文化馆ID' => 'require|number|max:7',
        'nick_name|昵称' => 'chsDash|max:40',
        'avatar|头像' => 'max:255',
        'teacher_title|职称' => 'chsDash|max:50',
        'department|单位' => 'chsDash|max:100'
    ];

    protected $scene = [
        'edit_info' => ['nick_name', 'avatar', 'teacher_title', 'department'],
    ];
}