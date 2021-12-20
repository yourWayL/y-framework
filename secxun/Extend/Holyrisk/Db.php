<?php
/**
 * @description 数据库 pdo 操作 类 因为已经存在一个  这个 用来 跑 command命令  类似 tp 操作 操作多个数据库
 * @author Holyrisk
 * @date 2019/5/28 16:02
 */

namespace Secxun\Extend\Holyrisk;

use Exception;
use PDO;

class Db
{
    //--------------------------------------------------------------------------------------------------
    // 配置项
    //--------------------------------------------------------------------------------------------------
    /**
     * @description 默认配置
     * @author Holyrisk
     * @date 2019/5/5 15:37
     * @var array
     */
    protected static $db_config = array(
        // 服务器地址
        'host' => '127.0.0.1',
        //数据库名
        'database' => 'weixin',
        //用户名
        'username' => 'wshx',
        //密码
        'password' => 'secxun@2019X',
        //端口
        'port' => 3306,
        //字符集
        'charset' => 'utf8'
    );

    //框架配置文件 信息缓存 避免 第二次引用加载异常
    protected static $fram_config = [];

    protected static $transActive = false;

    public $link;//连接
    //sql 链式操作
    //操作的数据表
    protected $table = null;
    // 要过滤的 字段 如select * 或者 select $field
    protected $field = '*';
    //where 条件 and
    protected $where = null;
    //where 条件 or
    protected $whereOr = null;
    //order
    protected $order = null;
    //group
    protected $group = null;
    //limit
    protected $limit = null;
    //预处理 数据 参数 绑定  :参数=>参数值
    protected $execute = [];
    //PDO 获取数据方式参数
    protected $pdo_mode = array(
        //默认
        'default' => null,
        //显示全部字段
        'all_field' => PDO::FETCH_ASSOC,
    );
    //表达式 exp 数组 用作 判断 表达式 是否合法
    protected $exp = array(
        //数字
        '=',
        '!=',
        '>',
        '<',
        '>=',
        '=>',
        '=<',
        '<=',
        '<>',
        '><',
        //英文
        'like',
        'LIKE',
        'is',// is null is not null
        'IS',
        'in',
        'IN',
    );
    //用作区分 字段条件表达式  参数绑定
    protected $exp_where = array(
        '=' => 'eq',//等于
        '>=' => 'gte',// 大于或等于 （greater than or equal to）
        '<=' => 'lte',// 小于或等于（less than or equal to）
        '>' => 'gt',// 大于（greater than）
        '<' => 'lt',// 小于（less than）
        '<>' => 'isrange',//在范围内
        '><' => 'notrange',//不在范围内
        '!=' => 'noteq',//不等于
    );
    //直接返回SQL而不是执行查询，适用于任何的CURD操作方法 默认是 false
    protected $fetch_sql = false;
    //开启 sql 错误异常输出
    protected $errorSql = false;
    //需要销毁参数
    protected $destruction = array('table','field','where','whereOr','order','group','limit','execute','fetch_sql','errorSql');

    //--------------------------------------------------------------------------------------------------
    // 核心功能 【不会改变】
    // 注释 ：PDO::prepare  如果成功，PDO::prepare()返回PDOStatement对象，如果失败返回 FALSE 或抛出异常 PDOException 。
    //--------------------------------------------------------------------------------------------------
    /**
     * @description 默认
     * @author Holyrisk
     * @date 2019/5/5 15:49
     * Db constructor.
     * @param array $db_config 数据库配置
     * @param bool $mode 是否是长连接 默认 false , true 为 持久化连接 【如果是 脚本长时间运行  model 为 false ，请手动 close 断开 数据库连接 ，因为 pdo 连接的生命周期是脚本结束 会自动断开连接 否则 会出现 超时】
     * @throws Exception
     */
    public function __construct(array $db_config = [],$mode = false)
    {
        if (empty($db_config)){
            $mysql_config = $this->getConfig();
            //获取 框架默认的配置
            if (empty($mysql_config)){
                //如果没有配置参数  默认 类库自己的类库
                $link = $this->connect([],$mode);
            }else{
                $link = $this->connect($mysql_config,$mode);
            }
        }else{
            //多数据库 转入  不止 默认数据库
            $link = $this->connect($db_config,$mode);
        }
        $this->link = $link;
        unset($link);
    }

    /**
     * @description 连接数据库
     * @author Holyrisk
     * @date 2019/5/5 15:27
     * @param array $db_config
     * @param bool $mode 连接方式  默认 false、 true 为 持久化连接
     * @return PDO
     * @throws Exception
     */
    protected function connect(array $db_config = [],$mode = false)
    {
        $db_config = $this->unsetArrayEmptey($db_config);
        if (!empty($db_config))
        {
            foreach ($db_config as $key => $value){
                //$this->db_config[$key] = $value;
                self::$db_config[$key] = $value;
            }
        }

        $host =  self::$db_config['host'];
        $database =  self::$db_config['database'];
        $username =   self::$db_config['username'];
        $password =   self::$db_config['password'];
        $port =  self::$db_config['port'];
        $charset =  self::$db_config['charset'];
        try{
            if ($mode == false){
                $dbh = new PDO('mysql:host='.$host.';dbname='.$database.';port='.$port.';charset='.$charset, $username, $password);
            }else{
                //持久化连接 在脚本结束后不会被关闭，且被缓存，当另一个使用相同凭证的脚本连接请求时被重用。
                //持久连接缓存可以避免每次脚本需要与数据库回话时建立一个新连接的开销，从而让 web 应用程序更快。
                $dbh = new PDO('mysql:host='.$host.';dbname='.$database, $username, $password, array(
                    PDO::ATTR_PERSISTENT => true
                ));
            }
        }catch (Exception $exception){
            //一般情况不会抛出 异常 但 会出现  mysql 并发量很高 客户端 连接 量 过大 【脚本运行】  所以 这里做一下 睡眠 错误说法
            //实际 要检查这个 db 连接 是否是 长连接  还有 部署的 服务器 有没有设置 长连接 回收 0 改成 1
            //sleep(1);
            $dbh = new PDO('mysql:host='.$host.';dbname='.$database.';port='.$port.';charset='.$charset, $username, $password);
//            var_dump($database);
//            var_dump($username);
//            var_dump($password);
//            throw new Exception($exception->getMessage().PHP_EOL.$exception->getCode());
        }
        return $dbh;
    }



    /**
     * @description 获取框架默认配置文件
     * @author Holyrisk
     * @date 2019/5/28 15:35
     * @return array|string
     * @throws \Exception
     */
    protected function getConfig()
    {
        $mysql_config = [];
        if (empty(self::$fram_config)){
            $path = dirname(dirname(__DIR__)) . '/config/database.php';
            if (is_file($path)){
                $config = require $path;//不能使用 require_one
                if (is_array($config) and array_key_exists('mysql_config',$config)){
                    //默认设置  是一个 mysql_config 数组
                    $mysql_config = $config['mysql_config'];
                    self::$fram_config = $config['mysql_config'];
                }
            }
        }else{
            $mysql_config = self::$fram_config;
        }
        return $mysql_config;
    }

    /**
     * @description 获取数据表的全部字段信息
     * @author Holyrisk
     * @date 2019/5/6 17:21
     * @param $table 要获取的 表名称
     * @param bool $mode 方式 是否返回多维数组  默认 false
     * @return array
     */
    public function getTableField($table,$mode = false)
    {
        $sql = "DESC `$table`";
        $sth = $this->link->prepare($sql);
        $sth->execute();
        if ($mode == false){
            $result = $sth->fetchAll(PDO::FETCH_COLUMN,0);
        }else{
            $result = $sth->fetchAll($this->pdo_mode['all_field']);
        }
        return $result;
    }

    /**
     * @description
     * 连接数据成功后，返回一个 PDO 类的实例给脚本，此连接在 PDO 对象的生存周期中保持活动。
     * 要想关闭连接，需要销毁对象以确保所有剩余到它的引用都被删除，可以赋一个 NULL 值给对象变量。
     * 如果不这么做，PHP 在脚本结束时会自动关闭连接。
     * @author Holyrisk
     * @date 2019/5/15 15:51
     */
    public function close()
    {
        $this->link = null;
    }

    /**
     * @description 销毁参数 避免多次调用 上一次 参数 存在
     * @author Holyrisk 修改  李有为 做的
     * @date 2019/5/18 14:27
     */
    protected function destructionInit(){
        $destruction = $this->destruction;
        foreach ($destruction as $v){
            if($v == 'field'){
                $this->$v = '*';
            }elseif($v == 'execute'){
                $this->$v = [];
            }else{
                if(!empty($this->$v)){
                    $this->$v = null;
                }
            }
        }
    }

    //--------------------------------------------------------------------------------------------------
    // PDO 功能 拼接sql 和 参数绑定
    // 注释 ：PDO::prepare  如果成功，PDO::prepare()返回PDOStatement对象，如果失败返回 FALSE 或抛出异常 PDOException 。
    //--------------------------------------------------------------------------------------------------

    /**
     * @description 设置 数据表
     * @author Holyrisk
     * @date 2019/5/7 17:59
     * @param $table 要操作的数据表
     * @return $this
     */
    public function table($table)
    {
        $this->table = $table;
        unset($table);
        return $this;
    }

    /**
     * @description limit 函数
     * @author Holyrisk
     * @date 2019/5/7 19:03
     * @param $start 获取范围 或者 开始
     * @param int $end 结束
     * @return $this|bool
     */
    public function limit($start,$end = 0)
    {

        if (!is_numeric($start) or !is_numeric($end)){
            return false;
        }else{
            $limit[] = $start;
            if (!empty($end)){
                $limit[] = $end;
            }
            $this->limit = ' LIMIT '.implode(',',$limit);
            unset($limit);
            return $this;
        }
    }

    /**
     * @description 处理 预处理参数
     * @author Holyrisk
     * @date 2019/5/7 19:20
     * @return array
     */
    protected function getExecute()
    {
        $exec = $this->execute;
        $arr = [];
        foreach ($exec as $key => $value){
            foreach ($value as $k => $v){
                $arr[$k] = $v;
            }
        }
        return $arr;
    }

    /**
     * @description 过滤 显示 字段
     * @author Holyrisk
     * @date 2019/5/7 19:35
     * @param string $field 要过滤的字段  默认 显示 全部
     * @return $this
     */
    public function field($field = '*')
    {
        $this->field = $field;
        return $this;
    }

    /**
     * @description ORDER BY 函数
     * @author Holyrisk
     * @date 2019/5/7 19:51
     * @param $field 要排序的字段
     * @param string $model 方式  默认 asc 降序 ，升序 desc
     * @return $this
     */
    public function order($field,$model = 'asc')
    {
        if (empty($field)) return $this;
        $this->order[] = $field.' '.$model;
        return $this;
    }

    /**
     * @description 获取 order 参数
     * @author Holyrisk
     * @date 2019/5/7 19:52
     * @return string|null
     */
    protected function getOrder()
    {
        $order = $this->order;
        if (!empty($order)){
            $order = ' ORDER BY '.implode(',',$order);
        }
        return $order;
    }

    /**
     * @description GROUP BY 函数
     * @author Holyrisk
     * @date 2019/5/7 20:20
     * @param $field 支持 多个  多个使用 逗号 隔开
     * @return $this
     */
    public function group($field)
    {
        $this->group = $field;
        return $this;
    }

    /**
     * @description 直接返回SQL而不是执行查询，适用于任何的CURD操作方法
     * @author Holyrisk
     * @date 2019/5/8 11:16
     * @param bool $mode 方式 默认 false
     * @return $this
     */
    public function fetchSql($mode = false)
    {
        $this->fetch_sql = $mode;
        return $this;
    }

    /**
     * @description 执行sql操作 如果sql 异常 抛出 异常信息 而不是 返回 false
     * @author Holyrisk
     * @date 2019/7/4 17:27
     * @param bool $model 方式 默认 false
     * @return $this
     */
    public function errorSql($model = false)
    {
        $this->errorSql = $model;
        return $this;
    }

    /**
     * @description where 条件
     * @author Holyrisk
     * @date 2019/5/7 16:20
     * @param $field 字段
     * @param $exp 表达式
     * @param $value 值
     * @return $this
     */
    public function where($field,$exp,$value)
    {
        $where = $this->where;//整个条件
        //条件 表达式
        $exp = $this->isExp($exp);//过滤exp
        //用作区分 字段条件表达式  参数绑定
        $exp_where = $this->setExpWhere($exp);
        $sql = '`'.$field.'` '.$exp.' '.':'.$field.$exp_where;
        //预处理 参数数据
        $exp_data = array(
            ':'.$field.$exp_where => $value,
        );
        $this->execute[] = $exp_data;
        unset($exp_data);
        if (empty($where)){
            $where = " WHERE ".$sql.' ';
        }else{
            $where .= " AND ".$sql.' ';
        }
        //重新赋值
        $this->where = $where;
        unset($where);
        return $this;
    }

    /**
     * @description 数组 where 条件操作
     * @author Holyrisk
     * @date 2019/7/8 17:07
     * @param $whereArray 多维数组 下标格式
     * [
     * ['字段','表达式','字段值'],
     * ['字段','字段值'],//默认等于 =
     * ]
     * @return $this
     */
    public function whereArray($whereArray)
    {
        if (empty($whereArray) or !is_array($whereArray)) return $this;
        foreach ($whereArray as $key => $value)
        {
            if (!is_array($value) and empty($value)) return $this;
            switch (count($value))
            {
                case 3:
                    $this->where($value[0],$value[1],$value[2]);
                    break;
                case 2:
                    $this->where($value[0],'=',$value[1]);
                    break;
                default :
                    break;
            }
        }
        return $this;
    }


    /**
     * @description where 条件
     * @author Holyrisk
     * @date 2019/5/7 16:20
     * @param $field 字段
     * @param $exp 表达式
     * @param $value 值
     * @return $this
     */
    public function whereOr($field,$exp,$value)
    {
        $where = $this->where;//整个条件 and
        //条件 表达式
        $exp = $this->isExp($exp);//过滤exp
        //用作区分 字段条件表达式  参数绑定
        $exp_where = $this->setExpWhere($exp);
        $sql = '`'.$field.'` '.$exp.' '.':'.$field.$exp_where;
        //预处理 参数数据
        $exp_data = array(
            ':'.$field.$exp_where => $value,
        );
        $this->execute[] = $exp_data;
        if (empty($where)){
            $where = " WHERE ".$sql.' ';
        }else{
            $where .= " OR ".$sql.' ';
        }
        //重新赋值
        $this->where = $where;
        return $this;
    }

    //--------------------------------------------------------------------------------------------------
    // 基础功能 增删改查
    // 注释 ：PDO::prepare  如果成功，PDO::prepare()返回PDOStatement对象，如果失败返回 FALSE 或抛出异常 PDOException 。
    //--------------------------------------------------------------------------------------------------

    /**
     * @description 获取全部数据
     * @author Holyrisk
     * @date 2019/5/5 16:00
     * @return array
     */
    public function select()
    {
        //数据表
        $table = $this->table;
        $field = $this->field;
        $limit = $this->limit;
        $order = $this->getOrder();
        //where 条件
        $where = $this->where;
        $execute = $this->getExecute();
        $sql = 'SELECT '.$field.' FROM `'.$table.'` '.$where.$order.$limit.';';
        //执行
        $result = $this->query($sql,$execute,'select');
        return $result;
    }

    /**
     * @description 获取一条记录
     * @author Holyrisk
     * @date 2019/5/10 9:54
     * @return array
     */
    public function find()
    {
        //数据表
        $table = $this->table;
        $field = $this->field;
        $limit = $this->limit;
        $order = $this->getOrder();
        //where 条件
        $where = $this->where;
        $execute = $this->getExecute();
        $sql = 'SELECT '.$field.' FROM `'.$table.'` '.$where.$order.$limit.';';
        //执行
        $result = $this->query($sql,$execute,'find');
        unset($sql);
        return $result;
    }

    /**
     * @description 批量添加数据
     * @author Holyrisk
     * @date 2019/5/15 13:39
     * @param array $insert_data 多维的 关联数组
     * @return array|bool
     */
    public function insertAll(array $insert_data)
    {
        if (empty($insert_data)) return false;
        //获取要添加数据的 表字段
        $table = $this->table;
        $field = $this->getTableField($table,false);
        //获取 插入数据 key
        $key_arr = [];
        foreach ($insert_data as $key => $value){
            //获取多维数组 的 单个数组的 key
            if (is_array($value)){
                $insert_key_arr = array_keys($value);
                //设置 插入的数据 字段
                foreach ($insert_key_arr as $k => $v){
                    if (in_array($v,$field)){
                        $key_arr[] = $v;
                    }
                }
            }else{
                //不是多维数组
                $key_arr = false;
            }
            break;
        }
        if (empty($key_arr)) return false;
        //真实插入的 数据
        $insert_arr_execute = [];
        //拼接 插入的 多维数组 value 预处理
        $insert_value = [];
        foreach ($insert_data as $key => $value){
            //真实的 插入数据
            $insert_value_arr = [];
            foreach ($value as $k => $v){
                if (in_array($k,$key_arr)){
                    $execute_key = ':field'.$key.$k;
                    $insert_arr_execute[$execute_key] = $v;
                    $insert_value_arr[$k] = $execute_key;
                    /**
                    if (is_numeric($v)){
                    $insert_value_arr[$k] = $v;
                    }else{
                    $insert_value_arr[$k] = "'".$v."'";
                    }
                     * */
                }
            }
            $field_value = implode(',',$insert_value_arr);
            unset($insert_value_arr);
            $insert_value[] = '('.$field_value.')';
            unset($field_value);
        }
        foreach ($key_arr as $kf => $vf)
        {
            $key_arr[$kf] = "`".$vf."`";
        }
        //真实的插入 字段
        $field_string = implode(',',$key_arr);
        unset($key_arr);
        $field_value = implode(',',$insert_value);
        unset($insert_value);
        $sql = "INSERT INTO `$table`(".$field_string.") VALUES $field_value;";
        unset($field_value);
        unset($field_string);
        //执行
        $result = $this->query($sql,$insert_arr_execute,'insert');
        unset($sql);
        unset($insert_arr_execute);
        return $result;
    }

    /**
     * @description 添加数据
     * @author Holyrisk
     * @date 2019/5/10 10:03
     * @param array $inser_data 关联数组
     * @return array|bool
     */
    public function insert(array $inser_data)
    {
        if (empty($inser_data)) return false;
        //获取要添加数据的 表字段
        $table = $this->table;
        $field = $this->getTableField($table,false);
        //获取 插入数据 key
        $key_arr = [];
        //获取 插入数据 value 预处理
        $value_arr = [];
        //真实插入的 数据
        $insert_arr = [];
        foreach ($inser_data as $key => $value){
            if (in_array($key,$field)){
                $key_arr[] = '`'.$key.'`';
                $vk_key = ':'.$key;
                $value_arr[] = $vk_key;
                //判断是否是数字 处理
                $insert_arr[$vk_key] = $value;
            }
        }
        unset($inser_data);
        unset($field);
        //操作的字段  为空  非法
        if (empty($key_arr)) return false;
        //数组 转 字符串
        $field_string = implode(',',$key_arr);
        $field_value = implode(',',$value_arr);
        unset($key_arr);
        unset($value_arr);
        $sql = "INSERT INTO `$table`(".$field_string.") VALUES (".$field_value.");";
        //执行
        $result = $this->query($sql,$insert_arr,'insert');
        unset($insert_arr);
        // 返回上一个由对应的 PDOStatement 对象执行DELETE、 INSERT、或 UPDATE 语句受影响的行数。
        //$count = $sth->rowCount();
        return $result;
    }

    /**
     * @description 修改 更新数据
     * @author Holyrisk
     * @date 2019/5/17 13:58
     * @param array $update_data
     * @return array|bool
     */
    public function update(array $update_data)
    {
        if (empty($update_data)) return false;
        //获取要添加数据的 表字段
        $table = $this->table;
        $field = $this->getTableField($table,false);
        //where 条件
        $where = $this->where;
        //真实插入的 数据 含 where 条件 和 update 的真实数据
        $execute = $this->getExecute();
        //获取 更新数据 value 预处理
        $update_arr = [];
        //更新的数据
        foreach ($update_data as $key => $value){
            if (in_array($key,$field)){
                $vk_key = ':'.$key;
                //更新的字段
                $update_arr[] = '`'.$key.'` = '.$vk_key;
                //真实插入的 数据 含 where 条件 和 update 的真实数据
                $execute[$vk_key] = $value;
            }
        }
        if (empty($update_arr)) return false;
        $update_value = implode(',',$update_arr);
        $sql = 'UPDATE `'.$table.'` SET '.$update_value.$where.';';
        //执行
        $result = $this->query($sql,$execute,'update');
        return $result;
    }

    /**
     * @description 删除 真实删除
     * @author Holyrisk
     * @date 2019/5/18 17:03
     * @return array
     */
    public function delete()
    {
        //数据表
        $table = $this->table;
        $limit = $this->limit;
        $order = $this->getOrder();
        //where 条件
        $where = $this->where;
        $execute = $this->getExecute();
        $sql = 'DELETE  FROM `'.$table.'` '.$where.$order.$limit.';';
        //执行
        $result = $this->query($sql,$execute,'delete');
        return $result;
    }

    /**
     * @description 执行sql语句
     * @author Holyrisk
     * @date 2019/5/9 13:25
     * @param $sql 执行的sql 语句
     * @param array $execute 绑定的 参数
     * @param string $model 默认执行原始 sql 语句
     * @return array
     */
    public function query($sql,array $execute = [],$model = 'query')
    {
        try{
            //是否开启 sql 调试 操作
            if ($this->fetch_sql == false){
                $sth = $this->link->prepare($sql);
                if (empty($execute))
                {
                    $execute = [];
                }
                try{
                    $exec = $sth->execute($execute);
                }catch (Exception $exception)
                {
                    var_dump($exception->getMessage());
                    var_dump($execute);
                    var_dump($sql);
                    die();
                }
                switch ($model){
                    case 'select':
                        /* 获取结果集  全部 */
                        $result = $sth->fetchAll($this->pdo_mode['all_field']);
                        break;
                    case 'find':
                        /* 获取结果集  一条记录 */
                        $result = $sth->fetch($this->pdo_mode['all_field']);
                        break;
                    case 'insert':
                        //返回上一个由对应的 PDOStatement 对象执行DELETE、 INSERT、或 UPDATE 语句受影响的行数。
                        $result = $sth->rowCount();
                        break;
                    case 'update':
                        //返回上一个由对应的 PDOStatement 对象执行DELETE、 INSERT、或 UPDATE 语句受影响的行数。
                        $result = $sth->rowCount();
                        break;
                    case 'delete':
                        //返回上一个由对应的 PDOStatement 对象执行DELETE、 INSERT、或 UPDATE 语句受影响的行数。
                        $result = $sth->rowCount();
                        break;
                    case 'query':
                        /* 获取结果集  全部 */
                        $result = $sth->fetchAll($this->pdo_mode['all_field']);
                        break;
                    default:
                        //返回上一个由对应的 PDOStatement 对象执行DELETE、 INSERT、或 UPDATE 语句受影响的行数。
                        $result =$exec;
                        break;
                }
                if ($this->errorSql == true)
                {
                    if ($result == false)
                    {
                        $result = array(
                            'code' => $sth->errorCode(),
                            'info' => $sth->errorInfo(),
                            'result' => $exec,
                        );
                    }
                }

            }else{
                $result = array(
                    'sql' => $sql,
                    'execute' => $execute
                );
            }
        }catch (Exception $exception){
            $result = $exception->getMessage();
        }
        //销毁 参数
        $this->destructionInit();
        return $result;
    }

    //--------------------------------------------------------------------------------------------------
    // 核心功能检查过滤参数
    //--------------------------------------------------------------------------------------------------

    /**
     * @description 数组空值传参处理 移除空值的key
     * @author Holyrisk
     * @date 2019/5/5 15:36
     * @param array $array_data 要处理的数组
     * @return array 移除空值后的 数组
     */
    protected function unsetArrayEmptey(array $array_data)
    {
        $new_data = [];
        if ($array_data  != false){
            foreach ($array_data as $k => $v){
                if ($v === '0' or $v === 0 or !empty($v)){
                    $new_data[$k] = trim($v);
                }
            }
        }
        unset($array_data);
        return $new_data;
    }

    /**
     * @description 检测用户输入的 表达式 exp 是否合法
     * @author Holyrisk
     * @date 2019/5/7 17:37
     * @param $exp
     * @return bool|string
     */
    protected function isExp($exp)
    {
        $exp_arr = $this->exp;
        if (in_array($exp,$exp_arr)){
            return trim($exp);
        }else{
            return false;
        }
    }

    /**
     * @description exp 对照 英文表达式  用作 同个字段 where 条件区分
     * @author Holyrisk
     * @date 2019/5/9 19:42
     * @param $exp
     * @return mixed
     */
    protected function setExpWhere($exp)
    {
        $exp_where_arr = $this->exp_where;//配置的  表达式  英文对照 表
        if (array_key_exists($exp,$exp_where_arr)){
            $exp = $exp_where_arr[$exp];
        }
        return $exp;
    }

//    /**
//     * @description 数据库事务是否已经被激活
//     * @author chensai
//     * @date 2019/7/5 09:56
//     * @return bool
//     */
//    public function isActiveTransaction()
//    {
//        if(self::$transActive)
//        {
//            return true;
//        }
//        return false;
//    }

    /**
     * @description pdo 开启事务
     * @author chensai
     * @date 2019/7/5 10:18
     */
    public function beginTransaction()
    {
        //使用事务之前  先要关闭自动提交，不关闭的话，则会出现异常的时候无法回滚
        $this->link->setAttribute(PDO::ATTR_AUTOCOMMIT,0);
        $this->link->beginTransaction();
    }

    /**
     * @description pdo 提交数据库事务
     * @author chensai
     * @date 2019/7/5 10:18
     */
    public function commit()
    {
        $this->link->commit();
    }

    /**
     * @description pdo 回滚数据库事务
     * @author chensai
     * @date 2019/7/5 10:18
     */
    public function rollBack()
    {
        $this->link->rollBack();
    }

}