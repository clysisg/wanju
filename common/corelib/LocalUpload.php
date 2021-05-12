<?php
/**
 * Created by PhpStorm.
 * User: jimzhou
 * Date: 2017/9/3 0003
 * Time: 17:27
 */

namespace app\common\corelib;


class LocalUpload
{
    static private $uptype = null;		//可以上传的类型
    static public $filedir 	= null;		//上传后的文件夹
    static public $uploadpath = null; 	//上传的文件路径
    static public $filesize = null;		//可以上传的文件大小
    static public $filename = null;		//成功后的文件名
    static public $files = null;		//要上传的数据
    static public $error = null; 		//错误信息
    static public $ok = null; 			//上传成功还是失败

    //设置上传配置参数
    static public function upload_config($type=null,$size=null,$uploadpath=null){
        if(empty($uploadpath)){
            self::$uploadpath=dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'];
        }else{
            self::$uploadpath=dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$uploadpath.DIRECTORY_SEPARATOR;
        }
        self::$uptype = (!empty($type)) ? $type : \Yii::$app->params['upload']['type'];
        self::$filesize=(!empty($size)) ? $size : \Yii::$app->params['upload']['size'];
    }

    //设置错误信息
    static private function set_error(){
        /*
            0——没有错误发生，文件上传成功。
            1——上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值。
            2——上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值。
            3——文件只有部分被上传。
            4——没有文件被上传。
        */
        if(self::$files['size']>self::$filesize){
            self::$error="文件大小超过了允许上传大小";
            return false;
        }
        switch(self::$files['error']){
            case 0:
                self::$error="上传成功没有错误";
                return true;
                break;
            case 1:
                self::$error="文件大小超过了ini大小";
                return false;
                break;
            case 2:
                self::$error="文件大小超过了HTML大小";
                return false;
                break;
            case 3:
                self::$error="文件只有部分被上传";
                return false;
                break;
            case 4:
                self::$error="文件没有被上传";
                return false;
                break;
            default:
                return false;
                break;
        }
        return true;

    }
    static public function go_upload($file){
        if(!$file){self::$ok=0;return false;}
        if(!self::$uptype){self::upload_config();}
        self::$files=$file;


        if(!self::set_error()){
            self::$ok=0;
            return false;
        }
        $filetype=explode('.',self::$files['name']);
        $filetype=strtolower(array_pop($filetype));
        if($filetype=='jpeg')$filetype='jpg';

        if(in_array($filetype,self::$uptype)){
            if(is_uploaded_file(self::$files['tmp_name'])){
                self::$filedir=date("Ymd");
                $finaluploadpath = self::$uploadpath.self::$filedir.DIRECTORY_SEPARATOR;
               // if(!is_dir(self::$uploadpath.self::$filedir.'/'))
                    //self::$uploadpath=self::$uploadpath.self::$filedir.'/';
                //var_dump(self::$uploadpath);exit;
                if(!is_dir($finaluploadpath)){

                    if(!mkdir($finaluploadpath,0777)){
                        self::$error="上传失败,请检查文件夹权限mkdir";
                        self::$ok=0;
                        return self::$ok;
                    }
                    if(!chmod($finaluploadpath,0777)){
                        self::$error="上传失败,请检查文件夹权限chmod";
                        self::$ok=0;
                        return self::$ok;
                    }
                }
                $rand=rand(10,99).substr(microtime(),2,6).substr(time(),4,6);
                self::$filename=$rand.".".$filetype;

                $error=move_uploaded_file(self::$files['tmp_name'],$finaluploadpath.self::$filename);
                if($error){
                    self::$error="上传成功";
                    self::$ok=1;
                }else{
                    self::$error="上传失败,请检查文件夹权限";
                    self::$ok=0;
                }

            }else{
                self::$error="不是上传文件";
                self::$ok=0;
            }
        }else{
            self::$error="上传文件类型不正确";
            self::$ok=0;
        }
        return self::$ok;
    }

    /*public static function thumbss($width=null,$height=null,$path=null){
        if(empty($width))$width=50;
        if(empty($height))$height=50;
        $width = intval($width);
        $height = intval($height);

        if(!$path){
            $path=self::$uploadpath.self::$filedir.DIRECTORY_SEPARATOR.self::$filename;
        }
        if(!file_exists($path)){
            return false;
        }
        //打开原图资源
        //获取能够使用的后缀
        $ext = explode('.',$path);
        $ext = array_pop($houzhui);
        //拼凑函数名
        $open = 'imagecreatefrom' . $ext;    //imagecreatefromgif
        $save = 'image' . $ext;          //imagegif
        //如果不清楚；echo $open,$save;exit;
        //可变函数打开原图资源
        $src_img = $open($path); //利用可变函数打开图片资源
        //imagecreatefromgif($src)
        //缩略图资源
        $dst_img = imagecreatetruecolor($this->thumb_width,$this->thumb_height);
        //背景色填充白色
        $dst_bg_color = imagecolorallocate($dst_img,255,255,255);
        imagefill($dst_img,0,0,$dst_bg_color);
        //宽高比确定宽高
        $dst_size = $this->thumb_width / $this->thumb_height;
        //获取原图数据
        $file_info = getimagesize($src);
        $src_size = $file_info[0]/$file_info[1];
        //求出缩略图宽和高
        if($src_size > $dst_size){
            //原图宽高比大于缩略图
            $width = $this->thumb_width;
            $height = round($width / $src_size);
        }else{
            $height = $this->thumb_height;
            $width = round($height * $src_size);
        }
        //求出缩略图起始位置
        $dst_x = round($this->thumb_width - $width)/2;
        $dst_y = round($this->thumb_height - $height)/2;
        //制作缩略图
        if(imagecopyresampled($dst_img,$src_img,$dst_x,$dst_y,0,0,$width,$height,$file_info[0],$file_info[1])){
            //采样成功：保存，将文件保存到对应的路径下
            $thumb_name = 'thumb_' . basename($src);
            $save($dst_img,$path . '/' . $thumb_name);
            //保存成功
            return $thumb_name;
        }else{
            //采样失败
            $this->thumb_error = '缩略图采样失败！';
            return false;
        }
    }*/

    //生成图片的缩略图，30*30,50*50,100*100
    //是否覆盖 0不覆盖 ，1覆盖
    //$path 外部指定文件
    public static function thumbs($width=null,$height=null,$fugai=false,$path=null,$point=null){

        if(empty($width))$width=50;
        if(empty($height))$height=50;
        $width = intval($width);
        $height = intval($height);

        if(!$path){
            $path=self::$uploadpath.self::$filedir.DIRECTORY_SEPARATOR.self::$filename;
        }
        if(!file_exists($path)){
            return false;
        }
        $imgSize = GetImageSize($path);
        $houzhui = explode('.',$path);
        $houzhui = array_pop($houzhui);
        $imgType = $imgSize[2];

        if(!is_array($point)){
            $point = array("x"=>0,"y"=>0,"w"=>$imgSize[0],"h"=>$imgSize[1]);
        }

        switch ($imgType)
        {
            case 1:
                $srcImg = @ImageCreateFromGIF($path);
                break;
            case 2:
                $srcImg = @ImageCreateFromJpeg($path);
                break;
            case 3:
                $srcImg = @ImageCreateFromPNG($path);
                break;
            case 6:
                $srcImg = self::ImageCreateFromBMP($path);
                break;
            default:
                ;
        }

        //缩略图片资源
        $targetImg=ImageCreateTrueColor($width,$height);
        $white = ImageColorAllocate($targetImg, 255,255,255);
        imagefill($targetImg,0,0,$white); // 从左上角开始填充背景

        //宽高比确定宽高
        $dst_size = $width / $height;
        //获取原图数据
        $src_size = $imgSize[0]/$imgSize[1];
        //求出缩略图宽和高
        if($src_size > $dst_size){
            //原图宽高比大于缩略图
            $fin_width = $width;
            $fin_height = round($fin_width / $src_size);
        }else{
            $fin_height = $height;
            $fin_width = round($fin_height * $src_size);
        }
        //求出缩略图起始位置
        $dst_x = round($width - $fin_width)/2;
        $dst_y = round($height - $fin_height)/2;




        ImageCopyResampled($targetImg,$srcImg,$dst_x,$dst_y,0,0,$fin_width,$fin_height,$imgSize[0],$imgSize[1]);//缩放
        if($fugai){
            $tag_name = '';
        }else{
            $tag_name = '_'.$width.$height.'.'.$houzhui;
        }

        switch ($imgType) {
            case 1:
                ImageGIF($targetImg,$path.$tag_name);
                break;
            case 2:
                ImageJpeg($targetImg,$path.$tag_name,100);
                break;
            case 3:
                ImagePNG($targetImg,$path.$tag_name,9);
                break;
            default:
                ImageJpeg($targetImg,$path.$tag_name,100);
                break;
                ;
        }
        ImageDestroy($srcImg);
        ImageDestroy($targetImg);


        return $houzhui;

    }
}