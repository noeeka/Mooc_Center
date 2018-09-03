<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\admin\model;

use app\admin\model\RouteModel;
use think\Model;
use think\Db;

class ActivitySignInModel extends Model
{   // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    protected $table = "cxtj_activity_signIn";

    public static function insertSignIn($userInfo  , $sfzInfo , $activityId , $mobile , $type)
    {
        $data=[
            "user_id" => isset($userInfo['id']) ? $userInfo['id'] : 0,
            "activity_id" => $activityId ,
            'sign_mobile' => $mobile,
            "user_name" => isset($userInfo['user_login']) ? $userInfo['user_login']: '' ,
            "real_name" => isset($sfzInfo) ?  $sfzInfo->toArray()['realname'] : '',
            "sex" =>  isset($userInfo['sex']) ? $userInfo['sex']:'' ,
            "birth_time" =>  isset($userInfo['birth_time']) ?$userInfo['birth_time'] :"" ,
            "address" => isset($userInfo['address']) ? $userInfo['address'] :"" ,
            "create_time" => time(),
            'delete_time' => 0 ,
            "type" => $type
        ];

        return self::insert($data);
    }
}