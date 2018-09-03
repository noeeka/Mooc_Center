<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2018/3/15
 * Time: 14:15
 */

namespace app\portal1\controller;


use cmf\controller\HomeBaseController;

class LiveController extends HomeBaseController
{
     public function index(){
         return $this->fetch(':live/index');
     }
     public function huifang(){
         return $this->fetch(':live/huifang');
     }
}