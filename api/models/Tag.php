<?php
namespace app\models;

/**
* 
*/
class Tag extends BaseModel
{

    public static function getDb()
    {
        return \Yii::$app->db;
    }

}
