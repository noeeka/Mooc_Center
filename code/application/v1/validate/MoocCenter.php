<?php

namespace app\v1\validate;

use think\Validate;


class MoocCenter extends Validate
{
    protected $rule = [
        'id|场馆ID' => 'require|number|max:7',
        'center_name|场馆名称' => 'requireAdd|max:40',
        'address|地区' => 'max:255',
        'status|状态' => 'number|between:0,1',
    ];

    protected $scene = [
        'add' => ['center_name', 'address'],
        'edit' => ['id', 'center_name', 'address', 'status'],
    ];

    protected function requireAdd($value, $rules, $data){
        if ($this->currentScene == 'add') {
            if (empty($value)) {
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

}