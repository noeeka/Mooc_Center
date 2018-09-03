<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/1
 * Time: 11:01
 */

namespace app\admin\model;


use think\Model;
use token\Token;

class LikeModel extends Model
{
    static function have($resource_id, $type){
        $token = input('param.token');
        $user_id = Token::get_user_id($token);
        if($user_id > 0){
            return  self::where(['resource_id'=>$resource_id, 'type'=>$type, 'user_id'=>$user_id])->count(1);
        }else{
            return 0;
        }
    }
}