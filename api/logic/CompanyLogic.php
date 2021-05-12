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
use app\models\Company;
use app\models\Follow;
use app\models\Trends;
use yii;

class CompanyLogic
{
    public function getCompany($id){
        return Company::find()
            ->select('co.*,u.status,count(c.comment_id) comment_count,i.name industry_name,r1.region_shortname province_name,r2.region_shortname city_name,r3.region_shortname district_name,ca.name market_name')
            ->from('{{%company}} co')
            ->leftJoin('{{%user}} u','u.id=co.uid')
            ->leftJoin('{{%comment}} c','c.comment_to_uid=co.uid and c.status=1')
            ->leftJoin('{{%industry}} i', 'i.industry_id=co.industry_id')
            ->leftJoin('{{%category}} ca', 'ca.category_id=co.market_id')
            ->leftJoin('{{%region}} r1', 'r1.region_code=co.province_id')
            ->leftJoin('{{%region}} r2', 'r2.region_code=co.city_id')
            ->leftJoin('{{%region}} r3', 'r3.region_code=co.district_id')
            ->where(['co.uid'=>$id])
            ->asArray()
            ->one();
    }

    public function saveFollow($id,$uid){
        if($follow = Follow::findOne(['follow_by_uid'=>$uid,'follow_to_uid'=>$id,'type'=>2])){
            $res = $follow->delete();
            return ['res'=>$res,'is_add'=>0];
        }else{
            $follow = new Follow();
            $follow->follow_by_uid = $uid;
            $follow->follow_to_uid = $id;
            $follow->type = 2;
            $follow->input_date = date('Y-m-d H:i:s');
            $res = $follow->save();
            return ['res'=>$res,'is_add'=>1];
        }
    }

    public function getTrends($id,$uid,$skip,$limit){
        return CommentTrends::find()
            ->select('ct.id,ct.type,ct.input_date input_time,c.*,t.*,i.uid investor_uid,i.head_img,i.name investor_name,i.title,co.logo_img,co.name company_name,co1.name to_company_name,co1.logo_img to_company_img,uf.id user_favorite_id')
            ->from('{{%comment_trends}} ct')
            ->leftJoin('{{%comment}} c','c.comment_id=ct.id')
            ->leftJoin('{{%trends}} t','t.trends_id=ct.id')
            ->leftJoin('{{%investor}} i','i.uid = c.comment_by_uid')
            ->leftJoin('{{%company}} co1','co1.uid = c.comment_to_uid')
            ->leftJoin('{{%company}} co','co.uid = t.uid')
            ->leftJoin('{{%user_favorite}} uf','uf.comment_trends_id = ct.id and uf.user_id='.$uid)
            ->where('find_in_set('.$id.',ct.uid) and (c.status=1 OR c.status is null)')
            ->groupBy('ct.id')
            ->offset($skip)
            ->limit($limit)
            ->orderBy('ct.input_date desc')
            ->asArray()
            ->all();
    }

    public function getComment($id,$uid,$skip,$limit){
        return Comment::find()
            ->select('ct.id,ct.input_date input_time,c.*,i.uid investor_uid,i.head_img,i.name investor_name,i.title,co1.uid to_company_uid,co1.name to_company_name,co1.logo_img to_company_img,c.content,uf.id user_favorite_id')
            ->from('{{%comment}} c')
            ->leftJoin('{{%comment_trends}} ct','c.comment_id=ct.id')
            ->leftJoin('{{%investor}} i','i.uid = c.comment_by_uid')
            ->leftJoin('{{%company}} co1','co1.uid = c.comment_to_uid')
            ->leftJoin('{{%user_favorite}} uf','uf.comment_trends_id = ct.id and uf.user_id='.$uid)
            ->where(['c.comment_to_uid'=>$id,'c.status'=>1])
            ->offset($skip)
            ->limit($limit)
            ->orderBy('ct.input_date desc')
            ->asArray()
            ->all();
    }

    public function getTrendsPhoto($id){
        return Trends::find()->select('img')->where(['uid'=>$id])->orderBy('trends_id desc')->asArray()->all();
    }

    public function getCommentPhoto($id){
        return Comment::find()->select('images')->where(['comment_to_uid'=>$id])->orderBy('comment_id desc')->asArray()->all();
    }

    public function getTrendsDetail($id,$uid){
        return Trends::find()
            ->select('t.*,uf.id user_favorite_id,co.logo_img,co.name company_name')
            ->from('{{%trends}} t')
            ->leftJoin('{{%company}} co','co.uid = t.uid')
            ->leftJoin('{{%user_favorite}} uf','uf.comment_trends_id = t.trends_id and uf.user_id='.$uid)
            ->where('t.trends_id='.$id)
            ->asArray()
            ->one();
    }

    public function getCommentDetail($id,$uid){
        return Comment::find()
            ->select('c.*,i.head_img,i.name investor_name,i.title,co1.name to_company_name,co1.logo_img to_company_img,uf.id user_favorite_id')
            ->from('{{%comment}} c')
            ->leftJoin('{{%investor}} i','i.uid = c.comment_by_uid')
            ->leftJoin('{{%company}} co1','co1.uid = c.comment_to_uid')
            ->leftJoin('{{%user_favorite}} uf','uf.comment_trends_id = c.comment_id and uf.user_id='.$uid)
            ->where('c.comment_id='.$id)
            ->asArray()
            ->one();
    }

    public function getTrendsCount($uid){
        return CommentTrends::find()
            ->from('{{%comment_trends}} ct')
            ->leftJoin('{{%comment}} c','c.comment_id=ct.id')
            ->where('find_in_set('.$uid.',ct.uid) and (c.status=1 OR c.status is null)')->count();
    }

    public function getCommentCount($id){
        return Comment::find()->where(['comment_to_uid'=>$id,'status'=>1])->count();
    }

    public function getFollowInvestorCount($uid){
        return Follow::find()->where(['follow_by_uid'=>$uid,'type'=>1])->count();
    }

    public function getFollowCompanyCount($uid){
        return Follow::find()->where(['follow_by_uid'=>$uid,'type'=>2])->count();
    }

    public function addTrends($uid,$img,$content){
        $ct = new CommentTrends();
        $ct->uid = $uid;
        $ct->type = 1;
        $time = date('Y-m-d H:i:s');
        $ct->input_date = $time;
        $ct->save();
        $trends = new Trends();
        $trends->trends_id = $ct->id;
        $trends->uid = $uid;
        $trends->content = $content;
        $trends->img = $img;
        $trends->input_date = $time;
        $trends->save();
    }


}