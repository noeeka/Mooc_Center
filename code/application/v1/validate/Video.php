<?php
/**
 * Created by PhpStorm.
 * User: zhangchun
 * Date: 2018/6/28
 * Time: 11:43
 */

namespace app\v1\validate;

use think\Validate;

class Video extends Validate
{
    protected $rule=[
        'center_id'=>'require|number',
        'title'=>'require',
        'thumb'=>'require',
        'url'=>'require',
    ];

    protected $message = [
        'center_id.require'  =>  '文化馆不能为空',
        'centerid.number'  =>  '文化馆id必须是数字',
        'title.require'  =>  '标题不能为空',
        'thumb.require'  =>  '缩略图不能为空',
    ];
}