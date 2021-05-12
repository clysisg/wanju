<?php
/**
 * Created by PhpStorm.
 * User: wjj
 * Date: 2016/11/1
 */

namespace app\logic;


use app\models\Category;
use app\models\Comment;
use app\models\CommentTrends;
use app\models\Industry;
use app\models\Region;
use app\models\Tag;
use app\models\Trends;
use app\models\Company;
use app\models\Investor;
use app\models\OpenHistory;
use app\models\User;
use yii;
use yii\db\Query;

use JPush\Client as JPush;

class CommonLogic
{
    public function saveOpenHistory($device)
    {
        $oh = new OpenHistory();
        $oh->device = $device;
        $oh->input_date = date('Y-m-d H:i:s');
        return $oh->save();
    }

    public function searchCompany($keyword,$skip,$limit){
        return Company::find()
            ->select('c.*, i.name industry_name,r1.region_shortname province_name,r2.region_shortname city_name,ca.name market_name')
            ->from('{{%company}} c')
            ->leftJoin('{{%user}} u', 'u.id=c.uid')
            ->leftJoin('{{%industry}} i', 'i.industry_id=c.industry_id')
            ->leftJoin('{{%category}} ca', 'ca.category_id=c.market_id')
            ->leftJoin('{{%region}} r1', 'r1.region_code=c.province_id')
            ->leftJoin('{{%region}} r2', 'r2.region_code=c.city_id')
            ->where(['or',['like','c.name',$keyword],['like','i.name',$keyword]])
            ->andWhere('u.status!=0')
            ->offset($skip)
            ->limit($limit)
            ->asArray()
            ->all();
    }
    public function searchInvestor($keyword,$skip,$limit){
        return Investor::find()
            ->from('{{%investor}} i')
            ->leftJoin('{{%user}} u', 'u.id=i.uid')
            ->where(['like','i.name',$keyword])
            ->andWhere('u.status!=0')
            ->offset($skip)
            ->limit($limit)
            ->asArray()
            ->all();
    }

    public function getAll($uid,$type,$skip,$limit){
        if($type==-1){
            return CommentTrends::find()
                ->select('ct.id,ct.type,ct.input_date input_time,c.*,t.*,co.uid company_uid,i.head_img,i.uid investor_id,i.name investor_name,i.title,co.logo_img,co.name company_name,co1.name to_company_name,co1.logo_img to_company_img,uf.id user_favorite_id')
                ->from('{{%comment_trends}} ct')
                ->leftJoin('{{%follow}} f','find_in_set(f.follow_to_uid,ct.uid)')
                ->leftJoin('{{%comment}} c','c.comment_id=ct.id')
                ->leftJoin('{{%trends}} t','t.trends_id=ct.id')
                ->leftJoin('{{%investor}} i','i.uid = c.comment_by_uid')
                ->leftJoin('{{%company}} co1','co1.uid = c.comment_to_uid')
                ->leftJoin('{{%company}} co','co.uid = t.uid')
                ->leftJoin('{{%user_favorite}} uf','uf.comment_trends_id = ct.id and uf.user_id='.$uid)
                ->where(['f.follow_by_uid'=>$uid])
                ->andWhere( 'c.status=1 OR c.status is null')
                ->groupBy('ct.id')
                ->offset($skip)
                ->limit($limit)
                ->orderBy('ct.input_date desc')
                ->asArray()
                ->all();
        }elseif($type==0){
            return CommentTrends::find()
                ->select('ct.id,ct.type,ct.input_date input_time,c.*,i.uid investor_id,i.head_img,i.name investor_name,i.title,co.name to_company_name,co.logo_img to_company_img,uf.id user_favorite_id')
                ->from('{{%comment_trends}} ct')
                ->leftJoin('{{%follow}} f','find_in_set(f.follow_to_uid,ct.uid)')
                ->leftJoin('{{%comment}} c','c.comment_id=ct.id')
                ->leftJoin('{{%investor}} i','i.uid = c.comment_by_uid')
                ->leftJoin('{{%company}} co','co.uid = c.comment_to_uid')
                ->leftJoin('{{%user_favorite}} uf','uf.comment_trends_id = ct.id and uf.user_id='.$uid)
                ->where(['f.follow_by_uid'=>$uid,'f.type'=>1])
                ->andWhere( 'c.status=1 OR c.status is null')
                ->groupBy('ct.id')
                ->offset($skip)
                ->limit($limit)
                ->orderBy('ct.input_date desc')
                ->asArray()
                ->all();
        }else{
            /*return CommentTrends::find()
                ->select('ct.id,ct.type,ct.input_date input_time,t.*,co.logo_img,co.name company_name,t.img,uf.id user_favorite_id')
                ->from('{{%comment_trends}} ct')
                ->leftJoin('{{%follow}} f','f.follow_to_uid=ct.uid')
                ->leftJoin('{{%trends}} t','t.trends_id=ct.id')
                ->leftJoin('{{%company}} co','co.uid = t.uid')
                ->leftJoin('{{%user_favorite}} uf','uf.comment_trends_id = ct.id and uf.user_id='.$uid)
                ->where(['f.follow_by_uid'=>$uid,'f.type'=>2])
                ->groupBy('ct.id')
                ->offset($skip)
                ->limit($limit)
                ->orderBy('ct.input_date desc')
                ->asArray()
                ->all();*/
            return CommentTrends::find()
                ->select('ct.id,ct.type,ct.input_date input_time,c.*,t.*,co.uid company_uid,i.uid investor_id,i.head_img,i.name investor_name,i.title,co.logo_img,co.name company_name,co1.name to_company_name,co1.logo_img to_company_img,uf.id user_favorite_id')
                ->from('{{%comment_trends}} ct')
                ->leftJoin('{{%follow}} f','find_in_set(f.follow_to_uid,ct.uid)')
                ->leftJoin('{{%comment}} c','c.comment_id=ct.id')
                ->leftJoin('{{%trends}} t','t.trends_id=ct.id')
                ->leftJoin('{{%investor}} i','i.uid = c.comment_by_uid')
                ->leftJoin('{{%company}} co1','co1.uid = c.comment_to_uid')
                ->leftJoin('{{%company}} co','co.uid = t.uid')
                ->leftJoin('{{%user_favorite}} uf','uf.comment_trends_id = ct.id and uf.user_id='.$uid)
                ->where(['f.follow_by_uid'=>$uid,'f.type'=>2])
                ->andWhere( 'c.status=1 OR c.status is null')
                ->groupBy('ct.id')
                ->offset($skip)
                ->limit($limit)
                ->orderBy('ct.input_date desc')
                ->asArray()
                ->all();
        }

    }

    /*public function chat($uid,$id){
        $chat_to_uid = User::findOne(['id'=>$id]);
        $app_key = \Yii::$app->params['jpush']['app_key'];
        $master_secret = \Yii::$app->params['jpush']['master_secret'];
        $client = new JPush($app_key, $master_secret);
        $client->push()
            ->setPlatform('all')
            ->addRegistrationId($chat_to_uid->device)
            ->message($need_logout,['title'=>'need_logout','content_type'=>'1','extras'=>''])
            ->send();
    }*/

    public function getMarket(){
        return Category::find()->where(['is_fixed'=>0])->asArray()->all();
    }
    public function getIndustry(){
        return Industry::find()->asArray()->all();
    }
    public function getTag(){
        return Tag::find()->asArray()->all();
    }

    public function getProvince($city){
        return Region::find()
            ->select('r.*,r1.region_shortname province_name')
            ->from('{{%region}} r')
            ->leftJoin('{{%region}} r1', 'r.parent_code = r1.region_code')
            ->where(['LIKE','r.region_shortname',$city])
            ->andWhere(['r.region_level'=>2])
            ->asArray()
            ->one();
    }

    public function getCategorysearch($submit){
        $where1 = [];
        $where2 = [];
        $where3 = [];
        $where4 = [];
        foreach($submit AS $value){
            $type = substr($value,0,3);
            $filter = substr($value,3);
            switch($type){
                case "mar":
                    $where1[] = "co.market_id=$filter";
                    break;
                case "zon":
                    $where2[] = "co.city_id=$filter";
                    break;
                case "ind":
                    $where3[] = "co.industry_id=$filter";
                    break;
                case "tag":
                    $where4[] = 'FIND_IN_SET('.$filter.',c.tag)';
            }
            //var_dump($where4temp);
        }
        if($where1){
            $where1 = implode(' OR ',$where1);
        }
        if($where2){
            $where2 = implode(' OR ',$where2);
        }
        if($where3){
            $where3 = implode(' OR ',$where3);
        }
        if($where4){
            $where4 = implode(' OR ',$where4);
        }
        return Company::find()
            ->select('co.*')
            ->from('{{%company}} co')
            ->leftJoin('{{%comment}} c','c.comment_to_uid=co.uid and c.status=1')
            ->andWhere($where1)
            ->andWhere($where2)
            ->andWhere($where3)
            ->andWhere($where4)
            ->asArray()
            ->all();
    }
}