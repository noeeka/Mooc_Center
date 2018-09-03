<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 2018/4/2
 * Time: 9:39
 */

namespace app\portal\controller;

use cmf\controller\HomeBaseController;

class RoomController extends HomeBaseController
{

    public function index()
    {
        return $this->fetch();
    }

    public function read()
    {
        return $this->fetch('read');
    }
    public function readnew()
    {
        return $this->fetch('readnew');
    }
}