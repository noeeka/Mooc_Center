<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/2/28
 * Time: 15:00
 */

namespace app\api\controller;

use think\Db;

class FootprintController extends Base
{

    public function read()
    {

         $yaoqingma=input('yaoqingma','','string');
         $whgname=input('whgname','','string');

         Db::name('footprint')->data(['yaoqingma'=>$yaoqingma,'whgname'=>$whgname,'addtime'=>time()])->insert();

    }

}