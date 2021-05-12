<?php
namespace app\common;

/**
* 短信类
*/
class Sms
{

	/**
	 * 发送短信
	 * @param  string $mobile  手机号码
	 * @param  string $content 短信内容
	 * @return object          发送结果
	 */
	public static function sendSms($mobile, $content) {
		//配置
		$cfg = \yii::$app->params['sms'];

		$params = [
			'account'	=> $cfg['account'],
			'password'	=> $cfg['password'],
		];

		$pstr = '<?xml version="1.0" encoding="utf-8"?>
				<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
				  <soap:Body>
				    <PostSingle xmlns="http://www.139130.net">
				      <account>' . $cfg['account'] . '</account>
				      <password>' . $cfg['password'] . '</password>
				      <mobile>' . $mobile . '</mobile>
				      <content>' . $content . '</content>
				      <subid></subid>
				    </PostSingle>
				  </soap:Body>
				</soap:Envelope>';

		$r = \app\common\HttpCurl::get($cfg['server'], $pstr, ['Content-Type: text/xml; charset=utf-8', 'SOAPAction: http://www.139130.net/PostSingle']);
		return $r;
	}

	/**
	 * 验证手机号码格式
	 * @param  string $mobile 手机号
	 * @return bool           验证结果
	 */
	public static function checkMobile($mobile) {
		return preg_match('/^1(\d{10})$/', $mobile);
	}
}
