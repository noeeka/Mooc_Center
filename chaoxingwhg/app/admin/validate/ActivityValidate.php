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

class ActivityValidate extends Validate
{
    protected $rule = [
        'title' => 'require',
        'abstract' => 'require',
        'venue' => 'checkVenue',
        'type' => 'checkType',
        'address'=>'require',
        'content'=>'require',
        'start_time'=>'require',
        'end_time'=>'require',
        'like_count' => 'number',
        'page_view' => 'number',
        'contacts_mobile' => 'checkMobile'
    ];
    protected $message = [
        'title.require' => '请填写标题！',
        'abstract.require' => '请填写简介',
        'address.require' => '请填写活动地点！',
        'content.require' => '请填写活动内容！',
        'start_time.require' => '请填写活动开始时间！',
        'end_time.require' => '请填写活动结束时间！',
        'like_count' => '点赞量必须是数字',
        'page_view' => '浏览次数必须是数字',
        'contacts_mobile.checkMobile' => '联系电话格式不正确'
    ];

    function checkType($value)
    {
        return empty($value) ? '活动类型不能为空':true;
    }

    function checkVenue($value)
    {
        return empty($value) ? '场馆不能为空':true;
    }

    function checkMobile($value) {
        if(preg_match("/^1[34578]{1}\d{9}$/",$value)){
            return true;
        }else{
            return false;
        }
    }
}