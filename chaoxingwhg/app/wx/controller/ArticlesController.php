<?php

namespace app\wx\controller;

use app\admin\model\PortalCategoryModel;
use cmf\controller\HomeBaseController;

/**
 * Created by PhpStorm.
 * User: tony
 * Date: 2018/5/9
 * Time: 9:46
 */
class ArticlesController extends HomeBaseController
{
    public function index()
    {
        $id = input('param.id', 0, 'intval');
        $thisNode = PortalCategoryModel::get($id);
        if ($thisNode == null) {
            $this->error('参数异常');
        } else {
            $this->assign([
                'id' => $id,
                'title' => $thisNode->name
            ]);
            return $this->fetch();
        }
    }
}