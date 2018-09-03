<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/21
 * Time: 14:20
 */

namespace app\admin\controller;


use cmf\controller\AdminBaseController;
use think\Db;

class SwitchController extends AdminBaseController
{
    public function index()
    {

        $switch=Db::name('switch')->where('id',1)->value('switch');

        $this->assign('switch', $switch);

        return $this->fetch();
    }
    public function editPost()
    {
        $switch = $this->request->param('switch');
        Db::name('switch')->update(['id'=>1,'switch'=>$switch]);

        $this->success("修改成功！", url("switch/index"));
    }


}