<?php
/**
 * Note:
 * User: rcj
 * Date: 2020/5/29
 * Time: 12:07
 */

namespace App\Client\Model;

class WechatPhoneUser
{
    //定义表名
    protected static $tableName = "wechat_phone_user";
    protected static $app = 'Client';
    protected $mysql;

    /**
     * @Desc  获取居民信息
     * @param string $openid
     * @param string $fields
     * @return mixed
     * @throws \Exception
     */
    public function getUserByOpenid(string $openid, $fields= '*', $is_self= false)
    {
        $path = ROOT_PATH . DS . 'app' . DS . ucfirst(self::$app) . DS . 'Config' . DS . 'database.php';
        $databaseInfo = require $path;
        $config = $databaseInfo['db_master'];
        $mysql = new \Swoole\Coroutine\MySQL();
        $mysql->connect($config);
        $searchSql = "SELECT $fields  FROM " . self::$tableName;
        $searchSql .= " WHERE openid = ? ";
        if ($is_self) {
            $searchSql .= " and type = 0 ";
        }
        $searchSql .= "limit 1";
        $doPrepare = $mysql->prepare($searchSql);
        log_message(self::$app, 'info', 'SQL: ' . $searchSql);
        if ($doPrepare == false) {
            log_message(self::$app, 'info', '错误信息 : ' . $mysql->errno . $mysql->error);
            return $doPrepare;
        } else {
            $result = $doPrepare->execute([$openid]);
            return $result;
        }
    }

    public function updatePhoneUserUnit($unionid, $updateData)
    {
        $sql = "UPDATE " . self::$tableName . ' SET ';
        foreach ($updateData as $k => $v) {
            $sql .= "`$k` = ? , ";
            $execute[] = $v;
        }
        $sql = trim($sql, ', ');
        $sql .= ' WHERE `unionid` = ?';
        $execute[] = $unionid;
        $path = ROOT_PATH . DS . 'app' . DS . ucfirst(self::$app) . DS . 'Config' . DS . 'database.php';
        $databaseInfo = require $path;
        $config = $databaseInfo['db_master'];
        $mysql = new \Swoole\Coroutine\MySQL();
        $mysql->connect($config);
        $mysql->query('set names ' . $config['charset']);
        $stmt = $mysql->prepare($sql);
        if ($stmt == false) {
            return false;
        } else {
            $result = $stmt->execute($execute);
            if ($result) {
                return true;
            }
        }
    }

    /**
     * @Desc:根据openid获取所有家人
     * @param string $openid
     * @param string $fields
     * @return array
     * @throws \Exception
     */
    public function getUsersByOpenid(string $openid, $fields = '*')
    {
        $path = ROOT_PATH . DS . 'app' . DS . ucfirst(self::$app) . DS . 'Config' . DS . 'database.php';
        $databaseInfo = require $path;
        $config = $databaseInfo['db_master'];
        $mysql = new \Swoole\Coroutine\MySQL();
        $mysql->connect($config);
        $searchSql = "SELECT $fields FROM " . self::$tableName;
        $searchSql .= " where openid=? and type != 0 and delete_time is null";
        $doPrepare = $mysql->prepare($searchSql);
        log_message(self::$app, 'info', 'SQL: ' . $searchSql);
        if ($doPrepare == false) {
            log_message(self::$app, 'info', '错误信息 : ' . $mysql->errno . $mysql->error . PHP_EOL);
            return $doPrepare;
        } else {
            $result = $doPrepare->execute([$openid]);
            return $result;
        }
    }

}