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

class WenhuaMangeValidate extends Validate
{
    protected $rule = [
        'map_img' => 'checkMapImg',
    ];

   function checkMapImg($rules, $data){
       return '1111111';
       if($data['map_is_diy_img'] == 1 && $data['map_img'] == ''){
           return '独立图标必须上传';
       }else{
           return true;
       }
   }
}