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

use app\admin\model\DiscussionModel;
use app\admin\model\OpinionIdeaModel;
use app\admin\model\OptionModel;
use app\admin\model\SfzimgModel;
use cmf\controller\AdminBaseController;
use think\Db;
use app\admin\model\AdminMenuModel;

class IndexController extends AdminBaseController
{

    public function _initialize()
    {
        $adminSettings = cmf_get_option('admin_settings');
        if (empty($adminSettings['admin_password']) || $this->request->path() == $adminSettings['admin_password']) {
            $adminId = cmf_get_current_admin_id();
            if (empty($adminId)) {
                session("__LOGIN_BY_CMF_ADMIN_PW__", 1);//设置后台登录加密码
            }
        }

        parent::_initialize();
    }

    /**
     * 后台首页
     */
    public function index()
    {
        $adminMenuModel = new AdminMenuModel();
        $menus          = cache('admin_menus_' . cmf_get_current_admin_id(), '', null, 'admin_menus');

        if (empty($menus)) {
            $menus = $adminMenuModel->menuTree();
            cache('admin_menus_' . cmf_get_current_admin_id(), $menus, null, 'admin_menus');
        }

        $count[]='';
        $sfz = new SfzimgModel();
        $op = new OpinionIdeaModel();
        $dis = new DiscussionModel();
        $ops = $dis->where(['is_delete'=>1])->field('id')->select()->toArray();
        $a[] ='';
        for($i=0;$i<count($ops);$i++){
            $a[$i] =$ops[$i]['id'];
        }

        $count['sfz'] = $sfz->where(['status'=>0])->count();
        $count['myzj'] = $op->where(['status'=>1,'is_delete'=>1])->where('opinion_id','in',$a)->count();
//        print_r($count);die;
//        echo $count['myzj'];die;
        $this->assign("menus", $menus);
        $this->assign("count", $count);




        //$admin = Db::name("user")->where('id', cmf_get_current_admin_id())->find();
        //$this->assign('admin', $admin);
        return $this->fetch();
    }
}
