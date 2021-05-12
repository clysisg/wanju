<?php
function pr()
{
    $args = func_get_args();
    echo '<pre>';
    foreach($args as $v){
        print_r($v);
    }
    echo '</pre>';
    exit;
}

function vp()
{
    $args = func_get_args();
    echo '<pre>';
    foreach($args as $v){
        var_dump($v);
    }
    echo '</pre>';
    exit;
}

function U($params)
{
    $url = \yii::$app->urlManager->createUrl($params);
    return $url;
}

function jump($params)
{
    $url = U($params);
    header("Location: $url");
    exit;
}

function sqlSetStr($data)
{
    if(!is_array($data) || empty($data)) return false;
    $str = '';
    foreach($data as $key=>$val)
    {
        $str .= $key . "='".$val ."',";
    }
    return substr($str,0,-1);
}

/**
 * 2017-06-20 任我行添加
 * 提取数组元素的某个键值或属性值作为键名
 * @param array  $arr  传入数组
 * @param string $key  对应的键或属性名称
 * @return array
 */

function changeArrayKey(&$arr, $key) {
    $res = [];
    foreach ($arr as $val) {
        //判断元素是数组还是对象
        if (is_array($val)) {
            $res[$val[$key]] = $val;
        } elseif (is_object($val)) {
            $res[$val->$key] = $val;
        }
    }
    $arr = $res;
}