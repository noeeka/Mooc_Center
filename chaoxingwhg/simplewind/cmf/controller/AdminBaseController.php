<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace cmf\controller;

use think\Db;

class AdminBaseController extends BaseController
{

    public function _initialize()
    {
        // 监听admin_init
        hook('admin_init');
        parent::_initialize();
        $session_admin_id = session('ADMIN_ID');
        if (!empty($session_admin_id)) {
            $user = Db::name('user')->where(['id' => $session_admin_id])->find();

            if (!$this->checkAccess($session_admin_id)) {
                $this->error("您没有访问权限！");
            }
            $this->assign("admin", $user);
        } else {
            if ($this->request->isPost()) {
                $this->error("您还没有登录！", url("admin/public/login"));
            } else {
                header("Location:" . url("admin/public/login"));
                exit();
            }
        }

        $this->_initLog();
    }

    public function _initializeView()
    {
        $cmfAdminThemePath    = config('cmf_admin_theme_path');
        $cmfAdminDefaultTheme = cmf_get_current_admin_theme();

        $themePath = "{$cmfAdminThemePath}{$cmfAdminDefaultTheme}";

        $root = cmf_get_root();

        //使cdn设置生效
        $cdnSettings = cmf_get_option('cdn_settings');
        if (empty($cdnSettings['cdn_static_root'])) {
            $viewReplaceStr = [
                '__ROOT__'     => $root,
                '__TMPL__'     => "{$root}/{$themePath}",
                '__STATIC__'   => "{$root}/static",
                '__WEB_ROOT__' => $root
            ];
        } else {
            $cdnStaticRoot  = rtrim($cdnSettings['cdn_static_root'], '/');
            $viewReplaceStr = [
                '__ROOT__'     => $root,
                '__TMPL__'     => "{$cdnStaticRoot}/{$themePath}",
                '__STATIC__'   => "{$cdnStaticRoot}/static",
                '__WEB_ROOT__' => $cdnStaticRoot
            ];
        }

        $viewReplaceStr = array_merge(config('view_replace_str'), $viewReplaceStr);
        config('template.view_base', "$themePath/");
        config('view_replace_str', $viewReplaceStr);
    }


    public function _initLog()
    {
        if (config('log.debug') == true) {

            $modules = $this->request->module();
            $controller = $this->request->controller();
            $action = $this->request->action();
            $param = $this->request->param();
            $session_admin_id = session('ADMIN_ID');

            $controller == "Article" &&  isset($param['id']) && $action = $param['id'];
            $info = Db::name('admin_menu')->where(
                [
                    'app' => $modules ,
                    'controller' => $controller ,
                    'action' => $action
                ]
            )->find();
            $userAction = Db::name('user_action_log')->where([
                "action" => $modules.'/'.$controller ."/".$action,
                "user_id" => $session_admin_id
            ])->find();

            if($info && $session_admin_id && empty($userAction)) {
                $userInfo = Db::name('user')->where([
                    'id'=> $session_admin_id
                ])->find();
                $role = Db::name('role_user')->join('role', 'role_id = role.id' )->where('user_id' , $session_admin_id)->find();
                if ($session_admin_id == 1 && empty($role) ) {
                    $role['name'] = "超级管理员";
                    $role['id'] = 1;
                }
                Db::name('user_action_log')->insert([
                    "user_id" => $session_admin_id,
                    "count" => 1,
                    "last_visit_time" => time(),
                    "object" => $info['name'] ,
                    "action" => $modules.'/'.$controller ."/".$action ,
                    "user_name" => $userInfo['user_login'],
                    "user_role_name" => $role['name'] ,
                    "user_role" =>  $role['id'],
                    "ip" => get_client_ip(),
                ]);
            } else {
                Db::name('user_action_log')->where([
                    "action" => $modules.'/'.$controller ."/".$action,
                    "user_id" => $session_admin_id
                ])->update(
                    [
                        "ip" => get_client_ip(),
                        "last_visit_time" => time(),
                        "count" => $userAction['count'] + 1,
                    ]
                );
            }
        }

    }

    /**
     * 初始化后台菜单
     */
    public function initMenu()
    {
    }

    /**
     *  检查后台用户访问权限
     * @param int $userId 后台用户id
     * @return boolean 检查通过返回true
     */
    private function checkAccess($userId)
    {
        // 如果用户id是1，则无需判断
        if ($userId == 1) {
            return true;
        }

        $module     = $this->request->module();
        $controller = $this->request->controller();
        $action     = $this->request->action();
        $rule       = $module . $controller . $action;

        $notRequire = ["adminIndexindex", "adminMainindex"];
        if (!in_array($rule, $notRequire)) {
            return cmf_auth_check($userId);
        } else {
            return true;
        }
    }

}