<?php

namespace app\wx2\controller;

use cmf\controller\HomeBaseController;

class ApplyController extends HomeBaseController
{

    public function index()
    {
        return $this->fetch('index');
    }
     public function info()
    {
        return $this->fetch('info');
    }


}
