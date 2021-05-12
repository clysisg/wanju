<?php
/**
 * Created by PhpStorm.
 * User: wjj
 * Date: 2017/2/14
 */

namespace app\common\logic;

use app\models\AdminOperateLog;
use yii;

class AdminOperateLogLogic
{
    CONST ADD = '添加';
    CONST EDIT = '编辑';
    CONST DELETE = '删除';

    public static function log($title, $model, $type, $handle_id, $result = '', $describe = '', $sql_log='')
    {
        $adminOperateLog = new AdminOperateLog();
        $userinfo = Yii::$app->session->get('user');
        if ($userinfo) {
            $adminOperateLog->admin_id = $userinfo['uid'];
            $adminOperateLog->admin_name = $userinfo['username'];
            $adminOperateLog->admin_ip = Yii::$app->request->userIP;
            $adminOperateLog->admin_agent = Yii::$app->request->userAgent;
        }
        $adminOperateLog->add_date = date('Y-m-d H:i:s');
        $adminOperateLog->title = $title; // 操作标题
        $adminOperateLog->model = $model; // 操作所在模型
        $adminOperateLog->type = $type; // 操作类型
        $adminOperateLog->handle_id = $handle_id; // 数据表ID
        $adminOperateLog->result = $result; // 操作结果
        $adminOperateLog->describe = $describe; // 操作描述
        $adminOperateLog->sql_log = $sql_log; // SQL语句记录
        $adminOperateLog->save();
    }
}