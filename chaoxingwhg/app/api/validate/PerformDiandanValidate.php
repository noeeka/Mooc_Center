<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2018/3/14
 * Time: 15:23
 */

namespace app\api\validate;


use think\Validate;

class PerformDiandanValidate extends Validate
{
    protected $rule = [
        'perform_id' => 'require',
        'area_id' => 'require',



    ];
    protected $message = [
        'perform_id.require' => '活动名称不能为空！',
        'area_id.require' => '场馆不能为空！',

    ];
}