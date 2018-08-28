<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 2018/6/28
 * Time: 14:47
 */

namespace app\v1\controller;

use app\v1\model\MoocCenter;
use app\v1\model\MoocUser;
use app\v1\validate\MoocUser as UserValidate;
use app\v1\model\Follow;

class User extends Core
{
    /**
     * 用户列表
     * @param center_token 场馆令牌
     * @param title  标题
     * @param create_time  时间
     * @param page 页数
     * @param len  长度
     * @return array|bool
     */
    public function index()
    {
        $_GET['user_token'] = 'b616164e6ce937e8debb6345783a9746ebcd1e5c';
        $_GET['timestamp'] = strval(time());
        $salt = MoocUser::where('user_token', $_GET['user_token'])->value('salt');
        $_GET['sign'] = encrypt_key(['v1/user/index', $_GET['timestamp'], $_GET['user_token'], $salt], '');

        $type = input('param.type/a',[2]);
        $status = input('param.status', -1, 'intval');     //前台获取老师必传
        $page = input('param.page', 0, 'intval');
        $len = input('param.len', 0, 'intval');
        $all = input('param.all', -1, 'intval');  //超星馆获取所有传all
        $nick_name = input('param.nick_name', '', 'string');

        //身份校验
        $msg = verify();
        if ($msg['status'] == 0) {
            return $msg;
        } else {
            $center_id = $msg['data']['center_id'];
        }

        $where = [];
        if (!empty($type)) {
            if ($type != 4) {
                $where['u.type'] = ['in',$type];
            } else {
                return $this->fail(11011,'用户类型不存在');
            }
        }
        if ($status != -1) {
            $where['u.status'] = $status;
        }
        if (!empty($nick_name)) {
            $where['nick_name'] = ['like', "%$nick_name%"];
        }

        //数据获取
        $userModel = new MoocUser();
        $join[] = ['mooc_center mc', 'mc.id=u.center_id'];
        $field = 'u.id,u.type,u.user_login,u.nick_name,u.profile,u.avatar,u.teacher_title,u.company,u.department,u.status,u.center_id,mc.center_name';

        if ($all != -1) {
            if ($center_id != 1) {
                $where['u.center_id'] = $center_id;
            }
        }else{
            $where['u.center_id'] = $center_id;
        }

        if ($page==0 && $len == 0) {
            $userList = $userModel->alias('u')->join($join)->where($where)->field($field)->order(['u.center_id asc'])->select();

            return $this->ok( $userList,11111, '获取用户成功');
        } else {
            $userList = $userModel->alias('u')->join($join)->where($where)->field($field)->order(['u.center_id asc'])->page($page, $len)->select();
            $num = $userModel->alias('u')->join($join)->where($where)->count(1);
            return $this->ok(['list' => $userList, 'num' => $num], 11111, '获取用户成功');
        }

    }

    /**
     * 创建老师账号
     *
     * @param center_token 场馆令牌
     * @param user_login 登陆名
     * @param user_pass 密码
     * @param center_id 场馆ID 超星馆可以添加其他馆老师
     *
     * @return array|bool
     * @throws \think\exception\DbException
     */
    public function create()
    {
//        //测试代码
//        $_GET['center_token'] = 'd772d5c2ca2e10a36c6dbb3acae6850538ad0704';
//        $_GET['user_login'] = 'test12345';
//        $_GET['user_pass'] = '123456';
//        $_GET['center_id'] = 14;
//        $_GET['timestamp'] = time();
//        $salt = '8FrkI';
//        $_GET['sign'] = encrypt_key(['v1/user/create', $_GET['timestamp'], $_GET['center_token'], $salt], '');
//        var_dump(['v1/user/create_user', $_GET['timestamp'], $_GET['center_token'], $salt]);
        //初始化参数
        $center_token = input('param.center_token', '', 'trim');
        $user_login = input('param.user_login', '', 'trim');
        $user_pass = input('param.user_pass', '', 'trim');
        $confirm_pass = input('param.confirm_pass','','trim');
        $center_id = input('param.center_id', 1, 'intval');
        $nick_name = input('param.nick_name','','trim');
        $teacher_title = input('param.teacher_title','','trim');
        $department = input('param.department','','trim');
        $profile = input('param.profile','','trim');
        $avatar = input('param.avatar','','trim');
        $status = input('param.status',1,'intval');
        $type = input('param.type',2,'intval');

        //令牌校验
        $tokenRes = checkCenterToken($center_token);
        if (true !== $tokenRes) {
            return $tokenRes;
        }

        if(empty($user_login)){
            return $this->fail(12001,'用户名不能为空');
        }
        if(empty($user_pass)){
            return $this->fail(12002,'密码为空');
        }
        if(empty($confirm_pass)){
            return $this->fail(12003,'确认密码不能为空');
        }
        if($confirm_pass !== $user_pass){
            return $this->fail(12004,'密码与确认密码不一致');
        }

        //确认要添加的场馆
        $center = MoocCenter::where(['center_token' => $center_token])->find();
        if ($center['id'] == 1) {
            $data['center_id'] = $center_id;
//            if ($center_id == 1) {
//                return $this->fail(25018, '禁止通过此接口创建超级管理员');
//            }
            $num = MoocCenter::get($center_id);
            if (empty($num)) {
                return $this->fail(25019, '场馆不存在');
            }
        } else {
            $data['center_id'] = $center['id'];
        }

        //用户名校验
        $userRes = checkUserLogin($user_login, $data['center_id']);
        if (true !== $userRes) {
            return $userRes;
        }

        //密码校验
        $passRes = checkUserPass($user_pass);
        if (true !== $passRes) {
            return $passRes;
        }

        //数据整理
        $data['user_login'] = $user_login;
        $data['nick_name'] = $nick_name;
        $data['user_token'] = generate_token();
        $data['access_time'] = time();
        $data['salt'] = generate_salt();
        $data['user_pass'] = generate_password($user_pass, $data['salt']);
        $data['type'] = $type;
        $data['teacher_title'] = $teacher_title;
        $data['department'] = $department;
        $data['avatar'] = $avatar;
        $data['status'] = $status;
        $data['profile'] = $profile;
        $moocUserModel = new MoocUser();
        if ($moocUserModel->save($data) > 0) {
            return ok('', 25002, '创建用户成功', 1);
        } else {
            return fail(25017, '创建用户失败', 1);
        }
    }

    /**
     * 场馆后台修改用户密码
     * @param center_token 后台token
     * @param user_id 用户ID
     * @param user_pass 密码
     * @return array|bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function forget_user_from_center()
    {
        //测试代码
//        $_GET['center_token'] = 'afbe52d7014168fa8b70f2a6c716a88c3e06c910';
//        $_GET['user_login'] = 'test123';
//        $_GET['user_pass'] = '123456';
//        $_GET['user_id'] = 17;
//        $_GET['timestamp'] = strval(time());
//        $salt = 'l5Y2Q';
//        $_GET['sign'] = encrypt_key(['v1/user/forget_user_from_center', $_GET['timestamp'], $_GET['center_token'], $salt], '');
        //令牌校验
        $center_token = input('param.center_token', '');
        $centerRes = checkCenterToken($center_token);
        if (true !== $centerRes) {
            return $centerRes;
        }

        //用户校验
        $user_id = input('param.user_id', 0, 'intval');
        $user = MoocUser::where('id', $user_id)->find();
        if ($user == null) {
            return fail(26001, '该用户不存在', 1);
        }

        //场馆操作权限校验
        $center_id = MoocCenter::where('center_token', $center_token)->find();
        if ($center_id != 1 && $user['center_id'] != $center_id) {
            return fail(26002, '没有操作权限', 1);
        }

        //密码校验
        $user_pass = input('param.user_pass', '');
        $passRes = checkUserPass($user_pass);
        if (true !== $passRes) {
            return $passRes;
        }

        //数据提交
        $data['salt'] = generate_salt();
        $data['user_pass'] = generate_password($user_pass, $data['salt']);
        if (false === MoocUser::where('id', $user_id)->update($data)) {
            return fail(26003, '修改密码失败', 1);
        } else {
            return ok('', 26101, '修改密码成功', 1);
        }
    }

    /**
     * 老师修改密码
     * @param user_pass 密码
     * @return array|bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function forget_user_from_teacher()
    {
//        return $this->request->param();
        //        //测试代码
//        $_GET['user_token'] = '6cc6c00edab41244b13e3f71acab82439ae99ada';
////        $_GET['user_login'] = 'test123';
//        $_GET['user_pass'] = '123456454';
////        $_GET['center_id'] = 14;
//        $_GET['timestamp'] = strval(time());
//        $salt = MoocUser::where('user_token', $_GET['user_token'])->value('salt');
//        $_GET['sign'] = encrypt_key(['v1/user/forget_user_from_teacher', $_GET['timestamp'], $_GET['user_token'], $salt], '');
//var_dump(['v1/user/forget_user_from_teacher', $_GET['timestamp'], $_GET['user_token'], $salt]);

        //令牌校验
        $user_token = input('param.user_token', '');
        $tokenRes = checkUserToken($user_token);
        if (true !== $tokenRes) {
            return $tokenRes;
        }

        //密码校验
        $user_pass = input('param.user_pass', '');
        $passRes = checkUserPass($user_pass);
        if (true !== $passRes) {
            return $passRes;
        }

        //数据提交
        $data['salt'] = generate_salt();
        $data['user_pass'] = generate_password($user_pass, $data['salt']);
        if (false === MoocUser::where('user_token', $user_token)->update($data)) {
            return fail(26004, '修改密码失败', 1);
        } else {
            return ok('', 26102, '修改密码成功', 1);
        }
    }

    /**
     * 获取用户信息  他的个人中心
     * @param id 用户id
     * @param center_token 场馆token 后台获取用户信息时需要传
     * @param user_token 获取学生信息时需传，获取老师信息不需要
     */
    public function read(){
        $id = input('param.id',19,'intval');
        $type = input('param.type',2,'intval');

        $centerModel = new MoocCenter();
        $userModel = new MoocUser();
        if($type == 1){
            //后台获取
            $center_token = input('param.center_token','','trim');
            $res = checkCenterToken($center_token);
            if (true !== $res) {
                return $res;
            }
            $center_id = $centerModel->where(['center_token'=>$center_token])->value('id');

            $where = [];
            if($center_id == null){
                return $this->fail(11001,'场馆不存在');
            }
            if($center_id != 1){
                $where['center_id'] = $center_id;
            }

            $user_info = $userModel->where(['id'=>$id])->where($where)->find();
            return $this->ok($user_info,11111,'获取用户信息成功');

        }else if($type == 2){
            //前台获取
            $user_info = $userModel
                ->alias('u')
                ->join('__FOLLOW__ f','f.user_id = u.id','left')
                ->where(['u.id'=>$id,'u.status'=>1])
                ->field('u.id,u.sex,u.area,u.nick_name,u.teacher_title,u.department,u.user_login,u.avatar,u.type,count(f.id) as Fans_num')
                ->group('u.id')
                ->find();
            $user_info['follow_num'] = (new Follow())->where(['follow_id'=>$id])->count(1);

            if($user_info['type'] == 2 ){
                //获取老师
                //获取老师下的课程
                $courseModel = new \app\v1\model\Course();
                $classes =  $courseModel
                    ->alias('c')
                    ->join('__COURSE_RELA__ cr','cr.course_id = c.id')
                    ->join('__BAOMING__ b','b.course_id = c.id','left')
                    ->where(['cr.other_id'=>$id,'cr.type'=>3])
                    ->field('c.course_title,c.create_time,c.start_time,c.end_time,c.open_status,count(b.id) as baoming_num')
                    ->group('c.id')
                    ->select();
                return $this->ok(['user_info'=>$user_info,'classes'=>$classes],11112,'获取老师信息成功');
            }else if($user_info['type']== 1){
                //获取用户
                //获取用户下的课程
                $course = (new Baoming())
                    ->alias('b')
                    ->join('__COURSE__ c', 'c.id=b.course_id')
                    ->join('__SCHEDULE__ sd', 'sd.user_id=b.user_id and sd.course_id = b.course_id', 'left')
                    ->where(['b.user_id' => $id])
                    ->order(['sd.update_time'=>'desc'])
                    ->field('c.course_title,c.cover_img,c.start_time,c.end_time')
                    ->select();
                return $this->ok(['user_info'=>$user_info,'course'=>$course],11112,'获取用户信息成功');
            }

        }

    }

    /**
     * 修改个人信息
     * @param nick_name 昵称
     * @param id 用户ID
     * @param avatar 头像
     * @param teacher_title 职称
     * @param department 单位
     * @param user_pass 密码【可选】
     * @return array|bool
     */
    public function edit()
    {
        //        //测试代码
//        $_GET['user_token'] = '6cc6c00edab41244b13e3f71acab82439ae99ada';
////        $_GET['user_login'] = 'test123';
//        $_GET['user_pass'] = '123456454';
////        $_GET['center_id'] = 14;
//        $_GET['timestamp'] = time();
//        $_GET['nick_name'] = 'dsa-dssd';
//        $_GET['avatar'] = 'dsads sd';
//        $_GET['teacher_title'] = '教授';
//        $_GET['department'] = '天津大学';
//        $salt = MoocUser::where('user_token', $_GET['user_token'])->value('salt');
//        $_GET['sign'] = encrypt_key(['v1/user/edit', $_GET['timestamp'], $_GET['user_token'], $salt], '');
        //令牌校验
//        $user_token = input('param.user_token', '');
//        $tokenRes = checkUserToken($user_token);
//        if (true !== $tokenRes) {
//            return $tokenRes;
//        }

        $user_token = $this->request->param('user_token','');
        $param = $this->request->param();
//        return $param;
        $centerModel = new MoocCenter();
        $userModel = new MoocUser();
        if ($user_token) {
            //前台修改用户信息  仅允许修改昵称，职称，头像，单位
            $res = checkUserToken($user_token);
            if (true !== $res) {
                return $res;
            }
            $user_type = $userModel->where(['user_token'=>$user_token])->value('type');
            if($user_type == 1){
                //学生
                $allowdField = ['nick_name','sex', 'avatar','area','email','mobile'];
            }else{
                //老师
                $allowdField = ['nick_name','sex', 'avatar','area', 'email','mobile','teacher_title', 'department','profile'];
            }
            $where['user_token'] = $user_token;
        } else {
            //后台修改用户信息
            $center_token = $this->request->param('center_token');
            $res = checkCenterToken($center_token);
            if (true !== $res) {
                return $res;
            }
            $id = $this->request->param('id');
            $where['id'] = $id;
            $allowdField = ['nick_name', 'avatar', 'teacher_title', 'department','status','user_login','user_pass','profile'];

            //验证该id用户是否属于此文化馆
            $center_id = $centerModel->where(['center_token'=>$center_token])->value('id');
            $user_info = $userModel->where(['id'=>$id,'center_id'=>$center_id])->find();
            if($user_info == null){
                return $this->fail(20001,'该用户不属于此文化馆,没有权限修改');
            }

            //用户名校验
            $user_login = $this->request->param('user_login','','trim');
            if(!empty($user_login)){
                $userRes = checkUserLogin($user_login,$center_id);
                if (true !== $userRes) {
                    return $userRes;
                }
            }else{
                return $this->fail(23003,'用户名不能为空');
            }

            //密码校验
            $user_pass = $this->request->param('user_pass', '', 'trim');
            if (strlen($user_pass) > 0) {
                //密码校验
                $passRes = checkUserPass($user_pass);
                if (true !== $passRes) {
                    return $passRes;
                }
                $param['salt'] = generate_salt();
                $param['user_pass'] = generate_password($user_pass, $param['salt']);
            } else {
                unset($param['salt']);
                unset($param['user_pass']);
            }

        }

        //入参校验
        $userValidate = new UserValidate();
        if (!$userValidate->scene('edit_info')->check($param)) {
            return fail(26005, $userValidate->getError());
        }

//        $user_login = $this->request->param('param.user_login');
//        $userRes = checkUserLogin($user_login,$center_id);
//        if (true !== $userRes) {
//            return $userRes;
//        }

//        //密码校验
//        $user_pass = $this->request('param.user_pass', '', 'trim');
//        if (strlen($user_pass) > 0) {
//            //密码校验
//            $passRes = checkUserPass($user_pass);
//            if (true !== $passRes) {
//                return $passRes;
//            }
//            $param['salt'] = generate_salt();
//            $param['user_pass'] = generate_password($user_pass, $param['salt']);
//        } else {
//            unset($param['salt']);
//            unset($param['user_pass']);
//        }
        //数据提交
        $userModel = new MoocUser();
        if (false === $userModel->allowField($allowdField)->isUpdate(true)->save($param, $where)) {
            return fail(26006, '编辑失败', 1);
        } else {
            return ok('', 26105, '编辑个人信息成功', 1);
        }
    }

    /**
     * @param id 用户ID
     * @param ids 用户ID数组
     * @param status 状态 0 禁用 1 启用
     * @return array|bool
     */
    public function updateStatus()
    {
        $id = $this->request->param('id', 0, 'intval');
        $ids = $this->request->param('ids/a', []);

        //令牌校验
        $center_token = $this->request->param('center_token');
        $res = checkCenterToken($center_token);
        if ($res !== true) {
            return $res;
        };

        //状态校验
        $status = $this->request->param('status', 0, 'intval');
        if ($status != 0 && $status != 1) {
            return fail(26008, '状态异常');
        }

        //条件
        $where['type'] = ['in', [1, 2]];
        if ($id != 0) {
            $where['id'] = $id;
        } elseif (!empty($ids)) {
            $where['id'] = ['in', $ids];
        } else {
            return fail(26007, '参数不能为空');
        }

        //提交修改
        if (false === MoocUser::update(['status' => $status], $where)) {
            return fail(26009, '修改失败');
        } else {
            return ok('', 26106, '修改成功');
        }
    }
}