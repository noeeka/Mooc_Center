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

class AdminArticleValidate extends Validate
{
    protected $rule = [
        'categories' => 'require',
        'address' => 'checkAddress',
        'venue' => 'checkVenue',
        'post_title' => 'require',
        'post_excerpt|摘要' => 'require',
        'more.thumbnail|缩略图' => 'require',
    ];
    protected $message = [
        'categories.require' => '请指定文章分类！',
        'post_title.require' => '文章标题不能为空！',
    ];

    function checkAddress($value)
    {
        return empty($value) ? '地区不能为空':true;
    }

    function checkVenue($value)
    {
        return empty($value) ? '场馆不能为空':true;
    }

    protected $scene = [
//        'add'  => ['user_login,user_pass,user_email'],
//        'edit' => ['user_login,user_email'],
    ];
}