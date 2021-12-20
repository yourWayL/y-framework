<?php
/**
 * @description 生成 sql
 * @author Holyrisk
 * @date 2020/4/22 11:54
 */

namespace Secxun\Extend\Holyrisk\Sql;

use Secxun\Extend\Holyrisk\Sql\query\Where;

class Build
{

    /**
     * sql 组成
     * @var
     */
    protected $_optionsArr;

    protected $_fetch = 'SELECT';
    protected $_from = 'FROM';

    /**
     * 指定XOR查询条件
     * @access public
     * @param mixed $field     查询字段
     * @param mixed $op        查询表达式
     * @param mixed $condition 查询条件
     * @return $this
     */
    public function whereXor($field, $op = null, $condition = null)
    {
        $param = func_get_args();
        array_shift($param);
        if (empty($param) and is_array($field) == true)
        {
            $this->whereArray('XOR',$field);
            return $this;
        }
        //是否 预处理
        $isPretreatment = isset($this->_optionsArr['isPretreatment']) ? $this->_optionsArr['isPretreatment'] : false;
        $whereObj = new Where();
        if ($isPretreatment == false)
        {
            $result = $whereObj->parseWhereExp('XOR',$field,$op,$condition,$param);
            if (!empty($result))
            {
                $this->_optionsArr['where'][] = $result;
            }
        }
        else
        {
            $result = $whereObj->parseWhereExpPretreatment('XOR',$field,$op,$condition,$param,true);
            if (!empty($result))
            {
                $this->_optionsArr['where'][] = $result['sql'];
                $dataArr = isset($this->_optionsArr['whereArr']) ? $this->_optionsArr['whereArr'] :[];
                $whereArr[] = $result['data'];
                $this->_optionsArr['whereArr'] = array_merge($dataArr,$whereArr);
            }
        }
        return $this;
    }

    /**
     * 指定AND查询条件
     * @access public
     * @param mixed $field     查询字段
     * @param mixed $op        查询表达式
     * @param mixed $condition 查询条件
     * @return $this
     */
    public function whereOr($field, $op = null, $condition = null)
    {
        $param = func_get_args();
        array_shift($param);
        if (empty($param) and is_array($field) == true)
        {
            $this->whereArray('OR',$field);
            return $this;
        }
        //是否 预处理
        $isPretreatment = isset($this->_optionsArr['isPretreatment']) ? $this->_optionsArr['isPretreatment'] : false;
        $whereObj = new Where();
        if ($isPretreatment == false)
        {
            $result = $whereObj->parseWhereExp('OR',$field,$op,$condition,$param);
            if (!empty($result))
            {
                $this->_optionsArr['where'][] = $result;
            }
        }
        else
        {
            $result = $whereObj->parseWhereExpPretreatment('OR',$field,$op,$condition,$param,true);
            if (!empty($result))
            {
                $this->_optionsArr['where'][] = $result['sql'];
                $dataArr = isset($this->_optionsArr['whereArr']) ? $this->_optionsArr['whereArr'] :[];
                $whereArr[] = $result['data'];
                $this->_optionsArr['whereArr'] = array_merge($dataArr,$whereArr);
            }
        }
        return $this;
    }

    /**
     * 指定AND查询条件
     * @access public
     * @param mixed $field     查询字段
     * @param mixed $op        查询表达式
     * @param mixed $condition 查询条件
     * @return $this
     */
    public function where($field, $op = null, $condition = null)
    {
        $param = func_get_args();
        array_shift($param);
        if (empty($param) and is_array($field) == true)
        {
            $this->whereArray('AND',$field);
            return $this;
        }
        //是否 预处理
        $isPretreatment = isset($this->_optionsArr['isPretreatment']) ? $this->_optionsArr['isPretreatment'] : false;
        $whereObj = new Where();
        if ($isPretreatment == false)
        {
            $result = $whereObj->parseWhereExp('AND',$field,$op,$condition,$param);
            if (!empty($result))
            {
                $this->_optionsArr['where'][] = $result;
            }
        }
        else
        {
            $result = $whereObj->parseWhereExpPretreatment('AND',$field,$op,$condition,$param,true);
            if (!empty($result))
            {
                $this->_optionsArr['where'][] = $result['sql'];
                $dataArr = isset($this->_optionsArr['whereArr']) ? $this->_optionsArr['whereArr'] :[];
                $whereArr[] = $result['data'];
//                $this->_optionsArr['whereArr'] = $whereArr;
                $this->_optionsArr['whereArr'] = array_merge($dataArr,$whereArr);
            }
        }
        return $this;
    }

    /**
     * @description 数组处理
     * @author Holyrisk
     * @date 2020/4/24 10:28
     * @param $logic
     * @param array $field
     */
    protected function whereArray($logic,$field = [])
    {
        //数组时候
        if (!empty($field))
        {
            $whereObj = new Where();
            //是否 预处理
            $isPretreatment = isset($this->_optionsArr['isPretreatment']) ? $this->_optionsArr['isPretreatment'] : false;
            foreach ($field as $fkey => $fval)
            {
                if ($isPretreatment == false)
                {
                    //非 预处理
                    switch (count($fval))
                    {
                        case 2:
                            $result = $whereObj->parseWhereExp($logic,$fval[0],'=',$fval[1]);
                            break;
                        case 3:
                            $result = $whereObj->parseWhereExp($logic,$fval[0],$fval[1],$fval[2]);
                            break;
                        default:
                            $result = '';
                            break;
                    }
                    if (!empty($result))
                    {
                        $this->_optionsArr['where'][] = $result;
                    }
                }
                else
                {
                    //预处理
                    switch (count($fval))
                    {
                        case 2:
                            $result = $whereObj->parseWhereExpPretreatment($logic,$fval[0],'=',$fval[1],[],true);
                            break;
                        case 3:
                            $result = $whereObj->parseWhereExpPretreatment($logic,$fval[0],$fval[1],$fval[2],[],true);
                            break;
                        default:
                            $result = '';
                            break;
                    }
                    if (!empty($result))
                    {
                        $this->_optionsArr['where'][] = $result['sql'];
                        $whereArr[] = $result['data'];
                        $this->_optionsArr['whereArr'] = $whereArr;
                    }
                }
            }
        }
    }


    /**
     * @description 设置 操作的表
     * @author Holyrisk
     * @date 2020/4/22 17:45
     * @param $table 操作的表
     * @return $this
     */
    public function table($table)
    {
        $this->_optionsArr['table'] = '`'.trim($table).'`';
        return $this;
    }

    /**
     * @description 指定查询字段 支持字段排除和指定数据表
     * @author Holyrisk
     * @date 2020/4/22 20:11
     * @param string $field 指定查询字段
     * @return $this
     */
    public function field(string $field)
    {
        $this->_optionsArr['field'] = trim($field);
        return $this;
    }

    /**
     * @description 指定查询数量
     * @author Holyrisk
     * @date 2020/4/22 18:11
     * @param int $limitStart
     * @param null $limitLength
     * @return $this
     */
    public function limit($limitStart,$limitLength = null)
    {
        if (is_null($limitLength) && strpos($limitStart, ',')) {
            list($limitStart, $limitLength) = explode(',', $limitStart);
        }
        $limit =intval($limitStart) . ($limitLength ? ',' . intval($limitLength) : '');
        $this->_optionsArr['limit'] = 'LIMIT '.$limit;
        return $this;
    }

    /**
     * @description 指定排序 order('id','desc') 或者 order(['id'=>'desc','create_time'=>'desc']) 【目前只支持  order('id','desc')】
     * @author Holyrisk
     * @date 2020/4/23 14:31
     * @param $field $field 排序字段
     * @param null $orderBy $orderBy 排序 asc | desc
     * @return $this
     * @throws \Exception
     */
    public function order($field, $orderBy = null)
    {
        if (empty($field)) {
            throw new \Exception("order 函数 请设置 排序字段",500);
        }
        if ($orderBy == null)
        {
            $orderBy = 'ASC';
        }
        //非数组 -》 字符串 数字
        if (is_array($field) == false)
        {
            $this->_optionsArr['order'][] = $field.' '.$orderBy;
        }
        return $this;
    }

    /**
     * @description 查询 单条语句
     * @author Holyrisk
     * @date 2020/4/22 18:55
     * @return string
     * @throws \Exception
     */
    public function find()
    {
        $this->checkTable();
        $this->_optionsArr['limit'] = 'LIMIT 1';
        return $this->buildSql('select');
    }

    /**
     * @description 查询 单条语句
     * @author Holyrisk
     * @date 2020/4/22 18:55
     * @return string
     * @throws \Exception
     */
    public function select()
    {
        $this->checkTable();
        return $this->buildSql('select');
    }

    /**
     * 分页
     * @param int $page 页码
     * @param int $pageSize 分页显示条数
     * @return string
     * @throws \Exception
     */
    public function page($page = 1,$pageSize = 10)
    {
        $this->checkTable();
        $start = ($page-1) * $pageSize;
        $limitLength = $pageSize;
        $this->limit($start,$limitLength);
        return $this->buildSql('page');
    }

    /**
     * @description 是否 预处理 | 如果没有 没有设置 预处理 默认 返回 sql | 如果 设置 预处理 true 返回 预处理sql 和 执行 预处理绑定参数
     * @author Holyrisk
     * @date 2020/4/24 14:20
     * @param bool $isPretreatment 是否 预处理 默认 false 不进行预处理
     * @return $this
     */
    public function isPretreatment($isPretreatment = false)
    {
        $this->_optionsArr['isPretreatment'] = $isPretreatment;
        return $this;
    }

    /**
     * @description 真实生成 sql
     * @author Holyrisk
     * @date 2020/4/22 17:50
     * @param string $model 操作方式
     * @return array|string 返回
     */
    protected function buildSql($model = 'select')
    {
        $field = isset($this->_optionsArr['field']) ? $this->_optionsArr['field'] : '*';
        $table = isset($this->_optionsArr['table']) ? $this->_optionsArr['table'] : '';
        $where = isset($this->_optionsArr['where']) ? $this->_optionsArr['where'] : '';
        $limit = isset($this->_optionsArr['limit']) ? $this->_optionsArr['limit'] : '';
        $order = isset($this->_optionsArr['order']) ? $this->_optionsArr['order'] : '';
        //预处理参数
        $whereArr = isset($this->_optionsArr['whereArr']) ? $this->_optionsArr['whereArr'] : [];
        if (!empty($where))
        {
            $where = "WHERE ".ltrim(ltrim(ltrim(implode(' ',$where),'AND'),'OR'),'XOR');
        }
        if (!empty($order))
        {
            $order = 'ORDER BY '.implode(',',$order);
        }
        switch ($model)
        {
            case 'select':
                $sqlArr = array(
                    'select' => $this->_fetch,
                    'field' => $field,
                    'from' => $this->_from,
                    'table' => $table,
                    'where' => $where,
                    'order' => $order,
                    'limit' => $limit,
                );
                $sql = implode( ' ',array_values($sqlArr));
                break;
            case 'page':
                $sqlArr = array(
                    'select' => $this->_fetch,
                    'field' => $field,
                    'from' => $this->_from,
                    'table' => $table,
                    'where' => $where,
                    'order' => $order,
                    'limit' => $limit,
                );
                $sql = implode( ' ',array_values($sqlArr));
                $sqlArrCount = array(
                    'select' => $this->_fetch,
                    'field' => "COUNT('".$field."') as total",
                    'from' => $this->_from,
                    'table' => $table,
                    'where' => $where,
                );
                $sqlCount = implode( ' ',array_values($sqlArrCount));
                $sql = array(
                    'page' => $sql,
                    'count' => $sqlCount,
                );
                break;
            default:
                $sql = '';
                break;
        }
        $sql = $this->removeBlank($sql);
        //是否 预处理
        $isPretreatment = isset($this->_optionsArr['isPretreatment']) ? $this->_optionsArr['isPretreatment'] : false;
        //请空 参数
        $this->close();
        if ($isPretreatment == false)
        {
            return $sql;
        }
        else
        {
            return ['sql' => $sql,'data' => $whereArr];
        }
    }

    //----------------------------------------------------------------------------------------------------
    // 扩展函数
    //----------------------------------------------------------------------------------------------------

    protected function close()
    {
        //清空数据
        unset($this->_optionsArr);
    }

    /**
     * @description 移除 空数据
     * @author Holyrisk
     * @date 2020/4/23 17:03
     * @param $sql
     * @return array|string
     */
    protected function removeBlank($sql)
    {
        if (is_array($sql) == $sql)
        {
            $result = [];
            foreach ($sql as $key => $value)
            {
                $result[$key] = trim($value);
            }
            unset($sql);
            return $result;
        }
        else
        {
            return trim($sql);
        }
    }

    /**
     * @description 直接 返回sql
     * @author Holyrisk
     * @date 2020/4/22 12:05
     * @param $sql
     * @return mixed
     */
    protected function returnString($sql)
    {
        return $sql;
    }

    /**
     * @description 返回 预处理 格式
     * @author Holyrisk
     * @date 2020/4/22 11:59
     * @param $sql 生成的sql
     * @param $data 参数
     * @return array
     */
    protected function returnArray($sql,$data)
    {
        return ['sql' => $sql,'data' => $data];
    }

    //----------------------------------------------------------------------------------------------------
    // 扩展验证
    //----------------------------------------------------------------------------------------------------

    /**
     * @description 验证是否 存在 设置 表
     * @author Holyrisk
     * @date 2020/4/22 18:30
     * @throws \Exception
     */
    protected function checkTable()
    {
        $table = isset($this->_optionsArr['table']) ? $this->_optionsArr['table'] : false;
        if (empty($table))
        {
            throw new \Exception("没有获取到操作的数据表",10001);
        }
    }

}