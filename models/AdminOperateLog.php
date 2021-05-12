<?php
/**
 * Created by PhpStorm.
 * User: wjj
 * Date: 2017/2/14
 */

namespace app\models;


class AdminOperateLog extends BaseModel
{
    public static function getDb()
    {
        return \Yii::$app->db;
    }
}