<?php
/**
 * Note  标签中心
 * User: rcj
 * Date: 2020/5/30
 * Time: 15:36
 */

namespace App\Client\Model;
use Secxun\Core\Mysql;

class ManagerLabel
{
    //定义表名
    protected static $tableName = "manager_label";
    protected static $app = 'Client';
    protected $mysql;

    public function __construct()
    {
        $this->mysql = new Mysql();
    }

    /**
     * @Desc  获取标签列表
     * @param  $ids
     * @param string $fields
     * @return mixed
     */
    public function getLables($ids,string $fields='*')
    {
        $mysql = $this->connectMysql('db_master');
        $sql = "select $fields from ".self::$tableName . ' where id in(';
        if(is_string($ids)){
            $ids = explode(',',trim($ids,','));
        }
        $param = [];
        foreach ($ids as $val){
            $sql.='?,';
            $param[] = $val;
        }
        $sql = trim($sql,',').')';
        log_message(self::$app,'info','SQL: '.$sql);
        $stmt = $mysql->prepare($sql);
        if ($stmt == false) {
            log_message(self::$app,'info','错误信息 : ' . $mysql->errno . $mysql->error );
            return $stmt;
        }else{
           $result = $stmt->execute($param);
           return $result;
        }
    }

    public function connectMysql(string $dbNode)
    {
        $path = ROOT_PATH . DS . 'app' . DS . ucfirst(self::$app) . DS . 'Config' . DS . 'database.php';
        $databaseInfo = require $path;
        $config = $databaseInfo[$dbNode];
        $mysql = new \Swoole\Coroutine\MySQL();
        $mysql->connect($config);
        return $mysql;
    }

}