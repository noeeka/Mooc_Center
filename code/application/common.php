<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
function encrypt_key($data, $key)
{
	sort($data, SORT_STRING);
	return md5(implode('', $data) . $key);
}

function compare_key($data, $key, $sign)
{
	return encrypt_key($data, $key) == $sign;
}

function generate_token($extra_str = '')
{
	return sha1(uniqid() . $extra_str);
}

function generate_salt()
{
	return getRandomString(5);
}

function generate_password($password, $salt)
{
	return password_hash($password . $salt, PASSWORD_DEFAULT);
}

function compare_password($password, $salt, $password_hash)
{
	return password_verify($password . $salt, $password_hash);
}

function getRandomString($len, $chars = NULL)
{
	if (is_null($chars))
	{
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	}
	mt_srand(10000000 * (double)microtime());
	for ($i = 0, $str = '', $lc = strlen($chars) - 1; $i < $len; $i++)
	{
		$str .= $chars[mt_rand(0, $lc)];
	}
	return $str;
}

function checkUserLogin($user_login, $center_id)
{
	if (empty($user_login))
	{
		return fail(25008, '用户名不能为空', 1);
	}
	if (preg_match('/^[a-zA-Z]\w{5,19}$/', $user_login) == 0)
	{
		return fail(25009, '用户名必须由字母开头，6到20位的字母,数字,下划线组成', 1);
	}
	$type = $center_id == 1 ? 3 : 2;
	$num  = db('mooc_user')->where(['user_login' => $user_login, 'center_id' => $center_id, 'type' => $type])->count(1);
	if ($num > 0)
	{
		return fail(25016, '用户已存在', 1);
	}
	return TRUE;
}

function checkUserPass($user_pass)
{
	if (empty($user_pass))
	{
		return fail(25010, '密码不能为空', 1);
	}
	if (preg_match('/^\w{6,15}$/', $user_pass) == 0)
	{
		return fail(25011, '密码必须由6到15位的字母,数字,下划线组成', 1);
	}
	return TRUE;
}

function checkCenterToken($center_token)
{
	if (empty($center_token))
	{
		return fail(25012, '令牌不能为空');
	}
	$center = db('mooc_center')->where('center_token', $center_token)->find();
	if ($center == NULL)
	{
		return fail(25013, '令牌无效，场馆不存在');
	}
	$tokenRes = checkToken($center_token, $center['salt']);
	if (TRUE !== $tokenRes)
	{
		return $tokenRes;
	}
	$max_time = $center['access_time'] + config('mooc_center_expire_time');
	if (time() > $max_time)
	{
		return fail(25014, '令牌过期，请重新登陆');
	}
	if ($center['status'] == 0)
	{
		return fail(25015, '场馆被禁用');
	}
	return TRUE;
}

function checkUserToken($user_token)
{
	if (strlen($user_token) == 0)
	{
		return fail(10000, '令牌不能为空', 1);
	}
	$user = db('mooc_user')->where('user_token', $user_token)->find();
	if ($user == NULL)
	{
		return fail(10001, '令牌无效，用户不存在', 1);
	}
	$tokenRes = checkToken($user_token, $user['salt']);
	if (TRUE !== $tokenRes)
	{
		return $tokenRes;
	}
	$expire_time = $user['access_time'] + config('mooc_user_expire_time');
	if ($expire_time < time())
	{
		return fail(10002, '令牌过期，请重新登陆', 1);
	}
	if ($user['status'] == 0)
	{
		return fail(10003, '用户被禁用', 1);
	}
	return TRUE;
}

function checkToken($token, $salt)
{
	$timestamp  = input('param.timestamp', 0, 'intval');
	$sign       = input('param.sign', '');
	$request    = \think\Request::instance();
	$module     = $request->module();
	$controller = str_replace('_', '', $request->controller());
	$action     = $request->action();
	$url        = strtolower($module . '/' . $controller . '/' . $action);
	if ($timestamp == 0)
	{
		return fail(10006, '时间戳不能为空', 1);
	}
	$min = time() - config('timestamp_limit');
	$max = time() + config('timestamp_limit');
	if ($min > $timestamp || $max < $timestamp)
	{
		return fail(10007, '时间戳异常,当前服务器时间为:' . time(), 1);
	}
	if ($sign == '')
	{
		return fail(10008, '签名不能为空', 1);
	}
//    var_dump([$token, $url, $timestamp, $salt]);
//    var_dump($sign);die;
	if (compare_key([$token, $url, $timestamp, $salt], '', $sign))
	{
		return TRUE;
	}
	else
	{
		return fail(10005, '数据校验失败', 1);
	}
}

function ok($data, $code, $msg, $msg_prior = 0)
{
	$message = config('message');
	$data    = array(
		'status' => 1,
		'code' => $code,
		'data' => $data,
	);
	if ($msg_prior === 1)
	{
		$data['msg'] = $msg;
	}
	else if (array_key_exists($code, $message))
	{
		$data['msg'] = $message[$code];
	}
	else
	{
		$data['msg'] = $msg;
	}

	return $data;
}

function fail($code, $msg, $msg_prior = 0)
{
	$message = config('message');
	$data    = array(
		'status' => 0,
		'code' => $code,
		'data' => [],
	);
	if ($msg_prior === 1)
	{
		$data['msg'] = $msg;
	}
	else if (array_key_exists($code, $message))
	{
		$data['msg'] = $message[$code];
	}
	else
	{
		$data['msg'] = $msg;
	}

	return $data;
}

/**
 * 获取图片预览链接
 * @param string $file 文件路径，相对于upload
 * @param string $style 图片样式,支持各大云存储
 * @return string
 */
function get_image_url($file)
{
	if (empty($file))
	{
		return '';
	}
	elseif (strpos($file, "http") === 0)
	{
		return $file;
	}
	elseif (strpos($file, "/") === 0)
	{
		return $file;
	}
	else
	{
		return config('upload_server') . $file;
	}
}

function verify()
{
	$center_token = input('param.center_token');
	$user_token   = input('param.user_token');

	if ( ! empty($center_token))
	{
		//验证签名 token
		$cToken = checkCenterToken($center_token);
		if ($cToken !== TRUE)
		{
			return $cToken;
		}
		$center_id = db('mooc_center')->where(['center_token' => $center_token])->value('id');
		return ok(['user_id' => $center_id, 'center_id' => $center_id, 'type' => 1], 10101, '成功');
	}

	if ( ! empty($user_token))
	{
		//验证签名 token
		$uToken = checkUserToken($user_token);
		if ($uToken !== TRUE)
		{
			return $uToken;
		}
		$user_info = db('mooc_user')->where(['user_token' => $user_token])->find();
//        if ($user_info['type'] == 1) {
//            return fail(12001, '没有操作权限(当前身份不是老师)');
//        }
		return ok(['user_id' => $user_info['id'], 'center_id' => $user_info['center_id'], 'type' => 2], 10101, '成功');
	}

	if (empty($center_token) && empty($user_token))
	{
		return fail(12000, 'token不能为空');
	}
}

function numToWord($num)
{
	$chiNum = array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九');
	$chiUni = array('', '十', '百', '千', '万', '亿', '十', '百', '千');

	$chiStr = '';

	$num_str = (string)$num;

	$count     = strlen($num_str);
	$last_flag = TRUE; //上一个 是否为0
	$zero_flag = TRUE; //是否第一个
	$temp_num  = NULL; //临时数字

	$chiStr = '';//拼接结果
	if ($count == 2)
	{//两位数
		$temp_num = $num_str[0];
		$chiStr   = $temp_num == 1 ? $chiUni[1] : $chiNum[$temp_num] . $chiUni[1];
		$temp_num = $num_str[1];
		$chiStr   .= $temp_num == 0 ? '' : $chiNum[$temp_num];
	}
	else if ($count > 2)
	{
		$index = 0;
		for ($i = $count - 1; $i >= 0; $i--)
		{
			$temp_num = $num_str[$i];
			if ($temp_num == 0)
			{
				if ( ! $zero_flag && ! $last_flag)
				{
					$chiStr    = $chiNum[$temp_num] . $chiStr;
					$last_flag = TRUE;
				}
			}
			else
			{
				$chiStr = $chiNum[$temp_num] . $chiUni[$index % 9] . $chiStr;

				$zero_flag = FALSE;
				$last_flag = FALSE;
			}
			$index++;
		}
	}
	else
	{
		$chiStr = $chiNum[$num_str[0]];
	}
	return $chiStr;
}

/*
 * 根据时间获取时间段服务
 * @return array(1天是24个小时段，7天是7段，30天是按3天为一段分为10段，90天是按照9天为一段分为十段)
 * @param $num= 1 近24小时  2 近一周  3 近一个月   4 近三个月
 */
function getDateTimeArray($num)
{
	$result = array();
	if ($num == 1)
	{
		$now   = time();
		$start = strtotime('-1 days');
		for ($i = $start; $i <= $now; $i += 7200)  //3600秒是按每小时生成一条，如果按天或者月份换算成秒即可
		{
			$date     = date('Y-m-d H', $i);
			$mdate    = date('m-d H', $i);
			$result[] = strtotime($date . ":00");
		}
	}
	elseif ($num == 2)
	{
		$now   = time();
		$start = strtotime('-7 days');
		for ($i = $start; $i <= $now; $i += 3600 * 24)  //3600秒是按每小时生成一条，如果按天或者月份换算成秒即可
		{
			$date     = date('Y-m-d H', $i);
			$mdate    = date('m-d H', $i);
			$result[] = strtotime($date . ":00");
		}

	}
	elseif ($num == 3)
	{
		$now   = time();
		$start = strtotime('-30 days');
		for ($i = $start; $i <= $now; $i += 3600 * 24 * 3)  //3600秒是按每小时生成一条，如果按天或者月份换算成秒即可
		{
			$date     = date('Y-m-d H', $i);
			$mdate    = date('m-d H', $i);
			$result[] = strtotime($date . ":00");
		}

	}
	elseif ($num == 4)
	{
		$now   = time();
		$start = strtotime('-90 days');
		for ($i = $start; $i <= $now; $i += 3600 * 24 * 9)  //3600秒是按每小时生成一条，如果按天或者月份换算成秒即可
		{
			$date     = date('Y-m-d H', $i);
			$mdate    = date('m-d H', $i);
			$result[] = strtotime($date . ":00");
		}

	}
	return $result;

}

//数组降维操作
function multiToSingle($arr, $delimiter = '->', $key = ' ')
{
	$resultAry = array();
	if ( ! (is_array($arr) && count($arr) > 0))
	{
		return FALSE;
	}
	foreach ($arr AS $k => $val)
	{
		$newKey = trim($key . $k . $delimiter);
		if (is_array($val) && count($val) > 0)
		{
			$resultAry = array_merge($resultAry, multiToSingle($val, $delimiter, $newKey));
		}
		else
		{
			$resultAry[] = $newKey . $val;
		}
	}
	return $resultAry;
}