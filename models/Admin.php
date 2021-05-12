<?php
namespace app\models;

/**
* 
*/
class Admin extends BaseModel
{

    public static function getDb()
    {
        return \Yii::$app->db;
    }

}
