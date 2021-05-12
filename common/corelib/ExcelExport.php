<?php
/**
 * Created by PhpStorm.
 * User: guoxiang
 * Date: 2017/7/17 0017
 * Time: 17:44
 */

namespace app\common\corelib;
use Yii;

Class ExcelExport
{
    /**
     * Excel 导出
     * @author 郭靖
     * @date 2017/07/17
     * @param array $data 导出数据 - 数组
     * @param array $titles 标题数据 - 数组
     * @param string $sheetName 表格标题
     * @param string $filename 导出的文件名
     * @param array $styles excel表格样式定义
     * @param array $setWidths 表格宽度限制
     * ============ 官网使用手册 =============
     * https://github.com/codemix/yii2-excelexport
     * ======================================
     */
    public static function exportExcel($data, $titles, $sheetName, $filename, $styles=[], $setWidths=[])
    {
        $file = \Yii::createObject([
            'class' => 'codemix\excelexport\ExcelFile',
            'sheets' => [

                $sheetName => [   // Excel Sheet名字
                    'data' => $data, // 数据
                    'titles' => $titles, // 标题配置
                    'styles' => $styles, // 样式配置
                ],
            ],
        ]);
        // 如果传了表格宽度限制
        if (!empty($setWidths)) {
            foreach ($setWidths as $cell=>$width) {
                $file->getWorkbook()->getActiveSheet()->getColumnDimension($cell)->setWidth($width);
            }
        }
        ob_clean();
        // Save on disk
        $file->send($filename.'.xlsx');
    }
}