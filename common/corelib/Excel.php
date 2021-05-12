<?php
namespace app\common\corelib;
use Yii;
/**
 * 生成excel文件操作
 *
 * @author wesley wu
 * @date 2013.12.9
 */
class Excel
{

    /**
    +----------------------------------------------------------
     * 将EXCEL数据写入_SESSION
    +----------------------------------------------------------
     * @param string $fileName    EXCEL文件名
     * @param string $dataName    SESSION存储名
     * @param string $title       标题
     * @param array $key_arr      标题及数据key
     * example:array(array('key'=>'reg_total','name'=>'注册'),array('key'=>'regPer','name'=>'注册率(%)'))
     * @param array $total        总计
     * @param array $data         数据
     * @param array $action       地址
    +----------------------------------------------------------
     * @return array $excel       用于导出
    +----------------------------------------------------------
     */
    public  function write_excel($fileName, $dataName, $key_arr, $total, $data, $action = 'down'){
        $excel_end='';
        $excel_title='';
        $excel_data='';
        $excel_start = '<table border="1" align="center">';
        $excel_end .= '</table>';

        $colspan = count($key_arr);
        $excel_title .= '<tr align="center"><td colspan="'.$colspan.'"><b>'.$fileName.'</b></td></tr>';
        $excel_head = $excel_total =  '<tr align="center">';
        foreach($key_arr as $kv){
            $key = $kv['key'];
            $name = $kv['name'];
            $type = $kv['type'];
            $excel_head .= "<td><b>$name</b></td>";
            if (array_key_exists($key,$total)) {
                $val = $this->type_process($total[$key], $type);
                $excel_total .= "<td><b>$val</b></td>";
            }
            else
            {
                $excel_total .= "<td></td>";
            }
        }
        $excel_head .= '</tr>';
        $excel_total .= '</tr>';
        if(empty($total))
            $excel_total = '';

        foreach($data as $v){
            $excel_data .= '<tr align="center">';
            foreach($key_arr as $kv){
                $key = $kv['key'];
                $name = $kv['name'];
                $type = $kv['type'];
                $val = $this->type_process($v[$key], $type);
                $excel_data .= "<td>$val</td>";
            }
            $excel_data .= '</tr>';
        }
        $excel_all = $excel_start.$excel_title.$excel_head.$excel_total.$excel_data.$excel_end;
        //$excel_all=mb_convert_encoding($excel_all,"utf-8");
        Yii::$app->session->set($dataName,serialize($excel_all));

        $excel['fileName'] = $fileName;
        $excel['dataName'] = $dataName;
        $excel['dataType'] = 1; //已经整理好的格式，可以直接输出
        $excel['ts']       = time();
        $excel['sign']     = md5('@@weyee@@'.$excel['ts']);
        $excel['url']      = '/tongji/' . $action .'.html?'.http_build_query($excel);

        return $excel;
    }

    /**
    +----------------------------------------------------------
     *根据类型返回处理后的数据
    +----------------------------------------------------------
     * @param simple_mix $val          简单类型数据
     * @param string $type    		   数据类型		exp:int; string; float.2(2代表小数点几位);
    +----------------------------------------------------------
     * @return array $val              处理后的数据
    +----------------------------------------------------------
     */
    private function type_process($val, $type='int'){
        $t = explode('.', $type);
        switch($t[0]){
            case 'string':
                return $val;
            case 'int':
                return round($val+0);
            case  'float':
                $t[1] += 0;
                return round($val, $t[1]);
            default:
                return $val;
        }
    }

}