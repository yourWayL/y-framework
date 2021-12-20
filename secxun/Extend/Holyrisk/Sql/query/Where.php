<?php
/**
 * @description Where 处理
 * @author Holyrisk
 * @date 2020/4/23 14:14
 */

namespace Secxun\Extend\Holyrisk\Sql\query;


class Where
{

    /**
     * @description where 条件 重组
     * @param $logic XOR | AND | OR
     * @param $field 查询字段
     * @param $op 查询表达式
     * @param $condition 查询条件
     * @param $param 非字段 参数
     * @return string
     */
    public function parseWhereExp($logic,$field,$op,$condition,$param = [])
    {

        if (count($param) == 1)
        {
            $condition = $param[0];
            $op = '=';
        }
        //数字 时候 单独出来
        if (is_numeric($condition) == false)
        {
            $condition = "'".$condition."'";
        }
        //拼接
        $result = $logic.' `'.$field.'` '.$op.' '.$condition;
        return $result;
    }

    /**
     * where 条件 重组 | 预处理
     * @param $logic
     * @param $field
     * @param $op
     * @param $condition
     * @param array $param
     * @param bool $isPretreatment 是否 是预处理 默认 否 false
     * @return array
     */
    public function parseWhereExpPretreatment($logic,$field,$op,$condition,$param = [],$isPretreatment = false)
    {
        if (count($param) == 1)
        {
            $condition = $param[0];
            $op = '=';
        }
        if ($isPretreatment == false)
        {
            //数字 时候 单独出来
            if (is_numeric($condition) == false)
            {
                $condition = "'".$condition."'";
            }
        }
        //拼接
        $sql = $logic.' `'.$field.'` '.$op.' ?';
        ;
        $result = array(
            'sql' => $sql,
            'data' => $condition,
        );
        return $result;
    }


}