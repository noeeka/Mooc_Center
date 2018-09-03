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
namespace app\portal2\controller;

use cmf\controller\HomeBaseController;

class IndexController extends HomeBaseController
{
    /**
     * 前台首页
     *
     * @return view
     */
    public function index()
    {
        return $this->fetch('index/index');
    }
    public function indexnew()
        {
            return $this->fetch('index/index-new');
        }

    public function test()
    {
        return $this->fetch('test/index');
    }






}
