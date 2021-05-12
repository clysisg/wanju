<?php
namespace app\models;

/**
* 
*/
class Comment extends BaseModel
{

    public static function getDb()
    {
        return \Yii::$app->db;
    }

}
