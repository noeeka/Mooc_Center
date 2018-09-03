<?php
namespace Sms;

require dirname(__DIR__) . '/aliyun-dysms-php-sdk/api_sdk/vendor/autoload.php';
use Aliyun\Api\Sms\Request\V20170525\QuerySendDetailsRequest;
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Core\Config;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Core\Profile\DefaultProfile;
use think\Config as thinkC;

// 加载区域结点配置
Config::load();

/**
 * Class SmsDemo
 *
 * Created on 17/10/17.
 * 短信服务API产品的DEMO程序,工程中包含了一个SmsDemo类，直接通过
 * 执行此文件即可体验语音服务产品API功能(只需要将AK替换成开通了云通信-短信服务产品功能的AK即可)
 * 备注:Demo工程编码采用UTF-8
 */
class AliyunSms {

	static $acsClient = null;

	/**
	 * 取得AcsClient
	 *
	 * @return DefaultAcsClient
	 */
	public static function getAcsClient() {
		//产品名称:云通信流量服务API产品,开发者无需替换
		$product = "Dysmsapi";

		//产品域名,开发者无需替换
		$domain = "dysmsapi.aliyuncs.com";

		// TODO 此处需要替换成开发者自己的AK (https://ak-console.aliyun.com/)
		$accessKeyId = thinkC::get('aliyun.accessKeyId'); // AccessKeyId

		$accessKeySecret = thinkC::get('aliyun.accessKeySecret'); // AccessKeySecret

		// 暂时不支持多Region
		$region = "cn-hangzhou";

		// 服务结点
		$endPointName = "cn-hangzhou";

		if (static::$acsClient == null) {

			//初始化acsClient,暂不支持region化
			$profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);

			// 增加服务结点
			DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);

			// 初始化AcsClient用于发起请求
			static::$acsClient = new DefaultAcsClient($profile);
		}
		return static::$acsClient;
	}

	public static function sendCode($mobile, $code, $type) {
		switch ($type) {
		case 'regist':
			$Template = 'SMS_117450038';
			break;
		case 'reset_psd':
			$Template = 'SMS_117450037';
			break;
		default:
			return false;
			break;
		}
		$data = ['code' => $code];
		$res = self::sendSms($mobile, $Template, $data, '超星文化');
		return $res->Code == 'OK' ? true : false;
	}

	/**
	 * 发送短信
	 * @return stdClass
	 */
	public static function sendSms($mobile, $Template = '', $data = [], $sign_name = '阿里云短信测试专用') {

		// 初始化SendSmsRequest实例用于设置发送短信的参数
		$request = new SendSmsRequest();

		// 必填，设置短信接收号码
		$request->setPhoneNumbers($mobile);

		// 必填，设置签名名称，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
		$request->setSignName($sign_name);

		// 必填，设置模板CODE，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
		$request->setTemplateCode($Template);

		// 可选，设置模板参数, 假如模板中存在变量需要替换则为必填项
		$request->setTemplateParam(json_encode($data));

		// 可选，设置流水号
		// $request->setOutId("43544656546546565656");

		// 选填，上行短信扩展码（扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段）
		// $request->setSmsUpExtendCode("1234567");

		// 发起访问请求
		$acsResponse = static::getAcsClient()->getAcsResponse($request);

		return $acsResponse;

	}

	/**
	 * 短信发送记录查询
	 * @return stdClass
	 */
	public static function querySendDetails($mobile, $date = null, $page = 1, $len = 10) {

		// 初始化QuerySendDetailsRequest实例用于设置短信查询的参数
		$request = new QuerySendDetailsRequest();

		// 必填，短信接收号码
		$request->setPhoneNumber($mobile);

		if (empty($date)) {
			$date = date('Ymd');
		}

		// 必填，短信发送日期，格式Ymd，支持近30天记录查询
		$request->setSendDate($date);

		// 必填，分页大小
		$request->setPageSize($len);

		// 必填，当前页码
		$request->setCurrentPage($page);

		// 选填，短信发送流水号
		// $request->setBizId("yourBizId");

		// 发起访问请求
		$acsResponse = static::getAcsClient()->getAcsResponse($request);

		return $acsResponse;
	}

}