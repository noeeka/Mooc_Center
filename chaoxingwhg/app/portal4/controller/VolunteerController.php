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
use app\admin\model\AreaModel;

class VolunteerController extends HomeBaseController
{
    public function index()
    {
        return $this->fetch('index');
    }
    public function sign()
    {
//        $areaModel = new AreaModel();
//        $areasTree = $areaModel->adminAreaTree(0, 0);
//        $this->assign('areas_tree',$areasTree);
        return $this->fetch('sign');
    }
 public function elegant()
    {
        return $this->fetch('elegant');
    }
    public function recruite()
    {
        return $this->fetch('recruite');
    }

    public function reports()
    {
        return $this->fetch('reports');
    }
    public function recruiteread()
    {
        return $this->fetch('recruiteread');
    }
    public function reportsread()
    {
        return $this->fetch('reportsread');
    }
}
