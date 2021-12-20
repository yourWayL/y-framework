<?php
/**
 * @description 处理 where 条件
 * @author Holyrisk
 * @date 2020/4/16 16:09
 */

namespace Secxun\Extend\Holyrisk\Handle;


class SqlWhere
{
    /**
     * 预定义 是否在前面 加 where | 默认 加
     * @var string
     */
    protected $_where = '';
    protected $_set_update = '';

    /**
     * @description 初始化
     * @author Holyrisk
     * @date 2020/4/16 16:31
     * SqlWhere constructor.
     * @param bool $isInit
     */
    public function __construct($isInit = true)
    {
        if ($isInit == true)
        {
            $this->_where = ' where ';
            $this->_set_update = 'set ';
        }
    }

    /**
     * @description  参数数组 转换为 sql 条件处理 | and 条件 | 没有使用预处理 参数绑定
     * @author Holyrisk
     * @date 2020/4/16 16:31
     * @param $whereArr
     * @return string
     */
    public function whereAnd($whereArr)
    {
        $result = $this->_where;
        if (!empty($whereArr))
        {
            $and = '';
            foreach ($whereArr as $key => $value)
            {
                if (is_array($value))
                {
                    /**
                     * 条件转换
                     */
                    switch (count($value))
                    {
                        case 2:
                            //['>','asdas']
                            $and .= "and `".$key."` ".$value[0]." '".$value[1]."' ";
                            break;
                        default:
                            //case 1:
                            //['name']
                            $and .= "and `".$key."` = '".$value[0]."' ";
                            break;
                    }
                }
                else
                {
                    $and .= "and `".$key."` = '".$value."' ";
                }
            }
            $result .= ltrim($and,'and');
        }
        return $result;
    }

    /**
     * @description 参数数组 转换为 sql 条件处理 | and 条件 | 预处理 参数绑定
     * @author Holyrisk
     * @date 2020/4/16 17:15
     * @param $whereArr
     * @return array
     */
    public function whereAndExecute($whereArr)
    {
        $result = array(
            'sql' => $this->_where,
            'data' => [],
        );
        if (!empty($whereArr))
        {
            $sql = $this->_where;
            //预处理 语句
            $and = '';
            //预处理 绑定参数
            $andArr = [];
            //预处理 绑定下标
            $i = 0;
            foreach ($whereArr as $key => $value)
            {
                $i++;
                if (is_array($value))
                {
                    /**
                     * 条件转换
                     */
                    switch (count($value))
                    {
                        case 2:
                            //['>','asdas']
                            //关联 key | 占位符
                            //sql
                            $and .= "and `".$key."` ".$value[0]." ? ";
                            //关联数据
                            $andArr[$i] = $value[1];
                            break;
                        default:
                            // case 1:
                            //['name']
                            //sql
                            $and .= "and `".$key."` = ? ";
                            //关联数据
                            $andArr[$i] = $value[0];
                            break;
                    }
                }
                else
                {
                    //关联 key | 占位符
                    //sql
                    $and .= "and `".$key."` = ? ";
                    //关联数据
                    $andArr[$i] = $value;
                }
            }
            $sql .= ltrim($and,'and');
            $result['sql'] = $sql;
            $result['data'] = $andArr;
        }
        return $result;
    }


    /**
     * @description 修改语句
     * @author Holyrisk
     * @date 2020/4/17 18:59
     * @param $updateArr
     * @return string
     */
    public function updateDataArr($updateArr)
    {
        $result = $this->_set_update;
        if (!empty($updateArr))
        {
            $sql = '';
            foreach ($updateArr as $key => $value)
            {
                $sql .= "`".$key."` = '".$value."',";
            }
            $result .= rtrim($sql,',');
        }
        return $result;
    }

}