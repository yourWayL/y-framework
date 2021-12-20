<?php
declare(strict_types=1);
/**
 * @category: \Client\Model\MemberInfo
 * @description: wechat_member_info 表模型
 * @author yourway <lyw@secxiun.com>
 * @copyright 深圳安巽科技有限公司 <https://www.secxun.com>
 * @create: 2020 - 05 - 26
 */

namespace App\Client\Model;

use Secxun\Core\Mysql;


class MemberLog
{
    //定义表名
    protected static $tableName = "wechat_member_log";
    protected static $app = 'Client';
    public $mysql;

    public function __construct()
    {
        $this->mysql = new Mysql();
    }

    /**
     * @desc   获取待处理的日志
     * @return mixed
     */
    public function getPendingData()
    {
        $sql = 'select id,unit_id,create_time,qrcode_id,subscribe,new,reback,qrcode_user_id from '.self::$tableName.' where status = 1 and unit_id !=0 order by id asc limit 10';
        return $this->mysql->query($sql,self::$app);
    }


    public function checkOpenidExist(string $openid)
    {
        $select['openid'] = $openid;
        $result = $this->mysql->select($select, self::$tableName, self::$app, true);
        if (!empty($result['result'])) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 添加关注用户信息
     * @param array $addData
     * @return bool
     */
    public function addMenberLog(array $addData): bool
    {
        $result = $this->mysql->insert($addData, self::$tableName, self::$app, true);
        if ($result['result']) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 基于openid更新 member表
     * @param string $openid
     * @param array $updateData
     * @param string $fromAction
     * @return bool
     */
    public function updateMemberInfo(string $openid, array $updateData, string $fromAction): bool
    {
        $mysql = $this->connectMysql('db_master');
        if ($fromAction == 'HandleQrSceneSubscribe') {
            $sql = "UPDATE " . self::$tableName . ' SET';
            foreach ($updateData as $k => $v) {
                $sql .= "`$k` = ? and ";
                $execute[] = $v;
            }
            $sql = trim($sql, 'and ');
            $sql .= ' WHERE `openid` = ?';
            $execute[] = $openid;
        } else {
            $sql = "UPDATE " . self::$tableName . ' SET `subscribe` = ? WHERE `openid` = ?';
            $execute[] = $updateData['subscribe'];
            $execute[] = $openid;
        }

        //file_put_contents(ROOT_PATH . DS . 'runtime/log/debugLog/youway.txt', json_encode($sql) . PHP_EOL, FILE_APPEND);

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


