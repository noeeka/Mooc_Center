<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\validate;

use think\Validate;

class ProductionCollectValidate extends Validate
{
    protected $rule = [
        'name' => 'require',
        'venue' => 'checkVenue',
        'type' => 'checkType',
        'content'=>'require',
        'preview_start_time'=>'require',
        'preview_end_time'=>'require',
        'collect_start_time'=>'require',
        'collect_end_time'=>'require',
        'choose_start_time'=>'require',
        'choose_end_time'=>'require',
        'publish_time'=>'require'
    ];
    protected $message = [
        'name.require' => '请填写征集名称！',
        'content.require' => '请填写活动内容！',
        'preview_start_time.require' => '请填写预告开始时间！',
        'preview_end_time.require' => '请填写预告结束时间！',
        'collect_start_time.require' => '请填写征集开始时间！',
        'collect_end_time.require' => '请填写征集结束时间！',
        'choose_start_time.require' => '请填写筛选开始时间！',
        'choose_end_time.require' => '请填写筛选结束时间！',
        'publish_time.require' => '请填写公示时间！',
    ];

    function checkType($value)
    {
        return empty($value) ? '活动类型不能为空':true;
    }

    function checkVenue($value)
    {
        return empty($value) ? '场馆不能为空':true;
    }
}