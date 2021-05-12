<?php
namespace app\models;

/**
* 
*/
class Article extends BaseModel
{

    public static function getDb()
    {
        return \Yii::$app->db;
    }

}
