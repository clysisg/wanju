<?php
namespace app\models;

/**
* 
*/
class Category extends BaseModel
{

    public static function getDb()
    {
        return \Yii::$app->db;
    }

}
