<?php
/**
 * @description 管理端 管理账号
 * @author Holyrisk
 * @date 2020/5/27 14:24
 */
declare(strict_types=1);
namespace App\Client\Model;


use Secxun\Core\Mysql;
use Secxun\Extend\Holyrisk\Handle\SqlCreate;
use Secxun\Extend\Holyrisk\Sql\Build;
class ExportTask
{

    //定义表名
    protected static $tableName = "client_export_task";
    protected static $app = 'Client';
    protected $mysql;

    public function __construct()
    {
        $this->mysql = new Mysql();
    }

    /** 添加导出任务
     * @param array $addData
     * @return bool
     */
    public function add(array $addData):bool{
        $result = $this->mysql->insert($addData, self::$tableName, self::$app, true);
        if ($result['result']) {
            return true;
        } else {
            return false;
        }
    }

    public function getTask(array $arr) {
        $select = [];
        foreach ($arr as $key=>$value){
            $select[$key] = $value;
        }
        $result = $this->mysql->select($select, self::$tableName, self::$app, true);

        if (empty($result['result'])) {
            return [];
        } else {
            return $result['result'];
        }
    }

     /** 修改任务状态
     * @param array $updateArr
     * @param string $taskID
     * @return array
     */
    public function updateTaskStatus(array $updateArr,int $taskID) : bool {
        $mysql = $this->connectMysql('db_master');

        $sql = "UPDATE " . self::$tableName . ' SET ';

        foreach ($updateArr as $k => $v) {
            $sql .= "`$k` = ? , ";
            $execute[] = $v;
        }
        $sql = trim($sql, ', ');
        $sql .= ' WHERE `id` = ?';
        $execute[] = $taskID;

        $stmt = $mysql->prepare($sql);

        if ($stmt == false) {

            return false;
        } else {
            $result = $stmt->execute($execute);
            if ($result) {
                return true;
            }else{
                return false;
            }
        }

    }

    private function connectMysql(string $dbNode)
    {
        $path = ROOT_PATH . DS . 'app' . DS . ucfirst(self::$app) . DS . 'Config' . DS . 'database.php';
        $databaseInfo = require $path;
        $config = $databaseInfo[$dbNode];
        $mysql = new \Swoole\Coroutine\MySQL();
        $mysql->connect($config);
        return $mysql;
    }



}