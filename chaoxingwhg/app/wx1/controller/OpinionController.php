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
namespace app\wx1\controller;

use cmf\controller\HomeBaseController;

class OpinionController extends HomeBaseController
{
    public function info()
    {

       // halt($this->view);
        return $this->fetch('opinion/info');
    }


    public function detail()
    {
        $id = input('id' , '0' ,'intval');
        $this->assign('id' , $id);
        return $this->fetch('opinion/detail');
    }

}
