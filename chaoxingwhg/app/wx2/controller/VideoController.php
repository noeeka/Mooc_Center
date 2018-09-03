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
namespace app\wx2\controller;

use app\admin\model\PortalCategoryModel;
use cmf\controller\HomeBaseController;

class VideoController extends HomeBaseController
{
    public function index()
    {

        $this->assign('id', '49');
        $this->assign('title', '信息资讯');
        return $this->fetch('index');
    }



    public function read(){

        return $this->fetch();
    }
}
