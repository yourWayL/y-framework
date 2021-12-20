<?php


namespace Secxun\Extend\Eller;
use Secxun\Extend\Eller\Database\Connect;
use Swoole\Coroutine\MySQL;

class DB
{
    private static $instance = null;
    private $connect = null;

    // 查询条件
    public $condition = []; // where条件
    public $tableName  = ""; //  表名称

    private function __construct($config)
    {
        $connect = new Connect();
        return $this->connect = $connect;
    }

    /**
     * 获取MySQL实例
     * @return MySQL|null
     * @author ELLER
     */
    public function getMysql()
    {
        return $this->mysql;
    }

    /**
     * 获取DB连接
     * @return DB|null
     * @author ELLER
     */
    public static function connect()
    {
        if(!self::$instance){
            $httpConfig = require ROOT_PATH . DS . 'config' . DS . 'database.php';
            self::$instance = new self($httpConfig['servers']['db_master']);
        }
        return self::$instance;
    }

    /**
     * 执行SQL语句
     * @param $sql
     * @return array|bool
     * @author ELLER
     */
    public static function execute($sql)
    {
        $mysql = self::connect();
        return $mysql->getMysql()->query($sql);
    }

    /**
     * 设置表名称
     * @param $name
     * @return DB|null
     * @author ELLER
     */
    public static function table($name)
    {
        self::connect()->tableName = $name;
        return self::connect();
    }

    /**
     * 设置where条件
     * @param $condition
     * @param null $value
     * @return DB|null
     * @author ELLER
     */
    public static function where($condition, $value = null)
    {
        if(func_num_args() == 2){
            $data = [$condition => $value];
        }else{
            $data = $condition;
        }
        self::connect()->condition = $data;
        return self::connect();
    }


    public static function paginate(int $pageSize)
    {
        return self::connect()->paginate($pageSize);
    }
}