<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2018/3/9
 * Time: 14:58
 */

namespace app\admin\validate;

use think\Validate;

class VolunBannerValidate extends Validate
{
    protected $rule = [
        'description' => 'require',
        'author' => 'require',
        'publish_time' => 'require',
        'img' => 'require',
        'url' => 'require',

    ];
    protected $message = [
        'description.require' => '简介不能为空！',
        'author.require' => '作者不能为空！',
        'publish_time.require' => '发布时间不能为空！',
        'img.require' => '图片不能为空！',
        'url.require' => '链接不能为空！',

    ];
}