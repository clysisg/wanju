<?php
namespace app\common;

/**
 * http请求类
 */
class HttpCurl {
	
	/**
	 * 发送http请求
	 * @param  string $url    http地址
	 * @param  array $params 提交参数
	 * @return string         http返回的结果
	 */
	public static function get($url, $params = null, $header = null, $u = null) {
        $ssl = substr($url, 0, 8) == "https://" ? TRUE : FALSE;
        $ch = curl_init();
        $opt = array(
            CURLOPT_URL             => $url,
            CURLOPT_POST            => 1,
            CURLOPT_HEADER          => 0,
            // CURLOPT_BINARYTRANSFER  => 1,
            CURLOPT_POSTFIELDS      => $params,
            CURLOPT_RETURNTRANSFER  => 1,
            CURLOPT_TIMEOUT         => 10,
            CURLOPT_HTTPHEADER      => $header ? $header : [],
            CURLOPT_USERPWD         => $u ? $u : '',
        );
        if ($ssl)
        {
            $opt[CURLOPT_SSL_VERIFYHOST] = 2;
            $opt[CURLOPT_SSL_VERIFYPEER] = false;
        }
        curl_setopt_array($ch, $opt);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}