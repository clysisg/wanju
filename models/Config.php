<?php
/**
 * Created by PhpStorm.
 * User: flb
 * Date: 2017/6/15
 * Time: 16:22
 */

namespace app\models;


class Config extends BaseModel
{
    public static function getDb()
    {
        return \Yii::$app->db_reborn;
    }

    public static function tableName() {

        return '{{%config}}';
    }
}