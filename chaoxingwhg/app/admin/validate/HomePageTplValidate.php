<?php
/** `
 * Created by PhpStorm.
 * User: jxbx
 * Date: 2018/5/22
 * Time: 15:30
 */

namespace app\admin\validate;

use think\Validate;

class HomePageTplValidate extends Validate{
    protected $rule = [
        'name' => 'require',
        'html' => 'require',
        'exam_img' => 'require',
        'len'=>'require|checkVal',
    ];

    protected function checkVal($value,$rule,$data)
    {
        return $value < 0 ? '长度不能小于0' : true;
    }

    protected $message = [
        'name.require' => '模板名称名称不能为空',
        'html.require'=>'html不能为空',
        'exam_img.require'=>'示例图不能为空',
        'len.require'=>'长度不能为空',


    ];
}