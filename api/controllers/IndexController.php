<?php
namespace app\controllers;

use app\common\corelib\CacheHelper;
use app\logic\CategoryLogic;
use app\logic\CommonLogic;
use app\models\Region;
use Yii;


class IndexController extends BaseController
{

	public function actionIndex()
	{
            echo "index";
	}

	public function actionChangeJson1(){
        $regions = Region::find()->select('region_code id, region_shortname city, pinyin')->where(['region_level'=>2])->asArray()->all();
        echo file_put_contents('cityList.json',json_encode($regions));
    }

    public function actionChangeJson2(){
        $region = Region::find()->select('region_code id, region_name name')->where(['region_level'=>1])->asArray()->all();
        $result = [];
        foreach($region AS $key=>&$value){
            $city = Region::find()->select('region_code id, region_name name')->where(['region_level'=>2,'parent_code'=>$value['id']])->asArray()->all();
            foreach($city AS $k=>&$v){
                $district = Region::find()->select('region_code id, region_name name')->where(['region_level'=>3,'parent_code'=>$v['id']])->asArray()->all();
                $v['sub'] = $district;
            }
            $value['sub'] = $city;
        }
        echo file_put_contents('district.txt',json_encode($region));
    }

    public function actionChangeJson3(){
        $region = Region::find()->select('region_code id, region_name name, pinyin code')->where(['region_level'=>1])->asArray()->all();
        $result = [];
        foreach($region AS $key=>&$value){
            $city = Region::find()->select('region_code id, region_name name, pinyin code')->where(['region_level'=>2,'parent_code'=>$value['id']])->asArray()->all();
            foreach($city AS $k=>&$v){
                $district = Region::find()->select('region_code id, region_name name, pinyin code')->where(['region_level'=>3,'parent_code'=>$v['id']])->asArray()->all();
                $v['sub'] = $district;
            }
            $value['sub'] = $city;
        }
        echo file_put_contents('position1.json',json_encode($region));
    }

	public function actionOpenHistory(){
        $uid = Yii::$app->request->post('uid',0);
        $device = Yii::$app->request->post('device','');
        $registrationId = Yii::$app->request->post('registrationId','');
        if($device){
            $commonLogic = new CommonLogic();
            $result = $commonLogic->saveOpenHistory($device);
            if($result){
                if($uid){
                    if(CacheHelper::get('login_'.$uid)!=$registrationId){
                        $this->response(['need_logout'=>true]);
                    }
                 }
                $this->response(['need_logout'=>false]);
            }else{
                $this->error();
            }
        }else{
            $this->error();
        }
    }
	
    public function actionSearch(){
        $keyword = Yii::$app->request->post('keyword','');
        $skip = Yii::$app->request->post('skip',0);
        $limit = Yii::$app->request->post('limit',20);
        if(!$keyword){
            $this->response();
        }
        $commonLogic = new CommonLogic();
        $res1 = $commonLogic->searchCompany($keyword,$skip,$limit);
        foreach($res1 AS &$value){
            $file = $value['logo_img'];
            $houzhui = explode('.',$file);
            $houzhui = array_pop($houzhui);
//            if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file.'_'.'100100'.'.'.$houzhui)){
//                $value['thumb'] = $file.'_'.'100100'.'.'.$houzhui;
//            }else{
//                $value['thumb'] = $file;
//            }
            if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file.'_'.'100100'.'.'.$houzhui)){
                $value['thumb'] = $file.'_'.'100100'.'.'.$houzhui;
            }elseif(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file)){
                $value['thumb'] = $file;
            }else{
                $value['thumb'] = 'default-avatar.png';
            }
            if(!$file){
                $value['thumb'] = 'default-avatar.png';
            }


            if($value['province_name']=='北京' || $value['province_name']=='上海' || $value['province_name']=='天津' || $value['province_name']=='重庆'){
                $value['zone'] = $value['province_name'];
            }else{
                $value['zone'] = $value['province_name'].$value['city_name'];
            }

        }
        $res2 = $commonLogic->searchInvestor($keyword,$skip,$limit);
        foreach($res2 AS &$value){
            $file = $value['head_img'];
            $houzhui = explode('.',$file);
            $houzhui = array_pop($houzhui);
            if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file.'_'.'100100'.'.'.$houzhui)){
                $value['thumb'] = $file.'_'.'100100'.'.'.$houzhui;
            }else{
                $value['thumb'] = $file;
            }

        }
        $this->response(['company'=>$res1,'investor'=>$res2]);
    }

    public function actionAll(){
        $uid = Yii::$app->request->post('uid','0');
        $type = Yii::$app->request->post('type','-1');
        $skip = Yii::$app->request->post('skip',0);
        $limit = Yii::$app->request->post('limit',20);
        $commonLogic = new CommonLogic();
        $res = $commonLogic->getAll($uid, $type,  $skip, $limit);
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
//                    $value['logo_img_thumb'] = $file.'_'.'100100'.'.'.$houzhui;
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
//                    $value['head_img_thumb'] = $file.'_'.'100100'.'.'.$houzhui;
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

            }
        }
        $this->response($res);
    }
    public function actionTime(){
        echo date('Y-m-d H:i:s');
    }

    public function actionGetProvince(){
        $city = Yii::$app->request->post('city','');
        $commonLogic = new CommonLogic();
        $res = $commonLogic->getProvince($city);
        $this->response($res);
    }

    public function actionGetTag(){
        $commonLogic = new CommonLogic();
        $res = $commonLogic->getTag();
        $this->response($res);
    }
}