<?php
/**
 * Created by PhpStorm.
 * User: jxbx
 * Date: 2018/5/22
 * Time: 17:55
 */

namespace app\admin\validate;

use think\Validate;

class HomePageTplSpecialValidate extends Validate{
    protected $rule = [
        'name|名称' => 'require',
        'type|类型' => 'require',
        'exam_img|示例图' => 'require',
        'alias|别名' => 'require',
//        'background_image|背景图片' => 'requireIf:type,1',
//        'background_color|背景颜色' => 'requireIf:type,1',
//        'css|CSS' => 'requireIf:type,1',
    ];

}