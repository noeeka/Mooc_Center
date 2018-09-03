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

use cmf\controller\AdminBaseController;
use app\admin\model\UserModel;
use think\Db;


/**
 * Class UserController
 * @package app\admin\controller
 * @adminMenuRoot(
 *     'name'   => '管理组',
 *     'action' => 'default',
 *     'parent' => 'user/AdminIndex/default',
 *     'display'=> true,
 *     'order'  => 10000,
 *     'icon'   => '',
 *     'remark' => '管理组'
 * )
 */
class UserController extends AdminBaseController
{

    /**
     * 管理员列表
     * @adminMenu(
     *     'name'   => '管理员',
     *     'parent' => 'default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员管理',
     *     'param'  => ''
     * )
     */
    public $area=[];
    public function index()
    {
        $where = ["user_type" => 1];
        /**搜索条件**/
        $userLogin = $this->request->param('user_login');
        $userEmail = trim($this->request->param('user_email'));

        if ($userLogin) {
            $where['u.user_login'] = ['like', "%$userLogin%"];
        }

        if ($userEmail) {
            $where['u.user_email'] = ['like', "%$userEmail%"];;
        }
        $users = Db::name('user u')
            ->where($where)
            ->join('role_user m','m.user_id= u.id')
            ->join('role r','r.id=m.role_id')
            ->order("u.id DESC")
            ->field('u.*,r.name')
            ->paginate(10);
        $venue_list=[];
        foreach($users as &$v){
            $venue = Db::name('venue')
                ->where(['id'=>['in',$v['venue']]])
                ->field('name')
                ->select()
                ->toArray();
            $v['venue_list'] =$venue;
            $venue_list[] = $v;
        }

        $users->appends(['user_login' => $userLogin, 'user_email' => $userEmail]);
        // 获取分页显示
        $page = $users->render();

        $rolesSrc = Db::name('role')->select();
        $roles    = [];
        foreach ($rolesSrc as $r) {
            $roleId           = $r['id'];
            $roles["$roleId"] = $r;
        }

        $this->assign("page", $page);
        $this->assign("roles", $roles);
        $this->assign("users", $venue_list);
        return $this->fetch();
    }

    /**
     * 管理员添加
     * @adminMenu(
     *     'name'   => '管理员添加',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员添加',
     *     'param'  => ''
     * )
     */
    public function add()
    {
        $roles = Db::name('role')->where(['status' => 1])->order("id DESC")->select();

        //区域列表
        $areas = Db::name('area')->select();

        $this->assign("roles", $roles);
        $this->assign("areas", $areas);


        //获取顶级区域
        $parent_areas=UserModel::get_parent_areas();
//        var_dump($parent_areas);die;
        $this->assign("parent_areas", $parent_areas);

        return $this->fetch();
    }

    /**
     * 管理员添加提交
     * @adminMenu(
     *     'name'   => '管理员添加提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员添加提交',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        if ($this->request->isPost()) {

            if (!empty($_POST['role_id']) && is_array($_POST['role_id'])) {
                $role_ids = $_POST['role_id'];
                unset($_POST['role_id']);
                $result = $this->validate($this->request->param(), 'User');
                if ($result !== true) {
                    $this->error($result);
                } else {
                    if(!empty($_POST['venue']) && is_array($_POST['venue'])){
                        $_POST['venue']= implode(',', $_POST['venue']);
                      //  $_POST['area']= implode(',', $_POST['area']);
                    }else{
                        $this->error("请为此用户指定场馆！");
                    }
                    $_POST['user_type'] = 1;
                    $_POST['user_pass'] = cmf_password($_POST['user_pass']);
                    $_POST['volun_skill_imgs']='';
                    $result             = DB::name('user')->insertGetId($_POST);
                    if ($result !== false) {
                        //$role_user_model=M("RoleUser");
                        foreach ($role_ids as $role_id) {
                            if (cmf_get_current_admin_id() != 1 && $role_id == 1) {
                                $this->error("为了网站的安全，非网站创建者不可创建超级管理员！");
                            }
                            Db::name('RoleUser')->insert(["role_id" => $role_id, "user_id" => $result]);
                        }
                        $this->success("添加成功！", url("user/index"));
                    } else {
                        $this->error("添加失败！");
                    }
                }
            } else {
                $this->error("请为此用户指定角色！");
            }

        }
    }

    /**
     * 管理员编辑
     * @adminMenu(
     *     'name'   => '管理员编辑',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员编辑',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
        $id    = $this->request->param('id', 0, 'intval');
        $roles = DB::name('role')->where(['status' => 1])->order("id DESC")->select();
        $this->assign("roles", $roles);
        $role_ids = DB::name('RoleUser')->where(["user_id" => $id])->column("role_id");
        $this->assign("role_ids", $role_ids);

        $user = DB::name('user')->where(["id" => $id])->find();
        //获取已选中场馆
        $venues=Db::name('venue')->where(['id'=>['in',$user['venue']]])->select();

        //获取顶级区域
        $parent_areas=UserModel::get_parent_areas();
        $this->assign("parent_areas", $parent_areas);

        //获取已选择的区域
        $areas=Db::name('venue')->where(['id'=>['in',$user['venue'],'status'=>1]])->column('distinct address');

        $areas_arr=[];
        foreach($areas as $a){
            $this->parent_areas($a);
            $areas_arr=array_merge($areas_arr,$this->area);
        }


        $this->assign($user);
        $this->assign("areas", implode(',',$areas_arr));
        $this->assign("venues", $venues);
        $this->assign("v", $user['venue']);
        return $this->fetch();
    }

    /**
     * 管理员编辑提交
     * @adminMenu(
     *     'name'   => '管理员编辑提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员编辑提交',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {
        if ($this->request->isPost()) {
            if (!empty($_POST['role_id']) && is_array($_POST['role_id'])) {
                if (empty($_POST['user_pass'])) {
                    unset($_POST['user_pass']);
                } else {
                    $_POST['user_pass'] = cmf_password($_POST['user_pass']);
                }
                $role_ids = $this->request->param('role_id/a');
                unset($_POST['role_id']);
                $result = $this->validate($this->request->param(), 'User.edit');

                if ($result !== true) {
                    // 验证失败 输出错误信息
                    $this->error($result);
                } else {
                    if(!empty($_POST['venue']) && is_array($_POST['venue'])){
                        $_POST['venue']= implode(',', $_POST['venue']);
                       // $_POST['area']= implode(',', $_POST['area']);
                    }else{
                        $this->error("请为此用户指定场馆！");
                    }
                    $result = DB::name('user')->update($_POST);
                    if ($result !== false) {
                        $uid = $this->request->param('id', 0, 'intval');
                        DB::name("RoleUser")->where(["user_id" => $uid])->delete();
                        foreach ($role_ids as $role_id) {
                            if (cmf_get_current_admin_id() != 1 && $role_id == 1) {
                                $this->error("为了网站的安全，非网站创建者不可创建超级管理员！");
                            }
                            DB::name("RoleUser")->insert(["role_id" => $role_id, "user_id" => $uid]);
                        }
                        $this->success("保存成功！");
                    } else {
                        $this->error("保存失败！");
                    }
                }
            } else {
                $this->error("请为此用户指定角色！");
            }

        }
    }

    /**
     * 管理员个人信息修改
     * @adminMenu(
     *     'name'   => '个人信息',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员个人信息修改',
     *     'param'  => ''
     * )
     */
    public function userInfo()
    {
        $id   = cmf_get_current_admin_id();
        $user = Db::name('user')->where(["id" => $id])->find();
        $this->assign($user);
        return $this->fetch();
    }

    /**
     * 管理员个人信息修改提交
     * @adminMenu(
     *     'name'   => '管理员个人信息修改提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员个人信息修改提交',
     *     'param'  => ''
     * )
     */
    public function userInfoPost()
    {
        if ($this->request->isPost()) {

            $data             = $this->request->post();
            $data['birthday'] = strtotime($data['birthday']);
            $data['id']       = cmf_get_current_admin_id();
            $create_result    = Db::name('user')->update($data);;
            if ($create_result !== false) {
                $this->success("保存成功！");
            } else {
                $this->error("保存失败！");
            }
        }
    }

    /**
     * 管理员删除
     * @adminMenu(
     *     'name'   => '管理员删除',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员删除',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $id = $this->request->param('id', 0, 'intval');
        if ($id == 1) {
            $this->error("最高管理员不能删除！");
        }

        if (Db::name('user')->delete($id) !== false) {
            Db::name("RoleUser")->where(["user_id" => $id])->delete();
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }

    /**
     * 停用管理员
     * @adminMenu(
     *     'name'   => '停用管理员',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '停用管理员',
     *     'param'  => ''
     * )
     */
    public function ban()
    {
        $id = $this->request->param('id', 0, 'intval');
        if (!empty($id)) {
            $result = Db::name('user')->where(["id" => $id, "user_type" => 1])->setField('user_status', '0');
            if ($result !== false) {
                $this->success("管理员停用成功！", url("user/index"));
            } else {
                $this->error('管理员停用失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    /**
     * 启用管理员
     * @adminMenu(
     *     'name'   => '启用管理员',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '启用管理员',
     *     'param'  => ''
     * )
     */
    public function cancelBan()
    {
        $id = $this->request->param('id', 0, 'intval');
        if (!empty($id)) {
            $result = Db::name('user')->where(["id" => $id, "user_type" => 1])->setField('user_status', '1');
            if ($result !== false) {
                $this->success("管理员启用成功！", url("user/index"));
            } else {
                $this->error('管理员启用失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }
    //获取区域内的所有场馆
    public function get_venue(){
        $areas =$_POST['areas'];
        if(!empty($areas)){
            $venues=Db::name('venue')->where(['address'=>['in',$areas]])->select();
            echo json_encode(['status'=>1,'data'=>$venues,'mess
            age'=>'']);exit;
        }else{
            echo json_encode(['status'=>0,'data'=>'','message'=>'区域不能为空']);exit;
        }

    }
    /**
     *获取子区域
     * $area_id 区域id
     * $level 区域等级
     */
    public function children_area(){

           $area_id=$_POST['area_id'];
           $level=$this->request->param('level', 0, 'intval');

           if(empty($area_id)){
               echo json_encode(['status'=>0,'data'=>'','message'=>'没有区域']);exit;
           }
           //获取当前区域下的子区域
           $children_areas=UserModel::children_areas($area_id);
           //获取当前区域下的所有场馆
           $venues=UserModel::area_venues($area_id);

           $data['areas']=!empty($children_areas)?$children_areas:'' ;
           $data['venues']=!empty($venues)?$venues:'';

           if(!empty($data)){
               echo json_encode(['status'=>1,'data'=>$data,'level'=>$level]);exit;
           }else{
               echo json_encode(['status'=>0,'data'=>'','message'=>'没有区域']);exit;
           }
    }
    /**
     * 获取父区域
     * $area_id
     */
    public function parent_areas($area_id,$parent_ids=[]){
        array_push($parent_ids,$area_id);
        $parent_id=Db::name('area')->where(['status'=>1,'id'=>$area_id])->value('parent_id');
        $parent_area=Db::name('area')->where(['status'=>1,'id'=>$parent_id])->find();

        if($parent_area['parent_id']!=0){
            $this->parent_areas($parent_area['id'],$parent_ids);
        }else{
            array_push($parent_ids,$parent_area['id']);
            $this->area=$parent_ids;
        }
    }
}