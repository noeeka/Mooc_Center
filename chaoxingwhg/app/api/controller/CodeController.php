<?php
namespace app\api\controller;
use code\Code as MobileCode;
use sms\AliyunSms;
use think\Config;

class CodeController extends Base {
	/**
	 * 发送短信验证码
	 * @return array
	 */
	public function mobile() {
		$mobile = input('param.mobile'); //手机号
		$type = input('param.type'); //手机号
        
		//验证mobile
		if (!isMobile($mobile)) {
			return $this->output_error(12002, '手机号不能为空');
		}
		//验证type是否非法
		$type_config = Config::get('verify_code.type');

		if (!in_array($type, $type_config)) {
			return $this->output_error(12003, '验证码类型错误');
		}

		$code = MobileCode::get($mobile, $type);
		//1. 验证码已经存在
		if ($code) {
			//检查是否在发送间隔期内
			if (MobileCode::check_expire_time($mobile, $type)) {
				//在间隔期
				return $this->output_error(12004, '验证码已发送，请勿重复请求');
			} else {
				//不在间隔期，发送短信
				if ($this->send_SMS($mobile, $code, $type)) {
					//添加间隔计时器
					MobileCode::reset_expire_timer($mobile, $type);
					return $this->output_success(12101, ['code' => $code], '验证码发送成功');
				} else {
					return $this->output_error(12001, '验证码发送失败');
				}
			};

		}
		//2. 验证码不存在，发送短信
		else {
			$code = MobileCode::create($mobile, $type);
			if ($this->send_SMS($mobile, $code['code'], $type)) {
				//添加间隔计时器
				MobileCode::reset_expire_timer($mobile, $type);
				return $this->output_success(12101, ['code' => $code], '验证码发送成功');
			} else {
				return $this->output_error(12001, '验证码发送失败');
			}
		}
	}

	function check() {
		$mobile = input('param.mobile');
		$type = input('param.type');
		$code = input('param.code');
		$res = MobileCode::check($mobile, $code, $type);
		if ($res['status'] == 1) {
			return $this->output_success(12102, ['code' => $code], '验证码验证成功');
		} else {
			return $res;
		}
	}

	/**
	 * 发送短信
	 * @param $mobile
	 * @param $code
	 * @return bool
	 */
	private function send_SMS($mobile, $code, $type) {
		return AliyunSms::sendCode($mobile, $code, $type);
	}
}
