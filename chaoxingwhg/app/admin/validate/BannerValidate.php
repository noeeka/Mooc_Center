<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/1
 * Time: 13:45
 */

namespace app\admin\validate;


use think\Validate;

class BannerValidate extends Validate
{
    protected $rule = [
        'title' => 'require',
        'description' => 'require',
        'author' => 'require',
        'img' => 'require',
        'url'  => 'require',
    ];

    protected $message = [
        'title.require' => '标题不能为空',
        'description.require' => '简介不能为空',
        'author.require' => '来源/作者不能为空',
        'img.require' => '缩略图不能为空',
        'url.require'  => '链接不能为空',
    ];

}