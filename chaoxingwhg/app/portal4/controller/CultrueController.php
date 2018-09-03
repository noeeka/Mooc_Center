<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2018/3/15
 * Time: 16:24
 */

namespace app\portal4\controller;


use cmf\controller\HomeBaseController;

class CultrueController extends HomeBaseController
{
    public function index(){
        return $this->fetch();
    }
}