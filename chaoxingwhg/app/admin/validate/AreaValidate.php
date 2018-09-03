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

class AreaValidate extends Validate
{
    protected $rule = [
        'parent_id' => 'require',
        'name' => 'require',
    ];

    protected $message = [
        'name.require' => '区域名称不能为空',
        'parent_id.require'  => '上级名称不能为空',
    ];

}