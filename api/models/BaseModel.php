<?php
namespace app\models;

use yii;
use yii\db\ActiveRecord;
use yii\db\Query;

class BaseModel extends ActiveRecord {

	/**
	 * 初始化
	 */
	public function init() {
		
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
     * 批量更新数据
     * @param array $data 更新的数据
     * @param string $condition 更新条件,e.g.:['a'=>'b','c'=>'d']
     * @return int mixed 影响记录数
     */
    function modifyBatch($data,$condition)
    {
        return static::updateAll($data,$condition);
    }

    /**
     * 批量插入数据
     * @param array $fields 插入的字段,e.g.:['a','b']
     * @param array $data 插入的数据[['a'=>'x1','b'=>'x2'],['a'=>'x3','b'=>'x4']]
     * @return int mixed 影响记录数
     */
    function addBatch($fields,$data,$db)
    {
        // INSERT 一次插入多行
        return $db->createCommand()->batchInsert(static::tableName(), $fields,$data)->execute();
    }


}