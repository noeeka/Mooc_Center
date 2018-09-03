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

class ProductCollectController extends HomeBaseController
{
    public function index()
    {
        return $this->fetch(':product_collect/index');
    }
    public function read()
    {
        return $this->fetch(':product_collect/read');
    }
    public function collect()
    {
        return $this->fetch(':product_collect/collect');
    }
     public function details()
    {
        return $this->fetch(':product_collect/details');
    }
}
