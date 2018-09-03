<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2018/3/9
 * Time: 17:22
 */

namespace app\admin\model;
use think\Model;

class LiveBroadcastModel extends Model
{
    public function setStartTimeAttr($value){
        return strtotime($value);
    }

    public function setEndTimeAttr($value){
        return strtotime($value);
    }
}