<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2018/3/12
 * Time: 10:41
 */

namespace app\admin\validate;
use think\Validate;

class OpinionValidate extends Validate
{
    protected $rule = [
        'title' => 'require',
        'start_time' => 'require|date',
        'end_time' => 'require|date',
        'area_id' => 'checkAddress',
        'venue_id' => 'checkVenue',
        'content' => 'require',
    ];
    protected $message = [
        'title.require' => '标题不能为空！',
        'start_time.require' => '开始时间不能为空！',
        'end_time.require' => '结束时间不能为空！',
        'area_id.require' => '区域不能为空！',
        'venue_id.require' => '场馆不能为空！',
        'content.require' => '民意内容不能为空！',
    ];

    function checkAddress($value)
    {
        return empty($value) ? '地区不能为空':true;
    }

    function checkVenue($value)
    {
        return empty($value) ? '场馆不能为空':true;
    }
}