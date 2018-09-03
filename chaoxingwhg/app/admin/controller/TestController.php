<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 2018/5/8
 * Time: 11:19
 */

namespace app\admin\controller;


use cmf\controller\AdminBaseController;

class TestController extends AdminBaseController
{
    public function index(){
        $id = $this->request->param('id');
        var_dump($id);
    }
}