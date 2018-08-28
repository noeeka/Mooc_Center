<?php

namespace app\v1\controller;

use app\v1\model\MoocCenter;
use app\v1\model\MoocUser;
use app\v1\model\Terminal;
use app\v1\validate\Account;
use app\v1\validate\MoocUser as UserValidate;

class Passport extends Core
{
    /**
     * 用户登陆
     * @return array|bool
     */
    public function user_login()
    {
        $center_id = input('param.center_id', 0, 'intval');
        $center_user_id = input('param.center_user_id', 0, 'intval');
        $nick_name = input('param.nick_name','','trim');
//        $terminal = input('param.terminal',2,'intval');  // 1 pc端  2 移动端
        //数据校验
        $result = $this->checkSign($center_id, [$center_user_id]);
        if (true === $result) {
            //验证数据正确性
            $param = $this->request->param();
            if(empty($nick_name)){
                return $this->fail(25000,'用户昵称不能为空');
            }
            $userValidate = new UserValidate();
            if ($userValidate->check($param)) {
                //登陆
                return $this->doUserlogin($center_id, $center_user_id, $param);
            } else {
                return $this->fail(25001, $userValidate->getError(), 1);
            }
        } else {
            return $result;
        }
    }

    /**
     * 密码登陆
     *
     * @param user_login 用户名
     * @param user_pass 密码
     * @param mode 模式 2 老师 3 超级管理员
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function pass_login()
    {
        $user_login = input('param.user_login', '', 'trim');
        $user_pass = input('param.user_pass', '', 'trim');
        $center_id = input('param.center_id', 0, 'intval');
        $mode = input('param.mode', 2, 'intval');
        $terminal = input('param.terminal',2,'intval');   // 1 pc端  2 移动端
        //超级管理员登陆指定场馆1 ，其他指定场馆 不能为0
        if ($mode == 3) {
            $center_id = 1;
        } else {
            if ($center_id == 0) {
                return fail(25022, '请指定场馆', 1);
            }
        }
        //普通文化馆需要验证access_key
        $user = MoocUser::where(['user_login' => $user_login, 'center_id' => $center_id, 'type' => $mode])->find();
        if ($user == null) {
            return fail(25018, '用户不存在', 1);
        }
        if ($user['status'] == 0) {
            return fail(25019, '用户被禁用', 1);
        }
        if (compare_password($user_pass, $user['salt'], $user['user_pass'])) {

            if ($mode == 2) {
                //老师
                $data['user_token'] = generate_token();
                $data['salt'] = generate_salt();
                $data['access_time'] = time();
                $moocUserModel = new MoocUser();
                if (false === $moocUserModel->where(['id' => $user['id']])->update($data)) {
                    return fail(25020, '登陆失败', 1);
                } else {
                    //登陆端数量添加
                    $this->terminal($terminal,$center_id);
                    return ok(['token' => $data['user_token'], 'salt' => $data['salt'], 'expire_time' => config('mooc_user_expire_time')], 25104, '登陆成功', 1);
                }
            } else {
                //超级管理员
                $data['center_token'] = generate_token();
                $data['access_time'] = time();
                $data['salt'] = generate_salt();
                $centerModel = new MoocCenter();
                if (false === $centerModel->where('id', 1)->update($data)) {
                    return fail(25020, '登陆失败', 1);
                } else {
                    return ok(['token' => $data['center_token'], 'salt' => $data['salt'], 'expire_time' => config('mooc_user_expire_time')], 25103, '登陆成功', 1);
                }
            }
        } else {
            return fail(25021, '密码不正确', 1);
        }
    }

    /**
     * 场馆登陆
     * @return array|bool
     */
    public function center_login()
    {
//        $_GET['center_id'] = '14';
//        $_GET['center_token'] = MoocCenter::where('id', $_GET['center_id'])->value('center_token');
//        $_GET['timestamp'] = strval(time());
        $center_id = input('param.center_id', 0, 'intval');
        $result = $this->checkSign($center_id);
        if (true === $result) {
            $status = MoocCenter::where('id', $center_id)->value('status', 0);
            if ($status == 0) {
                return $this->fail(25008, '文化馆被禁用', 1);
            }
            $centerModel = new MoocCenter();
            $data['center_token'] = generate_token();
            $data['salt'] = generate_salt();
            $data['access_time'] = time();
            if (false === $centerModel->where('id', $center_id)->update($data)) {
                return $this->fail(25006, '登陆成功', 1);
            } else {
                return $this->ok(['center_token' => $data['center_token'], 'salt' => $data['salt'], 'expire_time' => config('mooc_center_expire_time')], 25102, '登陆成功', 1);
            }
        } else {
            return $result;
        }
    }

    /**
     * 执行登陆操作
     *
     * 没有账号的生成账号，有账号的修改登陆时间和token
     *
     * @param $center_id 文化馆ID
     * @param $center_user_id 文化馆方唯一标识
     * @param $param get请求参数
     * @return array
     */
    private function doUserlogin($center_id, $center_user_id, $param)
    {
        $userModel = new MoocUser();
        $user = $userModel->where(['center_id' => $center_id, 'center_user_id' => $center_user_id])->find();
        if ($user == null) {
            $param['user_token'] = generate_token();
            $param['salt'] = generate_salt();
            $param['access_time'] = time();
            if (false === $userModel->allowField(true)->save($param)) {
                return $this->fail(25005, '登陆失败', 1);
            } else {
                Cookie('mooc_user_token', $param['user_token'], config('mooc_user_expire_time'));
                return $this->ok(['user_token' => $param['user_token'], 'salt' => $param['salt'], 'expire_time' => config('mooc_user_expire_time')], 25101, '登陆成功', 1);
            }
        } else {
            if ($user['type'] != 1) {
                return $this->fail(25007, '用户类型异常', 1);
            }
            if ($user['status'] == 0) {
                return $this->fail(25006, '账户被禁用', 1);
            }
            $data['user_token'] = generate_token();
            $data['access_time'] = time();
            if (false === $userModel->where('id', $user->id)->update($data)) {
                return $this->fail(25005, '登陆失败', 1);
            } else {
                //登陆端数量添加
                $this->terminal($param['terminal'],$center_id);
                return $this->ok(['user_token' => $data['user_token'], 'salt' => $user['salt'], 'expire_time' => config('mooc_user_expire_time')], 25101, '登陆成功', 1);
            }
        }
    }

    private function terminal($type,$center_id){
        $terminalModel = new Terminal();
        $exist_id = $terminalModel->where(['center_id'=>$center_id])->value('id');
        if($type == 1){
            //pc 端
            if($exist_id != null){
                $terminalModel->where(['id'=>$exist_id])->setInc('pc_num');
            }else{
                $terminalModel->save(['pc_num'=>1,'center_id'=>$center_id]);
            }
        }else{
            //移动端
            if($exist_id != null){
                $terminalModel->where(['id'=>$exist_id])->setInc('wx_num');
            }else{
                $terminalModel->save(['center_id'=>$center_id,'wx_num'=>1]);
            }
        }

    }
}
