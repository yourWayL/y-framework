<?php
namespace Secxun\Extend\Holyrisk\Handle;

use Secxun\Extend\Holyrisk\Handle\SqlWhere;

class SqlCreate
{

    /**
     * @description 生成 查询 单条语句
     * @author Holyrisk
     * @date 2020/4/16 16:32
     * @param string $table 操作的表
     * @param string $field 查询过滤的字段  默认全部 *
     * @param array $whereArr 查询 条件
     * @param bool $isExecute 是否 需要 预处理 默认 true 需要 | false 不需要
     * @return array|string
     */
    public function find($table,$field = '*',$whereArr = [],$isExecute = true)
    {
        $whereObj = new SqlWhere();
        if ($isExecute == true)
        {
            $where = $whereObj->whereAndExecute($whereArr);
            $sql = 'select '.$field.' from `'.$table.'`'.$where['sql'].' limit 1';
            return ['sql' => $sql,'data' => $where['data']];
        }
        else
        {
            $where = $whereObj->whereAnd($whereArr);
            $sql = 'select '.$field.' from `'.$table.'`'.$where.' limit 1';
            return $sql;
        }
    }
    public function select($table,$field = '*',$whereArr = [],$isExecute = true)
    {
        $whereObj = new SqlWhere();
        if ($isExecute == true)
        {
            $where = $whereObj->whereAndExecute($whereArr);
            $sql = 'select '.$field.' from `'.$table.'`'.$where['sql'];
            return ['sql' => $sql,'data' => $where['data']];
        }
        else
        {
            $where = $whereObj->whereAnd($whereArr);
            $sql = 'select '.$field.' from `'.$table.'`'.$where;
            return $sql;
        }
    }

    /**
     * @description 返回 修改语句
     * @author Holyrisk
     * @date 2020/4/17 19:05
     * @param $table 操作的表
     * @param $updateData 要修改的语句
     * @param $whereData 要限制的 where 条件
     * @return string
     */
    public function update($table,$updateData,$whereData)
    {
        $whereObj = new SqlWhere();
        $setSql = $whereObj->updateDataArr($updateData);
        $whereData = $whereObj->whereAnd($whereData);
        //拼接
        $sql = "UPDATE `".$table."` ".$setSql.$whereData;
        return $sql;
    }

}