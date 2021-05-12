<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/24 0024
 * Time: 16:33
 */

namespace app\controllers;

use yii;
use yii\web\Controller;
use yii\web\Response;

class BaseController extends Controller
{
    public $enableCsrfValidation = false;
    public $data = array(
        'status' 	=> 1,
        'error' 	=> array('errorno' => 0, 'errormsg' => 'success'),
        'data'		=> array()
    );
    /**
     *  返回成功json数据
     * @param array $data
     * @param integer $stauts
     * @return array
     */
    public  function response($data = null, $stauts = 1)
    {
        if($data!=null) $this->data[ 'data' ] = $data;
        echo json_encode($this->data);
        exit;
    }

    /**
     * 返回错误信息
     * @param string $no 状态码
     * @param string $msg 错误信息
     * @return array
     */
    protected function error($no='00001',$msg='一般错误')
    {
        $this->data['status'] = 0;
        $this->data[ 'error' ] = array( 'errorno' => $no, 'errormsg' => $msg, );
        return $this->response(null, 0);
    }
}