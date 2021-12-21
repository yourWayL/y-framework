<?php
declare(strict_types=1);
/**
 * @category: Secxun\Core
 * @description: 框架提供的Mysql的处理方法
 * @author yourway <lyw@secxiun.com>
 * @copyright 深圳安巽科技有限公司 <https://www.secxun.com>
 * @create: 2020 - 03 - 16
 */


namespace Secxun\Core;


use mysql_xdevapi\Exception;

class Mysql
{
    private static $config;
    private static $mysql;

    /**
     * Mysql constructor.
     * @param string $app
     * @param string $node
     */
//    public function __construct(string $app, string $node = 'db_master')
//    {
//        $path = ROOT_PATH . DS . 'app' . DS . ucfirst($app) . DS . 'Config' . DS . 'database.php';
//        $databaseInfo = require $path;
//        self::$config = $databaseInfo['db_master'];
//        self::$mysql = new \Swoole\Coroutine\MySQL();
//        self::$mysql->connect(self::$config);
//        self::$mysql->query('set names ' . self::$config['charset']);
//    }

    /**
     * 执行sql语句
     * @param string $sql
     * @param float $timeout
     * @return mixed
     */
    public function query(string $sql, string $app, float $timeout = 0)
    {
        $path = ROOT_PATH . DS . 'app' . DS . ucfirst($app) . DS . 'Config' . DS . 'database.php';
        $databaseInfo = require $path;
        $config = $databaseInfo['db_master'];
        $mysql = new \Swoole\Coroutine\MySQL();
        $mysql->connect($config);
        try {
            $mysql->query('set names ' . $config['charset']);
        } catch (\Exception $e) {
            $mysql = new \Swoole\Coroutine\MySQL();
            $mysql->connect($config);
        }
        $result = $mysql->query($sql, $timeout);
        return $result;
    }

    /**
     * 处理select搜索
     * @param array $parameter
     * @param string $table
     * @param $app
     * @param bool $getSql
     * @param array $validate
     * @return array|bool
     * @throws ValidationException
     */
    public function select(array $parameter, string $table, string $app, bool $getSql = false, array $validate = array()): array
    {
        $path = ROOT_PATH . DS . 'app' . DS . ucfirst($app) . DS . 'Config' . DS . 'database.php';
        $databaseInfo = require $path;
        $config = $databaseInfo['db_master'];
        $mysql = new \Swoole\Coroutine\MySQL();
        $mysql->connect($config);
        $mysql->query('set names ' . $config['charset']);

        if (!empty($validate)) {
            $validate = new Validation();
            $handleParameter = $validate::validate($parameter, $validate);
            return $handleParameter;
        } else {
            $handleParameter = $parameter;
        }
        $prepareSql = "SELECT * FROM {$table} WHERE ";
		$order = '';
        $executeParam = array();
        foreach ($handleParameter as $key => $v) {
            if (is_array($v)) {
                if($v[0] == 'like'){
                    $prepareSql .= $key . ' ' . $v[0] . " concat('%',?,'%') and ";
                    $executeParam[] = $v[1];
                }else{
                    $prepareSql .= $key . ' ' . $v[0] . ' ? and ';
                    $executeParam[] = $v[1];
                }
            } else {
                if ($key == 'order') {
                    $order = ' order by ' . $v . ' ';
                    continue;
                } else {
                    $prepareSql .= $key . ' = ? and ';
                    $executeParam[] = $v;
                }
            }
        }
        $prepareSql = rtrim($prepareSql, ' and ');
        $prepareSql .= $order;
        $result = array();
        if ($getSql == true) {
            $result['prepareSql'] = $prepareSql;
            $result['executeParam'] = $executeParam;
        }
        $doPrepare = $mysql->prepare($prepareSql);
        if ($doPrepare == false) {
            echo '错误信息 : ' . $mysql->errno . $mysql->error . PHP_EOL;
        } else {
            $result['result'] = $doPrepare->execute($executeParam);
        }
        return $result;
    }

    /**
     * 处理主从不同步时 从主库搜索
     * @param array $parameter
     * @param string $table
     * @param $app
     * @param bool $getSql
     * @param array $validate
     * @return array|bool
     * @throws ValidationException
     */
    public function selectNodeOne(array $parameter, string $table, string $app, bool $getSql = false, array $validate = array()): array
    {
        $path = ROOT_PATH . DS . 'app' . DS . ucfirst($app) . DS . 'Config' . DS . 'database.php';
        $databaseInfo = require $path;
        $config = $databaseInfo['db_node1'];

        $mysql = new \Swoole\Coroutine\MySQL();
        $mysql->connect($config);
        $mysql->query('set names ' . $config['charset']);

        if (!empty($validate)) {
            $validate = new Validation();
            $handleParameter = $validate::validate($parameter, $validate);
            return $handleParameter;
        } else {
            $handleParameter = $parameter;
        }
        $prepareSql = "SELECT * FROM {$table} WHERE ";
        $order = '';
        $executeParam = array();
        foreach ($handleParameter as $key => $v) {
            if (is_array($v)) {
                if($v[0] == 'like'){
                    $prepareSql .= $key . ' ' . $v[0] . " concat('%',?,'%') and ";
                    $executeParam[] = $v[1];
                }else{
                    $prepareSql .= $key . ' ' . $v[0] . ' ? and ';
                    $executeParam[] = $v[1];
                }
            } else {
                if ($key == 'order') {
                    $order = ' order by ' . $v . ' ';
                    continue;
                } else {
                    $prepareSql .= $key . ' = ? and ';
                    $executeParam[] = $v;
                }
            }
        }
        $prepareSql = rtrim($prepareSql, ' and ');
        $prepareSql .= $order;
        $result = array();
        if ($getSql == true) {
            $result['prepareSql'] = $prepareSql;
            $result['executeParam'] = $executeParam;
        }
        $doPrepare = $mysql->prepare($prepareSql);
        if ($doPrepare == false) {
            echo '错误信息 : ' . $mysql->errno . $mysql->error . PHP_EOL;
        } else {
            $result['result'] = $doPrepare->execute($executeParam);
        }
        return $result;
    }

    /**
     * @param array $insertData
     * @param string $table
     * @param string $app
     * @param bool $getSql
     * @return mixed
     */
    public function insert(array $insertData, string $table, string $app, $getSql = false)
    {
        try{
            $path = ROOT_PATH . DS . 'app' . DS . ucfirst($app) . DS . 'Config' . DS . 'database.php';
            $databaseInfo = require $path;
            $config = $databaseInfo['db_master'];
            $mysql = new \Swoole\Coroutine\MySQL();
            $mysql->connect($config);
            $mysql->query('set names ' . $config['charset']);
            $startSql = "INSERT INTO `{$table}` ";
            $field = '';
            $values = '';
            $result = array();
            $executeParam = array();
            foreach ($insertData as $k => $v) {
                $field .= '`' . $k . '`,';
                $values .= '? ,';
                $executeParam[] = $v;
            }
            $doSql = $startSql . '(' . trim($field, ',') . ') VALUES (' . trim($values, ',') . ')';
            if ($getSql == true) {
                $result['Sql'] = $doSql;
                $result['executeParam'] = $executeParam;
            }
            $doPrepare = $mysql->prepare($doSql);
            if ($doPrepare == false) {
                echo '错误信息 : ' . $mysql->errno . $mysql->error . PHP_EOL;
            } else {
                $result['result'] = $doPrepare->execute($executeParam);
            }
            return $result;
        }catch (Exception $e){
            var_dump($e);
        }

    }

    /**
     * @param array $insertData
     * @param string $table
     * @param int $num
     * @return
     */
    function insertAll(array $insertData, string $table, $app, $num = 1000)
    {
        $path = ROOT_PATH . DS . 'app' . DS . ucfirst($app) . DS . 'Config' . DS . 'database.php';
        $databaseInfo = require $path;
        $config = $databaseInfo['db_master'];
        $mysql = new \Swoole\Coroutine\MySQL();
        $mysql->connect($config);
        $i = 0;
        $addData = array();
        foreach ($insertData as $v) {
            if ($i >= $num) {
                self::createInsertAllSql($table, $addData, $app);
                unset($addData);
            }
            $addData[$i] = $v;
            $i++;
        }
        return self::createInsertAllSql($table, $addData, $app);
    }

    /**
     * @param $table
     * @param $addData
     * @return mixed
     */
    private static function createInsertAllSql($table, $addData, $app)
    {
        $path = ROOT_PATH . DS . 'app' . DS . ucfirst($app) . DS . 'Config' . DS . 'database.php';
        $databaseInfo = require $path;
        $config = $databaseInfo['db_master'];
        $mysql = new \Swoole\Coroutine\MySQL();
        $mysql->connect($config);
        $startSql = "INSERT INTO `{$table}` ";
        $fields = array();
        $values = '';
        foreach ($addData as $value) {
            $values .= '(';
            foreach ($value as $key => $item) {
                $fields[$key] = 1;
                $values .= '\'' . $item . '\' ,';
            }
            $values = trim($values, ',');
            $values .= '),';
        }
        $field = '(';
        foreach ($fields as $k => $value) {
            $field .= $k . ',';
        }
        $field = trim($field, ',');
        $field .= ')';
        $doSql = $startSql . trim($field, ',') . ' VALUES ' . trim($values, ',');

//        echo PHP_EOL.$doSql;
        return $mysql->query($doSql);
    }

    /**
     * @description 执行sql语句 | 预处理 参数绑定
     * @param string $sql 将要被执行的 $sql 语句 | 占位符 使用 ?
     * @param array $sqlArr 数组必须为数字索引的数组，参数的顺序与 $sql 语句 ? 相同
     * @param string $app
     * @param float $timeout
     * @return mixed
     * @throws \Exception
     * @date 2020/4/16 17:46
     */
    public function queryExecute(string $sql, array $sqlArr = [], string $app, float $timeout = 0)
    {
        $path = ROOT_PATH . DS . 'app' . DS . ucfirst($app) . DS . 'Config' . DS . 'database.php';
        $databaseInfo = require $path;
        $config = $databaseInfo['db_master'];
        $mysql = new \Swoole\Coroutine\MySQL();
        $mysql->connect($config);
        try {
            $mysql->query('set names ' . $config['charset']);
        } catch (\Exception $e) {
            $mysql = new \Swoole\Coroutine\MySQL();
            $mysql->connect($config);
        }
        //执行 预处理
        $stmt = $mysql->prepare($sql);
        if ($stmt == false) {
            throw new \Exception($mysql->error, $mysql->errno);
        } else {
            $result = $stmt->execute($sqlArr);
        }
        return $result;
    }

    /**
     * @description 创建
     * @param array $insertData
     * @param string $table
     * @param $app
     * @param bool $getSql
     * @return mixed
     * @throws \Exception
     * @date 2020/5/14 21:10
     */
    public function create(array $insertData, string $table, $app, $getSql = false)
    {
        $startSql = "INSERT INTO `{$table}` ";
        $field = '';
        $values = '';
        foreach ($insertData as $k => $v) {
            $field .= '`' . $k . '`,';
            $values .= '\'' . $v . '\' ,';
        }
        $doSql = $startSql . '(' . trim($field, ',') . ') VALUES (' . trim($values, ',') . ')';
        $path = ROOT_PATH . DS . 'app' . DS . ucfirst($app) . DS . 'Config' . DS . 'database.php';
        $databaseInfo = require $path;
        $config = $databaseInfo['db_master'];
        $mysql = new \Swoole\Coroutine\MySQL();
        $mysql->connect($config);
        try {
            $mysql->query('set names ' . $config['charset']);
        } catch (\Exception $e) {
            $mysql = new \Swoole\Coroutine\MySQL();
            $mysql->connect($config);
        }
        //执行 预处理
        $stmt = $mysql->prepare($doSql);
        if ($stmt == false) {
            throw new \Exception($mysql->error, $mysql->errno);
        } else {
            $stmt->execute([]);
            if ($mysql->error != '') {
                throw new \Exception($mysql->error, $mysql->errno);
            } else {
                $result = $mysql->insert_id;
            }
        }
        return $result;
    }

    /**
     * @description 获取单条语句
     * @param string $sql
     * @param array $sqlArr
     * @param string $app
     * @param float $timeout
     * @return array
     * @throws \Exception
     * @date 2020/4/28 15:49
     */
    public function fetch(string $sql, array $sqlArr = [], string $app, float $timeout = 0)
    {
        $path = ROOT_PATH . DS . 'app' . DS . ucfirst($app) . DS . 'Config' . DS . 'database.php';
        $databaseInfo = require $path;
        $config = $databaseInfo['db_master'];
        $mysql = new \Swoole\Coroutine\MySQL();
        $mysql->connect($config);
        try {
            $mysql->query('set names ' . $config['charset']);
        } catch (\Exception $e) {
            $mysql = new \Swoole\Coroutine\MySQL();
            $mysql->connect($config);
        }
        //执行 预处理
        $result = [];
        $stmt = $mysql->prepare($sql);
        if ($stmt == false) {
            throw new \Exception($mysql->error, $mysql->errno);
        } else {
            $res = $stmt->execute($sqlArr);
            if (!empty($res)) {
                $result = $res[0];
            }
        }
        return $result;
    }

    /**
     * @description 获取 多条 语句
     * @param string $sql
     * @param array $sqlArr
     * @param string $app
     * @param float $timeout
     * @return mixed
     * @throws \Exception
     * @date 2020/4/28 15:50
     */
    public function fetchAll(string $sql, array $sqlArr = [], string $app, float $timeout = 0)
    {
        $path = ROOT_PATH . DS . 'app' . DS . ucfirst($app) . DS . 'Config' . DS . 'database.php';
        $databaseInfo = require $path;
        $config = $databaseInfo['db_master'];
        $mysql = new \Swoole\Coroutine\MySQL();
        $mysql->connect($config);
        try {
            $mysql->query('set names ' . $config['charset']);
        } catch (\Exception $e) {
            $mysql = new \Swoole\Coroutine\MySQL();
            $mysql->connect($config);
        }
        //执行 预处理
        $stmt = $mysql->prepare($sql);
        if ($stmt == false) {
            throw new \Exception($mysql->error, $mysql->errno);
        } else {
            $result = $stmt->execute($sqlArr);
        }
        return $result;
    }

    public function sqlQuery($sql)
    {
        $sql = json_decode(str_replace("/", "\\/", $sql), true);
        $sql = rtrim(ltrim($sql, "\""), "\"");
        $mysql = new \Swoole\Coroutine\MySQL();
        return $sql;
        return $mysql->query($sql);


    }
}
