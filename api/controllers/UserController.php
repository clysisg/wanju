<?php
namespace app\controllers;

use app\common\corelib\CacheHelper;
use app\common\corelib\LocalUpload;
use app\logic\UserLogic;
use app\common\corelib\alidayu\api_demo\SmsDemo;
use Yii;


class UserController extends BaseController
{

	public function actionLogin()
	{


        $registrationId = Yii::$app->request->post('registrationId','');
        $mobile = Yii::$app->request->post('mobile','');
        $pass = Yii::$app->request->post('pass','');
        if(!preg_match("/^1[34578]{1}\d{9}$/",$mobile)){
            $this->error('100001','请输入正确的手机号码');
        }
        $userLogic = new UserLogic();
        $res = $userLogic->login($mobile,$pass,$registrationId);
        if(isset($res['status']) && !$res['status']){
            $this->error('100002',$res['msg']);
        }
        $this->response($res);
	}

	public function actionGetVercode(){
        $mobile = Yii::$app->request->post('mobile','');
        if(!preg_match("/^1[34578]{1}\d{9}$/",$mobile)){
            $this->error('100001','请输入正确的手机号码');
        }
        $userLogic = new UserLogic();
        $res = $userLogic->getUser($mobile);
        if($res['status']){
            $this->error('100002',$res['msg']);
        }
        if($vercode = CacheHelper::get('reg_vercode_'.$mobile)){
            $this->response();
        }else{
            $vercode = $this->_generate_code(6);
            //此处需要发送短信
            $response = SmsDemo::sendSms(
                "点投科技", // 短信签名
                "SMS_109500097", // 短信模板编号
                $mobile, // 短信接收者
                Array(  // 短信模板中字段的值
                    "code"=>$vercode,
                    "product"=>"dt"
                )
            );
            //此处需要发送短信
            CacheHelper::set('reg_vercode_'.$mobile,$vercode,900);
            $this->response();
        }
    }

    public function actionGetPvercode(){
        $mobile = Yii::$app->request->post('mobile','');
        if(!preg_match("/^1[34578]{1}\d{9}$/",$mobile)){
            $this->error('100001','请输入正确的手机号码');
        }
        $userLogic = new UserLogic();
        $res = $userLogic->getUser($mobile);
        if(!$res['status']){
            $this->error('100002',$res['msg']);
        }
        if($vercode = CacheHelper::get('reg_vercode_'.$mobile)){
            $this->response();
        }else{
            $vercode = $this->_generate_code(6);
            //此处需要发送短信
            $response = SmsDemo::sendSms(
                "点投科技", // 短信签名
                "SMS_109525334", // 短信模板编号
                $mobile, // 短信接收者
                Array(  // 短信模板中字段的值
                    "code"=>$vercode,
                    "product"=>"dt"
                )
            );
            //此处需要发送短信
            CacheHelper::set('reg_vercode_'.$mobile,$vercode,900);
            $this->response();
        }
    }

    public function actionGetBindVercode(){
        $mobile = Yii::$app->request->post('mobile','');
        $uid = Yii::$app->request->post('uid','');
        if(!preg_match("/^1[34578]{1}\d{9}$/",$mobile)){
            $this->error('100001','请输入正确的手机号码');
        }
        $userLogic = new UserLogic();
        $user = $userLogic->getUserinfo($uid);
        if($user->user_name!=$mobile){
            $this->error('100001','请输入正确的手机号码');
        }
        if($vercode = CacheHelper::get('bind_vercode_'.$mobile)){
            $this->response();
        }else{
            $vercode = $this->_generate_code(6);
            //此处需要发送短信
            $response = SmsDemo::sendSms(
                "点投科技", // 短信签名
                "SMS_109490088", // 短信模板编号
                $mobile, // 短信接收者
                Array(  // 短信模板中字段的值
                    "code"=>$vercode,
                    "product"=>"dt"
                )
            );
            //此处需要发送短信
            CacheHelper::set('bind_vercode_'.$mobile,$vercode,900);
            $this->response();
        }
    }

    public function actionGetBindVercodes(){
        $mobile = Yii::$app->request->post('mobile','');
        if(!preg_match("/^1[34578]{1}\d{9}$/",$mobile)){
            $this->error('100001','请输入正确的手机号码');
        }

        if($vercode = CacheHelper::get('bind_vercode_'.$mobile)){
            $this->response();
        }else{
            $vercode = $this->_generate_code(6);
            //此处需要发送短信
            $response = SmsDemo::sendSms(
                "点投科技", // 短信签名
                "SMS_109350475", // 短信模板编号
                $mobile, // 短信接收者
                Array(  // 短信模板中字段的值
                    "code"=>$vercode,
                    "product"=>"dt"
                )
            );
            //此处需要发送短信
            CacheHelper::set('bind_vercode_'.$mobile,$vercode,900);
            $this->response();
        }
    }

    public function actionBind(){
        $mobile = Yii::$app->request->post('mobile','');
        $vercode = Yii::$app->request->post('vercode','');
        $openid = Yii::$app->request->post('openid','');
        $type = Yii::$app->request->post('type','');
        $registrationId = Yii::$app->request->post('registrationId','');
        if(!preg_match("/^1[34578]{1}\d{9}$/",$mobile)){
            $this->error('100001','请输入正确的手机号码');
        }
        $userLogic = new UserLogic();
        if($vercode == CacheHelper::get('bind_vercode_'.$mobile)){
           $res = $userLogic->bind($mobile,$openid,$type,$registrationId);
            $this->response($res);
        }else{
            $this->error('100006','验证码不正确');
        }
    }

    public function actionGetNewbindVercode(){
        $mobile = Yii::$app->request->post('mobile','');
        $uid = Yii::$app->request->post('uid','');
        if(!preg_match("/^1[34578]{1}\d{9}$/",$mobile)){
            $this->error('100001','请输入正确的手机号码');
        }
        $userLogic = new UserLogic();
        $res = $userLogic->getUser($mobile);
        if($res['status']){
            $this->error('100002',$res['msg']);
        }
        if($vercode = CacheHelper::get('bind_vercode_'.$mobile)){
            $this->response();
        }else{
            $vercode = $this->_generate_code(6);
            //此处需要发送短信
            $response = SmsDemo::sendSms(
                "点投科技", // 短信签名
                "SMS_109490088", // 短信模板编号
                $mobile, // 短信接收者
                Array(  // 短信模板中字段的值
                    "code"=>$vercode,
                    "product"=>"dt"
                )
            );
            //此处需要发送短信
            CacheHelper::set('bind_vercode_'.$mobile,$vercode,900);
            $this->response();
        }
    }

    public function actionCheckNewbindVercode(){
        //默认通过校验
        //$this->response();
        //默认通过校验
        $mobile = Yii::$app->request->post('mobile','');
        $vercode = Yii::$app->request->post('vercode','');
        $uid = Yii::$app->request->post('uid','');
        if(!preg_match("/^1[34578]{1}\d{9}$/",$mobile)){
            $this->error('100001','请输入正确的手机号码');
        }
        $userLogic = new UserLogic();
        $res = $userLogic->getUser($mobile);
        if($res['status']){
            $this->error('100002',$res['msg']);
        }
        if($vercode == CacheHelper::get('bind_vercode_'.$mobile)){
            $res = $userLogic->changephone($uid,$mobile);
            if($res)
                $this->response();
        }else{
            $this->error('100006','验证码不正确');
        }
    }


    public function actionCheckBindVercode(){
        //默认通过校验
        //$this->response();
        //默认通过校验
        $mobile = Yii::$app->request->post('mobile','');
        $vercode = Yii::$app->request->post('vercode','');
        $uid = Yii::$app->request->post('uid','');
        if(!preg_match("/^1[34578]{1}\d{9}$/",$mobile)){
            $this->error('100001','请输入正确的手机号码');
        }
        $userLogic = new UserLogic();
        $user = $userLogic->getUserinfo($uid);
        if($user->user_name!=$mobile){
            $this->error('100001','请输入正确的手机号码');
        }
        if($vercode == CacheHelper::get('bind_vercode_'.$mobile)){
            $this->response();
        }else{
            $this->error('100006','验证码不正确');
        }
    }

    public function actionCheckVercode(){
        //默认通过校验
        $this->response();
        //默认通过校验
        $mobile = Yii::$app->request->post('mobile','');
        $vercode = Yii::$app->request->post('vercode','');
        if(!preg_match("/^1[34578]{1}\d{9}$/",$mobile)){
            $this->error('100001','请输入正确的手机号码');
        }
        $userLogic = new UserLogic();
        $res = $userLogic->getUser($mobile);
        if(!$res['status']){
            $this->error('100002',$res['msg']);
        }
        if($vercode == CacheHelper::get('reg_vercode_'.$mobile)){
            $this->response();
        }else{
            $this->error('100006','验证码不正确');
        }
    }

    public  function  actionSetNewpass(){
        $mobile = Yii::$app->request->post('mobile','');
        $newpass = Yii::$app->request->post('newpass','');
        $conpass = Yii::$app->request->post('conpass','');
        if($newpass!=$conpass){
            $this->error('100003','新密码与确认密码不一致');
        }
        if(strlen($newpass) < 6 || strlen($newpass)>16){
            $this->error('100004','密码长度只允许6~16个字符');
        }
        if(!preg_match("/^[a-zA-Z0-9]*$/",$newpass)) {
            $this->error('100005','密码格式不可以包含特殊符号，请重新输入');
        }
        $userLogic = new UserLogic();
        $res = $userLogic->setnewpass($mobile,$newpass);
        if($res){
            $this->response();
        }

    }

    public function actionRegist(){
        $registrationId = Yii::$app->request->post('registrationId','');
        $mobile = Yii::$app->request->post('mobile','');
        $vercode = Yii::$app->request->post('vercode','');
        $pass = Yii::$app->request->post('pass','');
        $conpass = Yii::$app->request->post('conpass','');
        if(!preg_match("/^1[34578]{1}\d{9}$/",$mobile)){
            $this->error('100001','请输入正确的手机号码');
        }
        $userLogic = new UserLogic();
        $res = $userLogic->getUser($mobile);
        if($res['status']){
            $this->error('100002',$res['msg']);
        }
        //默认通过校验
        if($vercode != CacheHelper::get('reg_vercode_'.$mobile)){
            $this->error('100006','验证码不正确');
        }
        //默认通过校验
        if($pass!=$conpass){
            $this->error('100003','新密码与确认密码不一致');
        }
        if(strlen($pass) < 6 || strlen($pass)>16){
            $this->error('100004','密码长度只允许6~16个字符');
        }
        if(!preg_match("/^[a-zA-Z0-9]*$/",$pass)) {
            $this->error('100005','密码格式不可以包含特殊符号，请重新输入');
        }
        $userLogic = new UserLogic();
        $res = $userLogic->regist($mobile,$pass,$registrationId);
        $res['uid'] = $res['id'];
        if($res){
            $this->response($res);
        }
    }

    public function actionUploadHeadimg(){
        if(!empty($_FILES)){
            //LocalUpload::upload_config(array('png','jpg','jpeg'),5000000,'touimg');
            LocalUpload::go_upload($_FILES['file']);
            //$files=$_POST['typeCode'];
            if(!LocalUpload::$ok){
                echo LocalUpload::$error;
            }else{
                $img=LocalUpload::$filedir."/".LocalUpload::$filename;
                $size=getimagesize(LocalUpload::$uploadpath.$img);
                $max=100;$w=$size[0];$h=$size[1];
                if($w>100 or $h>100){
                    if($w>$h){
                        $w2=$max;
                        $h2 = intval($h*($max/$w));
                        LocalUpload::thumbs($w2,$h2);
                    }else{
                        $h2=$max;
                        $w2 = intval($w*($max/$h));
                        LocalUpload::thumbs($w2,$h2);
                    }
                }
                //LocalUpload::thumbs(200,200,true);
                echo $img;exit;
            }
        }
        echo "no photo";exit;
    }

    public function actionSaveInvestor(){
        $uid = Yii::$app->request->post('uid',0);
        $headimg = Yii::$app->request->post('headimg','');
        $name = Yii::$app->request->post('name','');
        $en_name = Yii::$app->request->post('en_name','');
        $sex = Yii::$app->request->post('sex',-1);
        $phone= Yii::$app->request->post('phone','');
        $title = Yii::$app->request->post('title','');
        $summary= Yii::$app->request->post('summary','');
        $business_card = Yii::$app->request->post('business_card','');
        if(strlen($title)>45){
            $this->error('100007','所在机构/职位不能超过15个字');
        }
        //$headimg = substr ($headimg,9);
//        $arr = explode(',',$business_card);
//        foreach($arr AS &$value){
//            $value = substr ($value,9);
//        }
//        $business_card = implode(',',$arr);
        $userLogic = new UserLogic();
        $res = $userLogic->saveInvestor($uid,$headimg,$name,$en_name,$sex,$phone,$title,$summary,$business_card);
        $file = $res['head_img'];
        $houzhui = explode('.',$file);
        $houzhui = array_pop($houzhui);
//        if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file.'_'.'100100'.'.'.$houzhui)){
//            $res['thumb'] = $file.'_'.'100100'.'.'.$houzhui;
//        }else{
//            $res['thumb'] = $file;
//        }
        if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file.'_'.'100100'.'.'.$houzhui)){
            $res['thumb'] = $file.'_'.'100100'.'.'.$houzhui;
        }elseif(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file)){
            $res['thumb'] = $file;
        }else{
            $res['thumb'] = 'default-avatar.png';
        }
        if(!$file){
            $res['thumb'] = 'default-avatar.png';
        }

         if($res){
             $this->response($res);
         }
    }

    public function actionSaveCompany(){
        $uid = Yii::$app->request->post('uid',0);
        $logoimg = Yii::$app->request->post('logoimg','');
        $name = Yii::$app->request->post('name','');
        $found_date = Yii::$app->request->post('found_date','');
        $money = Yii::$app->request->post('money',0);
        $owner = Yii::$app->request->post('owner','');
        $employee = Yii::$app->request->post('employee',0);
        $zone = Yii::$app->request->post('zone','');
        $address = Yii::$app->request->post('address','');
        $industry = Yii::$app->request->post('industry',0);
        $market = Yii::$app->request->post('market',0);
        $phone= Yii::$app->request->post('phone','');
        $summary= Yii::$app->request->post('summary','');
        $certificate = Yii::$app->request->post('certificate','');
        if(strlen($owner)>30){
            $this->error('100008','法人不能超过10个字');
        }
        if(!is_numeric($employee)){
            $this->error('100009','员工人数只能为数字');
        }
        //$logoimg = substr ($logoimg,9);
//        $arr = explode(',',$certificate);
//        foreach($arr AS &$value){
//            $value = substr ($value,9);
//        }
//        $certificate = implode(',',$arr);
        $arr = explode(',',$zone);
        $province_id = $arr[0];
        $city_id = $arr[1];
        $district_id = $arr[2];
        $userLogic = new UserLogic();
        $res = $userLogic->saveCompany($uid,$logoimg,$name,$found_date,$money,$owner,$employee,$province_id,$city_id,$district_id,$address,$industry,$market,$phone,$summary,$certificate);
        $file = $res['logo_img'];
        $houzhui = explode('.',$file);
        $houzhui = array_pop($houzhui);
//        if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file.'_'.'100100'.'.'.$houzhui)){
//            $res['thumb'] = $file.'_'.'100100'.'.'.$houzhui;
//        }else{
//            $res['thumb'] = $file;
//        }
        if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file.'_'.'100100'.'.'.$houzhui)){
            $res['thumb'] = $file.'_'.'100100'.'.'.$houzhui;
        }elseif(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file)){
            $res['thumb'] = $file;
        }else{
            $res['thumb'] = 'default-avatar.png';
        }
        if(!$file){
            $res['thumb'] = 'default-avatar.png';
        }
        if($res){
            $this->response($res);
        }
    }

    public function actionGetFavorite(){
        $uid = Yii::$app->request->post('uid',0);
        $skip = Yii::$app->request->post('skip',0);
        $limit = Yii::$app->request->post('limit',20);
        $userLogic = new UserLogic();
        $res = $userLogic->getFavorite($uid,$skip,$limit);
        foreach($res AS $key=>&$value){
            if(date('Y-m-d') == date('Y-m-d', strtotime($value['input_time']))){
                $value['time'] = date('H:i', strtotime($value['input_time']));
            }else{
                $value['time'] = date('Y-m-d H:i', strtotime($value['input_time']));
            }
            if(isset($value['logo_img'])){
                $file = $value['logo_img'];
                $houzhui = explode('.',$file);
                $houzhui = array_pop($houzhui);

//                if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file.'_'.'100100'.'.'.$houzhui)){
//                    $value['logo_img_thumb']  = $file.'_'.'100100'.'.'.$houzhui;
//                }else{
//                    $value['logo_img_thumb'] = $file;
//                }
                if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file.'_'.'100100'.'.'.$houzhui)){
                    $value['logo_img_thumb'] = $file.'_'.'100100'.'.'.$houzhui;
                }elseif(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file)){
                    $value['logo_img_thumb'] = $file;
                }else{
                    $value['logo_img_thumb'] = 'default-avatar.png';
                }
                if(!$file){
                    $value['logo_img_thumb'] = 'default-avatar.png';
                }

                $imgarr = explode(',',$value['img']);
                foreach ($imgarr AS $k=>$v){
                    if($k<4){
                        $file = $v;
                        $houzhui = explode('.',$file);
                        $houzhui = array_pop($houzhui);

//                        if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file.'_'.'100100'.'.'.$houzhui)){
//                            $value['imgs'][] = "http://".\Yii::$app->params['back_domain'].\Yii::$app->params['upload']['upload_path'].$file.'_'.'100100'.'.'.$houzhui;
//                        }else{
//                            $value['imgs'][] = "http://".\Yii::$app->params['back_domain'].\Yii::$app->params['upload']['upload_path'].$file;
//                        }
                        if($file){
                            if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file.'_'.'100100'.'.'.$houzhui)){
                                $value['imgs'][] = "http://".\Yii::$app->params['back_domain'].\Yii::$app->params['upload']['upload_path'].$file.'_'.'100100'.'.'.$houzhui;
                            }elseif(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file)){
                                $value['imgs'][] = "http://".\Yii::$app->params['back_domain'].\Yii::$app->params['upload']['upload_path'].$file;
                            }else{
                                $value['imgs'][] = "http://".\Yii::$app->params['back_domain'].\Yii::$app->params['upload']['upload_path'].'default-image.png';
                            }
                        }


                    }
                }
                $value['imgs_num'] = count($imgarr);
            }
            if(isset($value['head_img'])){
                $file = $value['head_img'];
                $houzhui = explode('.',$file);
                $houzhui = array_pop($houzhui);

//                if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file.'_'.'100100'.'.'.$houzhui)){
//                    $value['head_img_thumb']  = $file.'_'.'100100'.'.'.$houzhui;
//                }else{
//                    $value['head_img_thumb'] = $file;
//                }
                if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file.'_'.'100100'.'.'.$houzhui)){
                    $value['head_img_thumb'] = $file.'_'.'100100'.'.'.$houzhui;
                }elseif(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file)){
                    $value['head_img_thumb'] = $file;
                }else{
                    $value['head_img_thumb'] = 'default-avatar.png';
                }
                if(!$file){
                    $value['head_img_thumb'] = 'default-avatar.png';
                }


                $file = $value['to_company_img'];
                $houzhui = explode('.',$file);
                $houzhui = array_pop($houzhui);

//                if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file.'_'.'100100'.'.'.$houzhui)){
//                    $value['to_company_img_thumb'] = $file.'_'.'100100'.'.'.$houzhui;
//                }else{
//                    $value['to_company_img_thumb'] = $file;
//                }
                if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file.'_'.'100100'.'.'.$houzhui)){
                    $value['to_company_img_thumb'] = $file.'_'.'100100'.'.'.$houzhui;
                }elseif(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file)){
                    $value['to_company_img_thumb'] = $file;
                }else{
                    $value['to_company_img_thumb'] = 'default-avatar.png';
                }
                if(!$file){
                    $value['to_company_img_thumb'] = 'default-avatar.png';
                }

                $comment_star = explode(',',$value['star']);
                $value['comment_star'][0]['name'] = '行业前景';
                $value['comment_star'][0]['score'] = $comment_star[0];
                $value['comment_star'][1]['name'] = '盈利能力';
                $value['comment_star'][1]['score'] = $comment_star[1];
                $value['comment_star'][2]['name'] = '管理团队';
                $value['comment_star'][2]['score'] = $comment_star[2];
                $value['comment_star'][3]['name'] = '成长增速';
                $value['comment_star'][3]['score'] = $comment_star[3];
                $value['comment_star'][4]['name'] = '资本偏好';
                $value['comment_star'][4]['score'] = $comment_star[4];
                $imgarr = explode(',',$value['images']);
                foreach ($imgarr AS $k=>$v){
                    if($k<4){
                        $file = $v;
                        $houzhui = explode('.',$file);
                        $houzhui = array_pop($houzhui);
//                        if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file.'_'.'100100'.'.'.$houzhui)){
//                            $value['imgs'][] = "http://".\Yii::$app->params['back_domain'].\Yii::$app->params['upload']['upload_path'].$file.'_'.'100100'.'.'.$houzhui;
//                        }else{
//                            $value['imgs'][] = "http://".\Yii::$app->params['back_domain'].\Yii::$app->params['upload']['upload_path'].$file;
//                        }
                        if($file){
                            if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file.'_'.'100100'.'.'.$houzhui)){
                                $value['imgs'][] = "http://".\Yii::$app->params['back_domain'].\Yii::$app->params['upload']['upload_path'].$file.'_'.'100100'.'.'.$houzhui;
                            }elseif(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file)){
                                $value['imgs'][] = "http://".\Yii::$app->params['back_domain'].\Yii::$app->params['upload']['upload_path'].$file;
                            }else{
                                $value['imgs'][] = "http://".\Yii::$app->params['back_domain'].\Yii::$app->params['upload']['upload_path'].'default-image.png';
                            }
                        }
                    }
                }
                $value['imgs_num'] = count($imgarr);
                $tagids = explode(',',$value['tag']);
                $value['tags']  = $userLogic->getTag($tagids);
            }
        }
        $this->response($res);
    }

    public function actionSaveFavorite(){
        $uid = Yii::$app->request->post('uid',0);
        $id = Yii::$app->request->post('id',0);
        $userLogic = new UserLogic();
        $res = $userLogic->saveFavorite($uid,$id);
        $this->response();
    }

    public function actionCancelFavorite(){
        $uid = Yii::$app->request->post('uid',0);
        $id = Yii::$app->request->post('id',0);
        $userLogic = new UserLogic();
        $res = $userLogic->cancelFavorite($uid,$id);
        $this->response();
    }

    public function actionDelComment(){
        $uid = Yii::$app->request->post('uid',0);
        $id = Yii::$app->request->post('id',0);
        $userLogic = new UserLogic();
        $res = $userLogic->delComment($uid,$id);
        $this->response();
    }

    public function actionDelTrends(){
        $uid = Yii::$app->request->post('uid',0);
        $id = Yii::$app->request->post('id',0);
        $userLogic = new UserLogic();
        $res = $userLogic->delTrends($uid,$id);
        $this->response();
    }


    private function _generate_code($length = 4) {
        $min = pow(10 , ($length - 1));
        $max = pow(10, $length) - 1;
        return rand($min, $max);
    }

    public function actionUserinfo(){
        $id = Yii::$app->request->post('id',0);
        $userLogic = new UserLogic();
        $res = $userLogic->getUserinfodetail($id);
        if(isset($res['head_img'])){
            $file = $res['head_img'];
        }elseif(isset($res['logo_img'])){
            $file = $res['logo_img'];
        }
        $houzhui = explode('.',$file);
        $houzhui = array_pop($houzhui);

//        if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file.'_'.'100100'.'.'.$houzhui)){
//            $res['thumb'] = $file.'_'.'100100'.'.'.$houzhui;
//        }else{
//            $res['thumb'] = $file;
//        }
        if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file.'_'.'100100'.'.'.$houzhui)){
            $res['thumb'] = $file.'_'.'100100'.'.'.$houzhui;
        }elseif(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file)){
            $res['thumb'] = $file;
        }else{
            $res['thumb'] = 'default-avatar.png';
        }
        if(!$file){
            $res['thumb'] = 'default-avatar.png';
        }
        $this->response($res);
    }

    public function actionHeadimg(){
        $uid = Yii::$app->request->post('uid',0);
        if(!empty($_FILES)){
            //LocalUpload::upload_config(array('png','jpg','jpeg'),5000000,'touimg');
            LocalUpload::go_upload($_FILES['file']);
            //$files=$_POST['typeCode'];
            if(!LocalUpload::$ok){
                echo LocalUpload::$error;
            }else{
                $img=LocalUpload::$filedir."/".LocalUpload::$filename;
                /*$size=getimagesize(LocalUpload::$uploadpath.$img);
                $max=200;$w=$size[0];$h=$size[1];
                if($w>200 || $h>200){
                    if($w>$h){
                        $w2=$max;
                        $h2 = intval($h*($max/$w));
                        LocalUpload::thumbs($w2,$h2,true);
                    }else{
                        $h2=$max;
                        $w2 = intval($w*($max/$h));
                        LocalUpload::thumbs($w2,$h2,true);
                    }
                }*/
                LocalUpload::thumbs(200,200,true);
                $userLogic = new UserLogic();
                $userLogic->saveImg($uid,$img);
                echo $img;exit;
            }
        }
        echo "no photo";exit;
    }

    public function actionChangename(){
        $uid = Yii::$app->request->post('uid',0);
        $name = Yii::$app->request->post('name','');
        $userLogic = new UserLogic();
        $userLogic->changeName($uid,$name);
        $this->response();
    }

    public function actionChangeenname(){
        $uid = Yii::$app->request->post('uid',0);
        $en_name = Yii::$app->request->post('en_name','');
        $userLogic = new UserLogic();
        $userLogic->changeEnname($uid,$en_name);
        $this->response();
    }

    public function actionChangesex(){
        $uid = Yii::$app->request->post('uid',0);
        $sex = Yii::$app->request->post('sex',-1);
        $userLogic = new UserLogic();
        $userLogic->changeSex($uid,$sex);
        $this->response();
    }

    public function actionChangeCompanyInfo(){
        $uid = Yii::$app->request->post('uid',0);
        $info = Yii::$app->request->post('info','');
        $info_content = Yii::$app->request->post('info_content','');
        $userLogic = new UserLogic();
        $userLogic->changeCompanyInfo($uid,$info,$info_content);
        $this->response();
    }

    public function actionChangeZone(){
        $uid = Yii::$app->request->post('uid',0);
        $province_id = Yii::$app->request->post('province_id',0);
        $city_id = Yii::$app->request->post('city_id',0);
        $district_id = Yii::$app->request->post('district_id',0);
        $userLogic = new UserLogic();
        $userLogic->changeZone($uid,$province_id,$city_id,$district_id);
        $this->response();
    }

    public function actionQqlogin(){
        $openid = Yii::$app->request->post('openid',0);
        $registrationId = Yii::$app->request->post('registrationId','');
        $userLogic = new UserLogic();
        $res = $userLogic->openidlogin($openid,$registrationId,0);
        if($res){
            $this->response($res);
        }else{
            $this->error($no='20001',$msg='尚未绑定账户');
        }
    }

    public function actionWechatlogin(){
        $openid = Yii::$app->request->post('openid',0);
        $registrationId = Yii::$app->request->post('registrationId','');
        $userLogic = new UserLogic();
        $res = $userLogic->openidlogin($openid,$registrationId,1);
        if($res){
            $this->response($res);
        }else{
            $this->error($no='20001',$msg='尚未绑定账户');
        }
    }

    public function actionGetContactsByphone(){
        $mobile = Yii::$app->request->post('mobile','');
        $uid = Yii::$app->request->post('uid',0);
        $mobile = json_decode($mobile,true);
        $userLogic = new UserLogic();
        $res = $userLogic->findUser($mobile,$uid);
        if($res){
            $this->response($res);
        }else{
            $this->error();
        }
    }


}