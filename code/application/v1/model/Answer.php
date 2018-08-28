<?php
/**
 * Created by PhpStorm.
 * User: jxbx
 * Date: 2018/7/31
 * Time: 10:07
 */

namespace app\v1\model;

use think\Model;

class Answer extends Model
{

    public function index1(){
        return encrypt_key([1],2);
    }
}