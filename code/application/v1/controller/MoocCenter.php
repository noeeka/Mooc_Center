<?php

namespace app\v1\controller;

use app\v1\model\MoocCenter as MoocCenterModel;
use app\v1\validate\MoocCenter as CenterValidate;

class MoocCenter extends Base
{
    /**
     * 获取所有场馆(来源)
     * @param status 状态 -1 全部 0 不可用 1 可用
     * @return array|bool
     */
    public function index()
    {
////        测试用例
//        $_GET['center_token'] = 'afbe52d7014168fa8b70f2a6c716a88c3e06c910';
//        $_GET['timestamp'] = time();
//        $salt = 'l5Y2Q';
//        $_GET['sign'] = encrypt_key(['v1/mooccenter/index', $_GET['timestamp'], $_GET['center_token'], $salt], '');

        //身份校验
        $msg = verify();
        if ($msg['status'] == 0) {
            return $msg;
        } else {
            $center_id = $msg['data']['center_id'];
        }

        //数据获取
        $centerModel = new MoocCenterModel();
        $status = input('param.status', -1, 'intval');
        $where = ['delete_time' => 0];
        if ($center_id == 1) {
            if ($status != -1) $where['status'] = $status;
        } else {
            $where = ['id' => $center_id];
        }
        $center_list = $centerModel->where($where)->field('id,center_name')->select();
        return $this->ok($center_list, 11111, '获取来源成功', 1);
    }

    /**
     * 创建账号
     *
     * @param center_name 场馆名
     * @param address 地址
     */
    public function add()
    {
//        // 测试用例
//        $_GET['center_token'] = 'd772d5c2ca2e10a36c6dbb3acae6850538ad0704';
//        $_GET['timestamp'] = time();
//        $salt = '8FrkI';
//        $_GET['sign'] = encrypt_key(['v1/mooccenter/add', $_GET['timestamp'], $_GET['center_token'], $salt], '');
//        $_GET['center_name'] = '东丽文化馆';

        //身份校验
        $identity = verify();
        if ($identity['status'] == 0) {
            return $identity;
        }
        $identity = $identity['data'];
        if ($identity['center_id'] != 1 || $identity['type'] != 1) {
            return fail(20001, '没有操作权限', 1);
        }

        //入参校验
        $param = $this->request->param();
        $centerValidate = new CenterValidate();
        if ($centerValidate->scene('add')->check($param)) {
            $centerModel = new MoocCenterModel();
            $allowFields = ['center_name', 'address', 'access_key', 'create_time'];
            $param['access_key'] = generate_token($param['center_name']);
            $param['create_time'] = time();
            if ($centerModel->allowField($allowFields)->save($param) > 0) {
                return ok($centerModel->getLastInsID(), 20101, '成功');
            } else {
                return fail(20003, '失败');
            }
        } else {
            return fail(1000, $centerValidate->getError(), 1);
        }
    }

    /**
     * 修改帐号
     *
     * @param id 场馆ID
     * @param center_name 场馆名称
     * @param address 地址
     * @param status 状态
     */
    public function edit()
    {
//        // 测试用例
//        $_GET['center_token'] = 'd772d5c2ca2e10a36c6dbb3acae6850538ad0704';
//        $_GET['timestamp'] = time();
//        $salt = '8FrkI';
//        $_GET['sign'] = encrypt_key(['v1/mooccenter/edit', $_GET['timestamp'], $_GET['center_token'], $salt], '');
//        $_GET['id'] = '18';
//        $_GET['center_name'] = '东丽文化馆1';
//        $_GET['status'] = '0';
//        $_GET['address'] = '213232132';

        //身份校验
        $identity = verify();
        if ($identity['status'] == 0) {
            return $identity;
        }
        $identity = $identity['data'];
        if ($identity['type'] != 1) {
            return fail(20004, '不允许前台修改');
        }

        //获取入参
        $param = $this->request->param();

        //确定场馆
        if ($identity['center_id'] == 1) {
            $param['id'] = input('param.id', 0, 'intval');
            if ($param['id'] == 0) {
                return fail(20005, '超级管理员必须指定要修改的场馆');
            }
        } else {
            $param['id'] = $identity['center_id'];
        }

        //入参校验
        $centerValidate = new CenterValidate();
        if ($centerValidate->scene('edit')->check($param)) {
            $centerModel = new MoocCenterModel();
            $allowFields = ['center_name', 'address', 'status'];
//            return $param;
            if (false !== $centerModel->allowField($allowFields)->save($param, ['id' => $param['id']])) {
                return ok('', 20102, '成功');
            } else {
                return fail(20006, '失败');
            }
        } else {
            return fail(1000, $centerValidate->getError(), 1);
        }
    }

    /**
     * 获取某个场馆
     *
     * @param id 场馆ID
     *
     * @return array|bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function read()
    {
//        // 测试用例
//        $_GET['center_token'] = 'd772d5c2ca2e10a36c6dbb3acae6850538ad0704';
//        $_GET['timestamp'] = time();
//        $salt = '8FrkI';
//        $_GET['sign'] = encrypt_key(['v1/mooccenter/read', $_GET['timestamp'], $_GET['center_token'], $salt], '');
//        $_GET['id'] = '19';
        //身份校验
        $identity = verify();
        if ($identity['status'] == 0) {
            return $identity;
        }
        $identity = $identity['data'];
        if ($identity['type'] != 1) {
            return fail(20007, '不允许前台操作');
        }

        //确定场馆
        if ($identity['center_id'] == 1) {
            $id = input('param.id', 0, 'intval');
            if ($id == 0) {
                return fail(20008, '超级管理员必须指定场馆');
            }
        } else {
            $id = $identity['center_id'];
        }

        //数据获取
        $data = MoocCenterModel::where(['delete_time' => 0, 'id' => $id])->find();
        if ($data) {
            return ok($data, 20103, '获取成功');
        } else {
            return fail(20009, '获取失败');
        }
    }

    /**
     * 删除
     *
     * @param id 场馆ID
     * @param ids 场馆ID数组
     *
     * @return array|bool
     */
    public function delete()
    {
        // 测试用例
        $_GET['center_token'] = 'd772d5c2ca2e10a36c6dbb3acae6850538ad0704';
        $_GET['timestamp'] = time();
        $salt = '8FrkI';
        $_GET['sign'] = encrypt_key(['v1/mooccenter/delete', $_GET['timestamp'], $_GET['center_token'], $salt], '');
        $_GET['id'] = '19';
        //身份校验
        $identity = verify();
        if ($identity['status'] == 0) {
            return $identity;
        }
        $identity = $identity['data'];
        if ($identity['type'] != 1 || $identity['center_id'] != 1) {
            return fail(20010, '没有操作权限');
        }

        //参数获取
        $id = $this->request->param('id', 0, 'intval');
        $ids = $this->request->param('ids/a', []);

        //单个删除
        if ($id != 0) {
            $where['id'] = $id;
        } elseif (!empty($ids)) {
            $where['id'] = ['in', $ids];
        } else {
            return fail(20011, '未指定删除条件');
        }

        $centerModel = new MoocCenterModel();
        //批量删除
        if (false === $centerModel->where($where)->update(['delete_time' => time()])) {
            return fail(20012, '删除失败');
        } else {
            return ok('', 20104, '删除成功');
        }
    }
}
