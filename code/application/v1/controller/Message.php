<?php
/**
 * Created by James.
 * User: chen
 * Date: 2018/8/23
 * Time: 15:57
 */

namespace app\v1\controller;
class Message extends Base
{
	//发送消息服务
	public function send()
	{
		$sender=input('param.sender', 1);
		$receiver=input('param.receiver', 1);
		$content=input('param.content', 1,"trim");
		$Db            = new \think\Db;
		$ret=$Db::query("INSERT INTO message SET sender={$sender},receiver={$receiver},content='{$content}',datetime=".time());
		if ($ret)
		{

			return $this->ok('', 200, '发送成功', 1);
		}
	}

	//获取消息服务
	public function receive()
	{
		$result=array();
		$Db            = new \think\Db;
		foreach ($Db::query("select * from message") as $k=>$v)
		{
			$result[$k]['message_id']=$v['id'];
			$result[$k]['sender']=$Db::query("select * from mooc_user where id=".$v['sender']);
			$result[$k]['content']=$v['content'];
			$result[$k]['datetime']=$v['datetime'];
			$result[$k]['status']=$v['status'];

		}
		return $this->ok($result, 200, '获取消息成功', 1);
	}
}