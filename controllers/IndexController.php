<?php
namespace app\controllers;


use app\common\corelib\AliyunImage;
use app\common\corelib\LocalUpload;

class IndexController extends BackendController
{

	public function actionIndex()
	{
        return $this->render('index');
	}

    public function actionError()
	{
        return $this->render('error');
	}
    
    public function actionUpload(){
        $data = [ 'state'=>'FAIL','data'=>[] ];
        if(isset($_FILES['upfile']['tmp_name']) && !empty($_FILES['upfile']['tmp_name'])){
            LocalUpload::go_upload($_FILES['upfile']);
            $thumbs = LocalUpload::thumbs(100,100);
            $data['state'] = 'SUCCESS';
            $data['url'] = 'http://'.\Yii::$app->params['back_domain'].'/uploads/'.LocalUpload::$filedir.'/'.LocalUpload::$filename;
            /*$tmp_name = $_FILES['upfile']['tmp_name'];
            $filename = mt_rand(10000,99999).$_FILES['upfile']['name'];
            $type     = $_FILES['upfile']['type'];
            $img_64   = 'data:'.$type.';base64,';
            $fileStr  = @file_get_contents($tmp_name);
            if($fileStr){
                $imsg_str = $img_64 . base64_encode($fileStr);
                $AliyunImage = new AliyunImage();
                $AliyunImage->uploadImg($imsg_str,$filename);
                $res = $AliyunImage->result();
                if($res['result']){
                    $data['state'] = 'SUCCESS';
                    $data['url'] = 'http://'.$res['imageUrl'];
                }
            }*/
        }
        echo json_encode($data);exit;
    }

}