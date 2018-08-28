<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 2018/7/5
 * Time: 10:00
 */

namespace app\proxy\controller;

use think\Controller;

class Index extends Controller
{

    public function index()
    {
        echo $this->request->module();
        echo $this->request->controller();
        echo var_dump($this->request->param());
        return 'dsadsad';
    }

}