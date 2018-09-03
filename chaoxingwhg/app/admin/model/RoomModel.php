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

class RoomModel extends Model
{
    public function setPublishTimeAttr($value)
    {
        return strtotime($value);
    }

    public function getWeeks()
    {
        if ($this->preset_time == 0) {
            return range(0, 6);
        } elseif ($this->preset_time == 1) {
            return range(1, 5);
        } else {
            return explode(',', $this->custom_preset_time);
        }
    }

    public function checkDur($start_hour, $end_hour)
    {
        if (($start_hour >= $this->open_start_time_am &&
                $start_hour <= $this->open_end_time_am &&
                $end_hour >= $this->open_start_time_am
                && $end_hour <= $this->open_end_time_am) ||
            ($start_hour >= $this->open_start_time_pm &&
                $start_hour <= $this->open_end_time_pm &&
                $end_hour >= $this->open_start_time_pm &&
                $end_hour <= $this->open_end_time_pm)) {
            return true;
        } else {
            return false;
        }
    }

}