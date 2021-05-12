<?php
namespace app\models;

/**
* 
*/
class Company extends BaseModel
{

    public static function getDb()
    {
        return \Yii::$app->db;
    }

}
