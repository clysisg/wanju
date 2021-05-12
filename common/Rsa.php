<?php
namespace app\common;
/**
 * rsa 加、解密类
 */
class Rsa {
	
	//私有key
	private $privateKey='';
	//公有key
	private $publicKey='';
	
	/**
	 * 生成密钥
	 * @param $keylen int 密钥长度
	 * @return array 生成的密钥
	 */
	public function createKey($keylen=1024){
		$opt=array(
			"digest_alg" => "sha512",
		    "private_key_bits" => $keylen,
		    "private_key_type" => OPENSSL_KEYTYPE_RSA,
		);
		$key=openssl_pkey_new($opt);
		openssl_pkey_export($key, $privKey);
		$this->privateKey=$privKey;
		$pub=openssl_pkey_get_details($key);
		$this->publicKey=$pub['key'];
		
		return array('privateKey'=>$this->privateKey, 'publicKey'=>$this->publicKey);
	}
	
	/**
	 * 设置私有密钥
	 * @param $key string 密钥
	 */
	public function setPrivateKey($key){
		$this->privateKey=base64_decode($key);
	}
	
	/**
	 * 设置公钥
	 * @param $key 公钥
	 */
	public function setPublicKey($key){
		$this->publicKey=base64_decode($key);
	}
	
	/**
	 * 用公钥加密数据
	 * @param $data string 需要加密的数据
	 * @return string 加密后的base64编码数据
	 */
	public function encrypt($data){
		openssl_public_encrypt($data, $encrypted, $this->publicKey);
		return base64_encode($encrypted);
	}
	
	/**
	 * 用私钥解密数据
	 * @param $data string 需要解密的数据
	 * @return string 解密后的数据
	 */
	public function decrypt($data){
		openssl_private_decrypt(base64_decode($data), $decrypted, $this->privateKey);
		return $decrypted;
	}
}
