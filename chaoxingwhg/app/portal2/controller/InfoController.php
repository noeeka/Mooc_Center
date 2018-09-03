<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\portal\controller;

use cmf\controller\HomeBaseController;

class CategoryController extends HomeBaseController
{
    public function info()
    {
        $this->assign('id', '8');
        return $this->fetch('index');
    }

    public function muke()
    {
        $this->assign('id', '19');
        return $this->fetch('index');
    }

    public function quanjing()
    {
        $this->assign('id', '18');
        return $this->fetch('index');
    }
}
