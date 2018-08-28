<?php

namespace app\v1\controller;

use app\v1\model\MoocUser;
use think\config;
use think\Controller;
use app\v1\model\MoocCenter;

class Core extends Controller
{
    public function index()
    {
        return $data = array(
            'code' => 404,
            'status' => 0,
            'data' => [
                'ver' => '1.0.0'
            ],
            'msg' => '错误'
        );
    }

    public function _empty()
    {
        return array(
            'code' => 404,
            'status' => 0,
            'data' => [
                'ver' => '1.0.0'
            ],
            'msg' => '错误'
        );
    }

    protected function ok($data, $code, $msg, $msg_prior = 0)
    {
        return ok($data, $code, $msg, $msg_prior);
    }

    protected function fail($code, $msg, $msg_prior = 0)
    {
        return fail($code, $msg, $msg_prior);
    }

    //表前缀添加
    protected function table($table)
    {
        return Config::get('database.prefix') . $table;
    }

    /**
     * 校验数据
     * @param $center_id 文化馆ID
     * @param array $sign_data 加签数据，center_id,timestamp除外
     * @return array|bool
     * @throws \think\exception\DbException
     */
    protected function checkSign($center_id, $sign_data = [])
    {
        $center = MoocCenter::get($center_id);
        if ($center) {
            $access_key = $center->access_key;
            $sign = input('param.sign', '');
            $timestamp = input('param.timestamp', 0, 'intval');
            $min = time() - config('timestamp_limit');
            $max = time() + config('timestamp_limit');
            if ($timestamp > $max || $timestamp < $min) {
                return $this->fail(25002, '时间戳异常', 1);
            }
            $sign_data['center_id'] = $center_id;
            $sign_data['timestamp'] = $timestamp;
            if (compare_key($sign_data, $access_key, $sign)) {
                return true;
            } else {
                return $this->fail(25003, '数据校验失败', 1);
            }
        } else {
            return $this->fail(25004, '文化馆ID异常', 1);
        }
    }

    protected function getUserInfo($token, $isVali = true)
    {
        if (strlen($token) == 0) {
            return $this->fail(10000, '令牌不能为空', 1);
        }
        $user = MoocUser::where('token', $token)->find();
        if ($user == null) {
            return $this->fail(10001, '令牌无效，用户不存在', 1);
        }
        if ($isVali) {
            $expire_time = $user['access_time'] + config('mooc_user_expire_time');
            if ($expire_time < time()) {
                return $this->fail(10002, '令牌过期，请重新登陆', 1);
            }
        }
        return $user;
    }
}
