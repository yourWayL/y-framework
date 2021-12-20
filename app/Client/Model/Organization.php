<?php
declare(strict_types=1);

namespace App\Client\Model;

use Secxun\Core\Mysql;


class Organization{

    private static $mysqlObject;
    protected $mysql;


    public function __construct()
    {
        self::$mysqlObject = new Mysql();
        $this->mysql = new Mysql();
    }

    /**
     * 表
     * @var string
     */
    private $table = 'client_organization';

    /**
     * 模块
     * @var string
     */
    private $module = 'Client';

    public function getOrganizationByParentId($id){
        $sql = 'select id FROM ' . $this->table . ' where is_del=1 and parent_id=' . $id;
        return self::$mysqlObject->queryExecute($sql,[],$this->module);
    }
    public function getOrganizationByName($name,$unit){
        $sql = "select * from " . $this->table . " where  is_del=1 and name = '" . $name . "' and `unit`=" . $unit;
        return self::$mysqlObject->queryExecute($sql,[],$this->module);
    }

    public function delOrgan($organID){
        $sql = "DELETE FROM " . $this->table . " where id=" . $organID;
        return self::$mysqlObject->queryExecute($sql,[],$this->module);
    }

    public  function getOrganization($paramArr){
//        $sql = "select * from  client_organization  where `is_del`=1  ";
        $sql = "select * from  " . $this->table . "  where is_del=1 ";

        if (isset($paramArr['unit'])){
            $sql .= ' and `unit` = "' .  $paramArr['unit'] . '" ';
        }
        if (isset($paramArr['name'])){
            $sql .= ' and  `name` like  "%'. $paramArr['name'] .'%" ';
        }

        if (isset($paramArr['parent_id'])){
            $sql .= ' and `parent_id`=' . $paramArr['parent_id'];
        }

        if (isset($paramArr['id'])){
            $sql .= ' and `id` = ' . $paramArr['id'];
        }

        if (isset($paramArr['o_path'])){
            $sql .= ' and `path` like "' . $paramArr['o_path'] . '%"';
        }


        return self::$mysqlObject->queryExecute($sql,[],$this->module);

    }


    public function add($paramArr){
//
        return self::$mysqlObject->insert($paramArr, $this->table,$this->module);
    }


    public function update($paramArr){
        $sql = 'update ' . $this->table . ' set `name` = "' . $paramArr['name']  . '",`parent_id`=' . $paramArr['parent_id'] . ',`jurisdiction_id`="' . $paramArr['jurisdiction_id'] . '",`duplicate_jurisdiction`="'.$paramArr['duplicate_jurisdiction'] .'"  where `id`=' . $paramArr['id'];
        return self::$mysqlObject->queryExecute($sql,$this->module);
    }
    public function updateOrgan(array $update,string $userID) : bool {
        $mysql = $this->connectMysql('db_master');
        $sql = "UPDATE " . $this->table . ' SET';
        foreach ($update as $k => $v) {
            $sql .= "`$k` = ? , ";
            $execute[] = $v;
        }
        $sql = trim($sql, ', ');
        $sql .= ' WHERE `id` = ?';
        $execute[] = $userID;
        $stmt = $mysql->prepare($sql);
        if ($stmt == false) {
            //file_put_contents(ROOT_PATH . DS . 'runtime/log/debugLog/youway.txt', json_encode($mysql->error . PHP_EOL . $mysql->errno) . PHP_EOL, FILE_APPEND);
            return false;
        } else {
            $result = $stmt->execute($execute);
            if ($result) {
                return true;
            }
        }

    }

    public function delByPath($oPath){
//        $sql = 'update ' . $this->table . ' set `is_del` =1  where `id`=' .$organID;
        $sql = 'delete from ' . $this->table . ' where path like "' . $oPath . '%" and jurisdiction_id is null';
        return self::$mysqlObject->queryExecute($sql,[],$this->module);
    }

    public function delByID($id){
        $sql = 'delete from ' . $this->table . ' where id ="' . $id . '" and jurisdiction_id is null';
        return self::$mysqlObject->queryExecute($sql,[],$this->module);
    }
    public function recovery($paramArr){
        $sql = 'update ' . $this->table . ' set `is_del`=0,parent_id= ' . $paramArr['parent_id']  . ',`path`="' . $paramArr['path'] . '",`jurisdiction_id`= ' .$paramArr['jurisdiction_id'] . ' where `id`=' . $paramArr['id'];
        return self::$mysqlObject->queryExecute($sql,[],$this->module);
    }

    public function getListByPath($id){
        $sql = 'select * from ' . $this->table . ' where path like "%' . $id . '%"';
        return self::$mysqlObject->queryExecute($sql,[],$this->module);
    }
    public function getListByPathStr($path){
        $sql = "select * from " . $this->table . " where path like '" . $path . "%'";
        return self::$mysqlObject->queryExecute($sql,[],$this->module);
    }

    public function getOrgnaztionById($id){
        $sql = 'select * from ' . $this->table . ' where id = ' . $id ;
        return self::$mysqlObject->queryExecute($sql,[],$this->module);
    }

    public function updatePath($path,$id){
        $sql = 'update ' . $this->table . ' set path="' . $path .'" where id=' . $id;
        return self::$mysqlObject->queryExecute($sql,[],$this->module);
    }
    public function connectMysql(string $dbNode)
    {
        $path = ROOT_PATH . DS . 'app' . DS . ucfirst($this->module) . DS . 'Config' . DS . 'database.php';
        $databaseInfo = require $path;
        $config = $databaseInfo[$dbNode];
        $mysql = new \Swoole\Coroutine\MySQL();
        $mysql->connect($config);
        return $mysql;
    }
}