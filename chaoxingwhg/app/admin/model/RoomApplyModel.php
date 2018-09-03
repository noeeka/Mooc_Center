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

use think\Model;

class RoomApplyModel extends Model
{
    function book($room_id, $user_id, $start_time)
    {
        $start_time = strtotime(date('Y-m-d H:00:00', $start_time));
        if ($start_time <= time()) {
            return '只能预约当前时间之后的时间段';
        } else {
            $room = RoomModel::get($room_id);
            if ($room == null) {
                return '场馆不存在';
            } else {
                $num = $this->where(['room_id'=>$room_id, 'start_time'=>$start_time])->count(1);
                if($num > 0){
                    return '该时间段已被预约';
                }
                $week = date('w', $start_time);
                if (!in_array($week, $room->getWeeks())) {
                    return '无法预约';
                } else {
                    $start_hour = date('G', $start_time);
                    if ($start_hour > 23) {
                        return '开始时间不能大于23点';
                    }
                    $end_time = strtotime('+1 hour', $start_time);
                    $end_hour = date('G', $end_time);
                    if (!$room->checkDur($start_hour, $end_hour)) {
                        return '预约时间不在开放区间';
                    }else{
                        $this->save(['start_time'=>$start_time,'room_id'=>$room_id, 'end_time'=>$end_time, 'user_id'=>$user_id, 'create_time'=>time(), 'update_time'=>time()]);
                        return true;
                    }
                }
            }
        }
    }
}