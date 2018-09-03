<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2018/3/12
 * Time: 10:41
 */

namespace app\admin\validate;

use think\Validate;

class LiveBroadcastValidate extends Validate
{

    protected $rule = [
        'live_name' => 'require',
        'areaid' => 'checkNotSelect:直播区域',
        'venueid' => 'checkNotSelect:直播单位',
        'start_time' => 'require|date',
        'end_time' => 'require|date|checkEnd',
        'img' => 'require',
        'live_link' => 'require',
        "wx_live_link"=> 'require',
    ];

    function checkNotSelect($value, $rule, $data)
    {
//        var_dump($data);
        return $value == 0 ? $rule . '不能为空' : true;
    }

    function checkStart($value, $rule, $data)
    {
//        var_dump($data);
        if ((!isset($data['id']) || $data['id'] == 0)  && strtotime($value) <= time()) {
            return '开始时间必须大于当前时间';
        }
        return true;
    }

    function checkEnd($value, $rule, $data)
    {
//        var_dump($data);
        $start_time =  strtotime($data['start_time']);
        $end_time = strtotime($data['end_time']);
        if ($start_time > $end_time) {
            return '结束时间必须大于开始时间';
        }
        return true;
    }

    protected $message = [
        'live_name.require' => '直播名称不能为空！',
        'start_time.require' => '直播开始时间不能为空！',
        'end_time.require' => '直播结束时间不能为空！',
        'img.require' => '图片不能为空！',
        'live_link.require' => '直播链接不能为空！',
        'wx_live_link.require' => '微信端直播链接不能为空！',

    ];

}