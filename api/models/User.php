<?php
namespace app\models;

/**
* 
*/
class User extends BaseModel
{

    public static function getDb()
    {
        return \Yii::$app->db;
    }

}
