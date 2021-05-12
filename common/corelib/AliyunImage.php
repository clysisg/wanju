<?php
namespace app\common\corelib;
use \yii;
/* 
// 上传图片到阿里云服务器 
// 示例
$image = 'a1.png';						        //图片文件路径
// $image = file_get_contents('base64.txt');    //base64字符串(保留data:image/jpg;base64,标志)
$filename = 'sock_test_b'.mt_rand(0,1000);	    //自定义图片名称
$cls = new aliyun_image();
$cls->uploadImg($image,$filename);			    //上传图片
echo $cls->filename;						    //保存的图片名称，用于访问，保证唯一性，相同名称的图片不会覆盖
var_dump($cls->uploadResult());				    //上传结果
var_dump( $cls->getResponse()); 			    //上传响应
var_dump($cls->result());				        //上传结果
 */

class AliyunImage
{
    private $OSSAccessKeyId = '';
	private $seckey = '';
	private $bucket = '';
	private $remote_url = '';
    private $expiration = '2030-12-31T12:00:00.000Z';	// 授权过期时间
	private $remote_server = 'oss-cn-qingdao.aliyuncs.com';

	private $policy;
	private $sign;
	public  $filename;
	public  $imgDomain;

	public function __construct()
	{
        //加载配置文件，其他系统使用可修改此项
        $oss_config_image = \yii::$app->params['image']['oss'];

        $this->OSSAccessKeyId = $oss_config_image['keyId'];
        $this->seckey = $oss_config_image['keySecret'];
        $this->bucket = $oss_config_image['bucket'];
        $this->remote_url = '/'.$this->bucket;
        $this->imgDomain = $oss_config_image['domain'];
	}

	/* 上传图片入口，
	 * $image 图片文件路径或base64字符串(保留data:image/jpg;base64,标志) 
	 * $back_filename 阿里图片服务器保存的图片名称
	 * 访问地址为：http://img.weyee.com/图片名称
	 * 访问规则：http://docs.aliyun.com/?spm=5176.383663.9.3.8Ftign#/pub/oss/oss-img-api/image-processing&resize-w-or-h
	 */
	public function uploadImg($image,$back_filename)
	{
		$this->filename = $back_filename;
		$this->getSign();
		$rawString = $this->getRawString($image);
		$this->post($rawString,$this->remote_server);
	}	
	
	//签名
	protected function getSign()
	{
		$this->sign = base64_encode( hash_hmac( 'sha1',$this->getPolicy(),$this->seckey, true ) );
		return $this->sign;
	}
	
	//组装参数
	protected function getPolicy()
	{
		$this->policy = base64_encode( stripslashes( json_encode( $this->getOptions() ) ) );
		return $this->policy;
	}
	
	//组装参数
	protected function getOptions()
	{
		$options = array();
		$options['expiration'] = $this->expiration; /// 授权过期时间
		$conditions = array();
		array_push($conditions, array('bucket'=>$this->bucket));
		$content_length_range = array();
		array_push($content_length_range, 'content-length-range');
		array_push($content_length_range, 0);
		array_push($content_length_range, 104857600);
		array_push($conditions, $content_length_range);
		$options['conditions'] = $conditions;
		
		$this->options = $options;		
		return $this->options;
	}
	
	//要发送的参数
	protected function getPostArr()
	{
		$post_arr = array(
				"OSSAccessKeyId"	=>	$this->OSSAccessKeyId,	//ID号
				"policy"			=>	$this->policy,
				"Signature"			=>	$this->sign,
				"key"				=>	$this->filename,	//保存的文件名
			);
		return $post_arr;
	}
	
	//获取图片信息，返回的图片信息binary_content是二进制格式的数据
	public function getImageInfo($image)
	{
		$info = array('binary_content' => '','image_type' => '');
		if(is_string($image) && $image!='')
		{
			$pre50 = substr($image,0,50);	
			if(preg_match('/^data:\s*(image\/\w+);base64,/',$pre50,$matches))
			{	//base64格式
				$info = $this->base64_image($image);
			}
			else
			{	//文件格式
				$info = $this->file_image($image);
			}
		}
		
		return $info;
	}
	
	//处理base64字符串格式的图片
	public function base64_image($image)
	{
		$info = array('binary_content' => '','image_type' => '','from'=>'');
		$pre50 = substr($image,0,50);	//例:data:image/jpg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDA
		if(preg_match('/^(data:\s*image\/(\w+);base64,)/',$pre50,$matches))
		{
			$info['binary_content'] = base64_decode(str_replace($matches[1], '', $image));
			$info['image_type'] = isset($matches[2])?$matches[2]:'';
			$info['from'] = 'base64';
		}
		return $info;
	}
	
	//处理图片文件
	public function file_image($image)
	{
		$info = array('binary_content' => '','image_type' => '','from'=>'');
		if(!file_exists($image)) return $info;
		$size = getimagesize($image); 
		$info['binary_content'] = implode("", file($image));
		$info['image_type'] = isset($size['mime'])?$size['mime']:'';
		$info['from'] = 'file';
		return $info;
	}
	
	//获取HTTP发送的原始字符串
	protected function getRawString($image)
	{
		//参数分隔符
		$boundary = "---------------------------".substr(md5((double)microtime()*1000000),0,14);
		$header = "POST " . $this->remote_url . " HTTP/1.1\r\n";
		$header .= "Host: ".$this->remote_server."\r\n";
		$header .= "Content-type: multipart/form-data; boundary=$boundary\r\n";
		
		$data  = '';
		$data .="--$boundary\r\n";
		$post_arr = $this->getPostArr();
		
		foreach((array)$post_arr as $key => $value){			
			$data .= "Content-Disposition: form-data; name=\"".$key."\"\r\n";
			$data .= "\r\n".$value."\r\n";
			$data .= "--$boundary\r\n";
		}

		$image_info = $this->getImageInfo($image);
		$content_type = $image_info['image_type'];
		$content_file = $image_info['binary_content'];
		
		//保存的文件名
		$data .="Content-Disposition: form-data; name=\"file\"; filename=\"".$this->filename."\"\r\n";
		//图片类型
		$data .= "Content-Type: $content_type\r\n\r\n";
		//图片内容，二进制数据
		$data .= "".$content_file."\r\n";
		$data .="--$boundary--\r\n";
		$header .= "Content-Length: " . strlen($data) . "\r\n\r\n";
		
		$this->header = $header;
		$this->data = $data;
		
		$rawString = $header.$data;
		return $rawString;
	}
	
	//发送图片
	public function post($rawString,$url,$port=80)
	{
		$fp = fsockopen($url,$port);
		fputs($fp, $rawString);		
		$this->response = fread($fp, 1024);		
		fclose($fp);
	}
	
	//获取返回的响应
	public function getResponse()
	{
		return $this->response;
	}
	
	//上传结果
	public function uploadResult()
	{
		$res = $this->getResponse();
		$pattern = '/^HTTP\/1\.1 20[014]/';
		if(preg_match($pattern,$res,$matches))
		{
			return true;
		}else{
			return false;
		}		
	}

    //上传结果
    public function result()
    {
        $result = $this->uploadResult();
        $filename = $this->filename;
        $imageUrl = $this->imgDomain . '/' .$this->filename;
        $msg = $this->response;
        return ['result'=>$result,'filename'=>$filename,'imageUrl'=>$imageUrl,'msg'=>$msg];
    }

}