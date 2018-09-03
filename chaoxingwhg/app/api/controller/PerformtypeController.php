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
namespace app\api\controller;

use app\admin\model\PerformTypeModel;

class PerformTypeController extends Base
{
    public function index()
    {
        $type = input('param.type', 1, 'intval');
        $performTypeModel = new PerformTypeModel();
        $list = $performTypeModel->where(['status' => 1, 'type' => $type])->select();
        return $this->output_success(17101, $list, '获取类型成功');
    }
}