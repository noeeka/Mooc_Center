<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/2/28
 * Time: 17:17
 */

namespace app\admin\validate;


use think\Validate;
class VenueValidate extends Validate
{
    protected $rule = [
        'name' => 'require',
        'address'  => 'checkAddress',
        "venue_addr" => "require"
    ];

    protected $message = [
        'name.require' => '名称不能为空',
        'venue_addr.require' => '详细地址不能为空',
    ];
    function checkAddress($value, $rules, $data){
        if($value == 0){
            return '所在地区不能为空';
        }
        return true;
    }
}