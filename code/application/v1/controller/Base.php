<?php

namespace app\v1\controller;

use think\Request;

class Base extends Core
{
    public $center_id;      //文化馆ID
    public $token;          //请求token
    public $request;        //请求
    public $user_type;      //用户类型：0，游客；10，user（学生）；20，老师；30，文化馆；90，超星管理员；
    public $user;
    protected $whiteActionList = [];

    function __construct()
    {
        parent::__construct();
        $this->request = Request::instance();
        $action = $this->request->action();
        $this->token = input('param.token', '');
        if (!in_array($action, $this->whiteActionList)) {
            $user = $this->getUserInfo($this->token, true);
            if (isset($user['status'])) return $user;
            $this->center_id = $user['center_id'];
            $this->user_type = $user['type'];
            $this->user = $user;
        }
    }

    //获取省市列表服务
	function getCityList()
	{
		$Db            = new \think\Db;
		$res = array();
		$result = $Db::query("select * from city_info");
		foreach ($result as $key => $value) {

			if ($value['city'] === $value['district']) {
				array_push($res, (array )$value);
			}
		}
		$temp_arr = [];
		foreach ($res as $key => $value) {
			$temp_arr[$value['province']][] = $value;
		}
		$rt = array();
		$i = 0;
		foreach ($temp_arr as $key => $item) {
			$id = end($item);
			$rt[$i]['id'] = $id['city_id'];
			$rt[$i]['name'] = $key;
			$rt[$i]['pid'] = $id['city_id'];
			$j = 0;
			$rt_citys = array();
			foreach ($item as $k => $v) {
				if ($item[$j]['city'] != next($item)['city']) {
					array_push($rt_citys, array(
						"id" => $v['city_id'],
						"name" => $v['city'],
						"pid" => $v['city_id']));
				}
				$rt[$i]['cities'] = $rt_citys;
				$j++;
			}
			$i++;
		}

		return ok(array_values($rt), 21123, '获取省市列表成功');
	}
}
