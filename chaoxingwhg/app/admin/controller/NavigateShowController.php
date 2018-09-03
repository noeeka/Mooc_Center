<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\RouteModel;
use cmf\controller\AdminBaseController;
use think\Db;
use app\admin\model\TemplateModel;

class NavigateShowController extends AdminBaseController
{

    /**
     * 首页导航模板列表
     */
    public function index()
    {
        $navModel = new TemplateModel();
        $nav_list = $navModel->where('type',1)->select();
        $this->assign('nav_list', $nav_list);

        return $this->fetch();
    }
}