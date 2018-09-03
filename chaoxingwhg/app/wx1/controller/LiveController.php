<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2018/3/15
 * Time: 14:15
 */

namespace app\wx1\controller;


use cmf\controller\HomeBaseController;

class LiveController extends HomeBaseController
{
     public function index(){
         return $this->fetch();
     }
}