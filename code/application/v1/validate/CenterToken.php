<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 2018/6/28
 * Time: 9:38
 */

namespace app\v1\validate;

use think\Validate;
use app\v1\model\MoocCenter;

class CenterToken extends Validate
{

    protected $rule = [
        'center_token|场馆令牌' => 'require|max:40|isAvaliable',
    ];

    protected function isAvaliable($center_token)
    {
        $center = MoocCenter::where('center_token', $center_token)->find();
        if ($center == null) {
            return '令牌无效，场馆不存在';
        }
        $max_time = $center['access_time'] + config('mooc_center_expire_time');
        if (time() > $max_time) {
            return '令牌过期，请重新登陆';
        }
        if ($center['status'] == 0) {
            return '场馆被禁用';
        }
        return true;
    }
}