<?php
/**
 * Created by PhpStorm.
 * User: wjj
 * Date: 2016/11/1
 */

namespace app\logic;


use app\models\Comment;
use app\models\Company;
use app\models\Follow;
use app\models\Investor;

use app\models\User;
use yii;

class InvestorLogic
{
    public function getInvestor($id){
        return Investor::find()
            ->select('i.*,u.status')
            ->from('{{%investor}} i')
            ->leftJoin('{{%user}} u','u.id=i.uid')
            ->where(['i.uid'=>$id])
            ->asArray()
            ->one();
    }

    public function saveFollow($id,$uid){
        if($follow = Follow::findOne(['follow_by_uid'=>$uid,'follow_to_uid'=>$id])){
            $res = $follow->delete();
            return ['res'=>$res,'is_add'=>0];
        }else{
            $follow = new Follow();
            $follow->follow_by_uid = $uid;
            $follow->follow_to_uid = $id;
            $user = User::findOne(['id'=>$id]);
            if($user->type){
                $follow->type = 2;
            }else{
                $follow->type = 1;
            }
            $follow->input_date = date('Y-m-d H:i:s');
            $res = $follow->save();
            return ['res'=>$res,'is_add'=>1];
        }
    }

    public function getCommentCount($id){
        return Comment::find()->where(['comment_by_uid'=>$id])->count();
    }

    public function getFollowInvestorCount($uid){
        return Follow::find()->where(['follow_by_uid'=>$uid,'type'=>1])->count();
    }

    public function getFollowCompanyCount($uid){
        return Follow::find()->where(['follow_by_uid'=>$uid,'type'=>2])->count();
    }

    public function getComment($id,$uid,$skip,$limit){
        return Comment::find()
            ->select('ct.id,ct.input_date input_time,c.*,i.head_img,i.name investor_name,i.title,co1.uid to_company_uid,co1.name to_company_name,co1.logo_img to_company_img,c.content,uf.id user_favorite_id')
            ->from('{{%comment}} c')
            ->leftJoin('{{%comment_trends}} ct','c.comment_id=ct.id')
            ->leftJoin('{{%investor}} i','i.uid = c.comment_by_uid')
            ->leftJoin('{{%company}} co1','co1.uid = c.comment_to_uid')
            ->leftJoin('{{%user_favorite}} uf','uf.comment_trends_id = ct.id and uf.user_id='.$uid)
            ->where(['c.comment_by_uid'=>$id,'c.status'=>1])
            ->offset($skip)
            ->limit($limit)
            ->orderBy('ct.input_date desc')
            ->asArray()
            ->all();
    }

    public function getComment1($id,$uid,$skip,$limit){
        return Comment::find()
            ->select('ct.id,ct.input_date input_time,c.*,i.head_img,i.name investor_name,i.title,co1.name to_company_name,co1.logo_img to_company_img,c.content,uf.id user_favorite_id')
            ->from('{{%comment}} c')
            ->leftJoin('{{%comment_trends}} ct','c.comment_id=ct.id')
            ->leftJoin('{{%investor}} i','i.uid = c.comment_by_uid')
            ->leftJoin('{{%company}} co1','co1.uid = c.comment_to_uid')
            ->leftJoin('{{%user_favorite}} uf','uf.comment_trends_id = ct.id and uf.user_id='.$uid)
            ->where(['c.comment_by_uid'=>$id])
            ->offset($skip)
            ->limit($limit)
            ->orderBy('ct.input_date desc')
            ->asArray()
            ->all();
    }

    public function getFollowCompany($uid,$skip,$limit){
        return Company::find()
            ->select('co.*,i.name industry_name,r1.region_shortname province_name,r2.region_shortname city_name,ca.name market_name')
            ->from('{{%company}} co')
            ->leftJoin('{{%industry}} i', 'i.industry_id=co.industry_id')
            ->leftJoin('{{%category}} ca', 'ca.category_id=co.market_id')
            ->leftJoin('{{%region}} r1', 'r1.region_code=co.province_id')
            ->leftJoin('{{%region}} r2', 'r2.region_code=co.city_id')
            ->leftJoin('{{%follow}} f', 'f.follow_to_uid=co.uid')
            ->where(['f.follow_by_uid'=>$uid,'f.type'=>2])
            ->offset($skip)
            ->limit($limit)
            ->asArray()
            ->all();
    }

    public function getFollowInvestor($uid,$skip,$limit){
        return Investor::find()
            ->select('i.*')
            ->from('{{%investor}} i')
            ->leftJoin('{{%follow}} f', 'f.follow_to_uid=i.uid')
            ->where(['f.follow_by_uid'=>$uid,'f.type'=>1])
            ->offset($skip)
            ->limit($limit)
            ->asArray()
            ->all();
    }

    public function changeComment($comment_id){
        $comment = Comment::findOne(['comment_id'=>$comment_id]);
        if($comment->status){
            $comment->status = 0;
        }else{
            $comment->status = 1;
        }
        return $comment->save();
    }


}