<?php
namespace app\common;

use yii;

/**
 * token 处理类
 */
class Token {

	/**
	 * 生成 token
	 * @param  array $params 参数列表
	 * @return string         token
	 */
	public static function create($params) {
		unset($params['token'], $params['s']);	//token值 不参与
		$ret = yii::$app->params['token_key'];
		$ret .=  self::paramsToString($params);
		$ret .= yii::$app->params['token_key'];
		return md5($ret);
	}

	/**
	 * 将参数转成字符串
	 * @param  array $params 参数列表
	 * @return string         拼接后的结果
	 */
	public static function paramsToString($params) {
		$ret = '';
		ksort($params);
		foreach($params as $k => $v) {
			$ret .= $k . $v;
		}
		return $ret;
	}
}