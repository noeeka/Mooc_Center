<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/1
 * Time: 15:26
 */

namespace app\api\validate;


use think\Validate;

class ContactsValidate extends Validate
{
    protected $rule = [
        'type' => 'require|checkType|checkGuardian',
        'name' => 'require|max:4',
        'id_card' => 'require|checkIdCard',
        'mobile' => 'require|checkMobile',
    ];

    protected $message = [
        'type.require' => '联系人类型不能为空',
        'name.require' => '联系人姓名不能为空',
        'name.max' => '名字最多4个字',
        'id_card.require' => '联系人身份证不能为空',
        'mobile.require' => '联系人手机不能为空',
        'mobile.checkMobile' => '联系人手机格式不正确',
        'id_card.checkIdCard' => '身份证格式不正确',
        'type.checkType' => '联系人类型不正确',
        'type.checkGuardian' => "联系人监护人不能为空"
    ];

    function checkType($value) {
        $allowType = [1,2];
        if (in_array($value , array_values($allowType))) {
            return true;
        }
        return false;
    }

    function checkMobile($value) {
        if(preg_match("/^1[34578]{1}\d{9}$/",$value)){
            return true;
        }else{
           return false;
        }
    }

    function checkIdCard($value) {
        if (!isShenfenzheng($value)) {
            return false;
        }
        return true;
    }

    function checkGuardian($value , $rule , $data) {
        if ($value == 1)
            return true;
        if ($value == 2 && !empty($data['guardian']))
            return true;

        return false;
    }
}