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

class CollectModel extends Model
{
    static function have($id){
        $token = input('param.token');
        $user_id = Token::get_user_id($token);
        if($user_id > 0){
            return  self::where(['article_id'=>$id, 'user_id'=>$user_id])->count(1);
        }else{
            return 0;
        }
    }
}