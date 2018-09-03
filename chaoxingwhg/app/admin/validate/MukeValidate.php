<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/21
 * Time: 16:06
 */

namespace app\admin\validate;


use think\Validate;

class MukeValidate extends Validate
{
    protected $rule = [
        'categories' => 'require',
        'address' => 'checkAddress',
        'venue' => 'checkVenue',
        'post_title' => 'require',
//        'post_excerpt|摘要' => 'require',
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
