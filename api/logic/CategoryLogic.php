<?php
/**
 * Created by PhpStorm.
 * User: wjj
 * Date: 2016/11/1
 */

namespace app\logic;


use app\models\Category;
use app\models\Industry;
use app\models\Company;
use yii;

class CategoryLogic
{
    public function getCategory()
    {
        return Category::find()->orderBy('sort')->asArray()->all();
    }

    public function getIndustry(){
        return Industry::find()->orderBy('sort')->asArray()->all();
    }

    public function getCompany($type,$skip,$limit){
        switch($type){
            case 1:
                return Company::find()
                    ->select('co.*,count(c.comment_id) comment_count,i.name industry_name,r1.region_shortname province_name,r2.region_shortname city_name,ca.name market_name')
                    ->from('{{%company}} co')
                    ->leftJoin('{{%user}} u', 'u.id=co.uid')
                    ->leftJoin('{{%comment}} c','c.comment_to_uid=co.uid and c.status=1')
                    ->leftJoin('{{%industry}} i', 'i.industry_id=co.industry_id')
                    ->leftJoin('{{%category}} ca', 'ca.category_id=co.market_id')
                    ->leftJoin('{{%region}} r1', 'r1.region_code=co.province_id')
                    ->leftJoin('{{%region}} r2', 'r2.region_code=co.city_id')
                    ->where('u.status!=0')
                    ->groupBy('co.uid')
                    ->orderBy(' comment_count desc')
                    ->offset($skip)
                    ->limit($limit)
                    ->asArray()
                    ->all();
                break;
            case 2:
            case 3:
            case 4:
            case 5:
                return Company::find()
                    ->select('co.*,i.name industry_name,r1.region_shortname province_name,r2.region_shortname city_name,ca.name market_name')
                    ->from('{{%company}} co')
                    ->leftJoin('{{%user}} u', 'u.id=co.uid')
                    ->leftJoin('{{%industry}} i', 'i.industry_id=co.industry_id')
                    ->leftJoin('{{%category}} ca', 'ca.category_id=co.market_id')
                    ->leftJoin('{{%region}} r1', 'r1.region_code=co.province_id')
                    ->leftJoin('{{%region}} r2', 'r2.region_code=co.city_id')
                    ->where(['ca.category_id'=>$type])
                    ->andWhere('u.status!=0')
                    ->offset($skip)
                    ->limit($limit)
                    ->asArray()
                    ->all();
                break;
        }
    }

    public function getCompanyres($ids,$skip,$limit){
        return Company::find()
            ->select('co.*,i.name industry_name,r1.region_shortname province_name,r2.region_shortname city_name,ca.name market_name')
            ->from('{{%company}} co')
            ->leftJoin('{{%user}} u', 'u.id=co.uid')
            ->leftJoin('{{%industry}} i', 'i.industry_id=co.industry_id')
            ->leftJoin('{{%category}} ca', 'ca.category_id=co.market_id')
            ->leftJoin('{{%region}} r1', 'r1.region_code=co.province_id')
            ->leftJoin('{{%region}} r2', 'r2.region_code=co.city_id')
            ->where(['co.uid'=>$ids])
            ->andWhere('u.status!=0')
            ->offset($skip)
            ->limit($limit)
            ->asArray()
            ->all();
    }
}