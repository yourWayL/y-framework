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
class ClientUser
{

    //定义表名
    protected static $tableName = "client_user";
    protected static $app = 'Client';
    protected $mysql;

    public function __construct()
    {
        $this->mysql = new Mysql();
    }

    /**修改用户
     * @param array $update
     * @param string $userID
     * @return array
     */
   public function updateClientUserByID(array $update,string $userID) : bool {
       $mysql = $this->connectMysql('db_master');

       $sql = "UPDATE " . self::$tableName . ' SET ';

       foreach ($update as $k => $v) {
           $sql .= "`$k` = ? , ";
           $execute[] = $v;
       }
       $sql = trim($sql, ', ');
       $sql .= ' WHERE `id` = ?';
       $execute[] = $userID;

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

    /**添加用户
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

    /**根据用户ID查询用户信息
     * @param string $userID
     * @return array
     * @throws \Secxun\Core\ValidationException
     */
   public function getUserByID(int $userID) {
//       $sql = "select * from  client_user  where id = " . $userID;
//       $result = $this->mysql->query($sql.self::$app);
//       return $result;
       $select['id'] = $userID;
       $result = $this->mysql->select($select, self::$tableName, self::$app, true);

       if (empty($result['result'])) {
           return [];
       } else {
           return $result['result'];
       }
   }

    public function getUserByName(string $username):bool {
//       $sql = "select * from  client_user  where id = " . $userID;
//       $result = $this->mysql->query($sql.self::$app);
//       return $result;
        $select['username'] = $username;
        $result = $this->mysql->select($select, self::$tableName, self::$app, true);
        if (!empty($result['result'])) {
            return false;
        } else {
            return true;
        }
    }

    public function getUserByPpass(int $p_pass):bool{
        $select['p_pass'] = $p_pass;
        $result = $this->mysql->select($select, self::$tableName, self::$app, true);
        if (!empty($result['result'])) {
            return false;
        } else {
            return true;
        }

    }

    public function getUserList(array $arr){
       $sql = "select id,name,username from " . self::$tableName  . " where p_pass =0 ";

       if (isset($arr['o_path'])){
           $sql .=  '  and o_path like "' . $arr['o_path']  . '%"';
       }
       return  $this->mysql->query($sql,self::$app);
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

    public function delClientUser($oPath){
        $sql = "delete from " . self::$tableName . ' where o_path like "' . $oPath . '" and type=2';
        $this->mysql->query($sql,self::$app);
    }

    public function delClientUserByID(int $userID){
        $sql = "delete from " . self::$tableName . ' where id=' . $userID;
        $this->mysql->query($sql,self::$app);
    }

    /**
     * @Desc:
     * @param $rankeId
     * @return int
     * @throws \Exception
     */
    public function getCountByRankeId($rankeId)
    {
        $mysql = $this->connectMysql('db_master');
        $sql = "select count(1) as ct from ".self::$tableName." where ranke_id = ?";
        $executeParam[] = $rankeId;
        $doPrepare = $mysql->prepare($sql);
        if ($doPrepare == false) {
            echo $mysql->error;
            log_message(self::$app,'info','错误信息 : ' . $mysql->errno . $mysql->error . PHP_EOL);
            return $doPrepare;
        } else {
            $res = $doPrepare->execute($executeParam);
            return isset($res[0]['ct'])?$res[0]['ct']:0;
        }
    }

}