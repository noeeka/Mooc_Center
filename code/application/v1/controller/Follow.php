<?php
/**
 * Created by PhpStorm.
 * User: jxbx
 * Date: 2018/8/8
 * Time: 11:13
 */

namespace app\v1\controller;

use app\v1\model\MoocUser;

class Follow extends Base {
	//添加关注
	public function followById()
	{
		$Db = new \think\Db;
		//		$_GET['user_token'] = '4800970a86649ca1ce6215864cc3df8cec0319a9';
//		$_GET['timestamp']  = time();
//		$salt               = 'Fkg7e';
//		$_GET['sign']       = encrypt_key(['v1/my/mycollect', $_GET['timestamp'], $_GET['user_token'], $salt], '');
//
//		$user_token = input('param.user_token', '', 'trim');
//
//		//令牌校验
//		$tokenRes = checkUserToken($user_token);
//		if ($tokenRes !== TRUE)
//		{
//			return $tokenRes;
//		}
		$user_token = "4800970a86649ca1ce6215864cc3df8cec0319a9";
		$userModel  = new MoocUser();
		$user       = $userModel->where(['user_token' => $user_token])->find();
		$user_id    = $user['id'];
		$follow_id  = input('param.follow_id', '1', 'trim');
		if (empty($Db::query("select * from follow where `user_id`={$user_id} and follow_id={$follow_id}")))
		{

			$follow_sql = "INSERT INTO follow SET `user_id`={$user_id},follow_id={$follow_id},create_time=" . time();
			//exit($follow_sql);
			$ret = $Db::query($follow_sql);
			if ($ret)
			{
				$Db::query("UPDATE mooc_user SET fans_num=fans_num+1 where id={$follow_id}");
				$Db::query("UPDATE mooc_user SET follow_num=follow_num+1 where id={$user_id}");
				return $this->ok('', 200, '关注成功', 1);
			}

		}
		else
		{
			print_r($this->fail('', 500, '已关注此用户', 1));
			die;
		}

	}

	//取消关注
	public function unfollowById()
	{
		$Db = new \think\Db;
		//		$_GET['user_token'] = '4800970a86649ca1ce6215864cc3df8cec0319a9';
//		$_GET['timestamp']  = time();
//		$salt               = 'Fkg7e';
//		$_GET['sign']       = encrypt_key(['v1/my/mycollect', $_GET['timestamp'], $_GET['user_token'], $salt], '');
//
//		$user_token = input('param.user_token', '', 'trim');
//
//		//令牌校验
//		$tokenRes = checkUserToken($user_token);
//		if ($tokenRes !== TRUE)
//		{
//			return $tokenRes;
//		}
		$user_token = "4800970a86649ca1ce6215864cc3df8cec0319a9";
		$userModel  = new MoocUser();
		$user       = $userModel->where(['user_token' => $user_token])->find();
		$user_id    = $user['id'];
		$follow_id  = input('param.follow_id', '1', 'trim');
		if (empty($Db::query("select * from follow where `user_id`={$user_id} and follow_id={$follow_id}")))
		{
			return $this->ok('', 500, '未关注此用户', 1);
		}
		else
		{
			$unfollow_sql = "DELETE FROM follow WHERE `user_id`={$user_id} and follow_id={$follow_id}";
			$ret          = $Db::query($unfollow_sql);
			if ($ret)
			{
				$Db::query("UPDATE mooc_user SET fans_num=fans_num-1 where id={$follow_id}");
				$Db::query("UPDATE mooc_user SET follow_num=follow_num-1 where id={$user_id}");
				return $this->ok('', 200, '取消关注成功', 1);
			}
		}

	}
}