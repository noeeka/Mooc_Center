<?php

namespace app\v1\validate;
use think\Validate;
use think\Db;

class MoocTeacher extends Validate
{
    protected $rule = [
        'teacher_name'  =>  'require|max:40',
        'teacher_title'  =>  'require|max:30',
        'department'  =>  'max:40',
        'avatar'  =>  'require|max:100',
        'teacher_user_id'=>'require|checkNameInfo|checkNameUnique',
        'teacher_password'=>'require|checkPassword',
    ];

    protected $message = [
        'teacher_name.require'  =>  '老师名必须填写',
        'teacher_name.max'  =>  '老师名最多20个汉字',
        'teacher_title.require'  =>  '老师头衔必须填写',
        'teacher_title.max'  =>  '老师头衔最多15个汉字',
        'department.max'  =>  '所属单位最多20个汉字',
        'avatar.require'  =>  '头像必须上传',
        'avatar.max'  =>  '头像图像地址最多100个字符',
        'teacher_user_id.require'  =>  '老师登陆ID必须填写',
        'teacher_password.require'  =>  '老师登陆密码必须填写',


    ];

    protected $scene = [
        'add'   =>  ['teacher_name','teacher_title','department','avatar','teacher_user_id','teacher_password'],
        'edit'  =>  ['teacher_name','teacher_title','department','avatar'],
    ];

    protected function checkNameUnique($value,$rule,$data)
    {
        $result=Db::name('mooc_teacher')->where('teacher_user_id',$value)->count('id');
        if($result>=1){
            return '该ID已注册';
        }else{
            return true;
        }
    }

    protected function checkNameInfo($value,$rule,$data){
        //必须是字母+数字
        //$result=preg_match('/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9a-zA-Z]+$/', $value);
        //字母或数字
        $result=preg_match("/^[0-9a-zA-Z]{8,16}$/", $value);


        $len=strlen($value);
        if(!$result){
            return 'ID必须是6-20位的字母和数字的组合';
        }elseif($len<6||$len>20){
            return 'ID必须是6-20位的字母和数字的组合';
        }else{
            return true;
        }
    }

    protected function checkPassword($value,$rule,$data){
        $len=strlen($value);
        if($len<6||$len>20){
            return '密码长度必须是6-20位';
        }else{
            return true;
        }
    }

}