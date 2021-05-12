<?php
namespace app\models;

/**
* 
*/
class UserFavorite extends BaseModel
{

    public static function getDb()
    {
        return \Yii::$app->db;
    }

}
