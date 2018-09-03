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

use app\admin\model\PortalCategoryModel;
use cmf\controller\HomeBaseController;

class CategoryController extends HomeBaseController
{
    public function info()
    {
        $this->assign('id', '8');
        $this->assign('title', '信息资讯');
        return $this->fetch('index');
    }

    public function muke()
    {
        return $this->fetch('muke');
    }

    public function resource()
    {
        $this->assign('id', '12');
        $this->assign('title', '资源展示');
        return $this->fetch('index');
    }

    public function quanjing()
    {
        $this->assign('id', '18');
        $this->assign('title', '全景展示');
        return $this->fetch('quanjing');
    }

    public function read(){
        $cid = input('param.cid', 8, 'intval');
        $this->assign('cid', $cid);
        return $this->fetch();
    }
}
