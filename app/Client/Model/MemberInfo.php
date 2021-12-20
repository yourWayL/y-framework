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


class MemberInfo
{
    //定义表名
    protected static $tableName = "wechat_member_info";
    protected static $app = 'Client';
    protected $mysql;

    /**
     * 实例化数据库类
     * MemberInfo constructor.
     */
    public function __construct()
    {
        $this->mysql = new Mysql();
    }

    /**
     * 验证用户是否存在
     * @param string $openid
     * @return bool
     * @throws \Secxun\Core\ValidationException
     */
    public function checkOpenidExist(string $openid): bool
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
    public function addMemberInfo(array $addData): bool
    {
        $result = $this->mysql->insert($addData, self::$tableName, self::$app, true);
        if ($result['result']) {
            return true;
        } else {
            return false;
        }
    }

    public function checkOldData($unionid)
    {
        $sql = "SELECT * FROM `wechat_member_info` WHERE `unionid` = '{$unionid}' and `openid` = '-'";
        $result = $this->mysql->query($sql, self::$app);
        return $result;
    }

    public function deleteOldData($id)
    {
        $sql = "DELETE FROM `wechat_member_info` WHERE id = {$id}";
        $this->mysql->query($sql, self::$app);
    }

    public function updatePhonUserRegisterInfo($unionid, $updateData)
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
     * 基于openid更新 member表
     * @param string $openid
     * @param array $updateData
     * @param string $fromAction
     * @return bool
     */
    public function updateMemberInfo(string $openid, array $updateData, string $fromAction): bool
    {
        if ($fromAction == 'array') {
            $sql = "UPDATE " . self::$tableName . ' SET ';
            foreach ($updateData as $k => $v) {
                $sql .= "`$k` = ? , ";
                $execute[] = $v;
            }
            $sql = trim($sql, ', ');
            $sql .= ' WHERE `openid` = ?';
            $execute[] = $openid;
        } else {
            $sql = "UPDATE " . self::$tableName . ' SET `subscribe` = ? WHERE `openid` = ?';
            $execute[] = $updateData['subscribe'];
            $execute[] = $openid;
        }

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
     * 拉取需要更新用户信息列表
     * @description : whether_get = 1 or update_time < time()
     * @return array
     */
    public function getNeedUpdateUserInfoList(): array
    {
        $doSql = "SELECT * FROM " . self::$tableName . ' WHERE `whether_get` = 1  limit 100';
        return $this->mysql->query($doSql, self::$app);
    }

    /**
     * @Desc  获取粉丝列表
     * @param array $searchArray
     * @param int $pageSize
     * @param int $pageNum
     * @param string $field
     * @param bool $getSql
     * @return array
     * @throws \Exception
     */
    public function getPcFansList(array $searchArray, int $pageSize, int $pageNum, $field = '*',$getSql = false): array
    {
        $path = ROOT_PATH . DS . 'app' . DS . ucfirst(self::$app) . DS . 'Config' . DS . 'database.php';
        $databaseInfo = require $path;
        $config = $databaseInfo['db_master'];
        $mysql = new \Swoole\Coroutine\MySQL();
        $mysql->connect($config);
        $searchSql = "SELECT $field FROM " . self::$tableName;
        $searchSql .= ' as mbTb left join client_organization as unitlTb on mbTb.unit_id = unitlTb.id left join client_user as cli_user on mbTb.qrcode_user_id = cli_user.id';
        $searchSql .= ' WHERE ';
        foreach ($searchArray as $key => $v) {
            if (is_array($v)) {
                if ($v[0] == 'between') {
                    $searchSql .= '' . $key . '' . ' ' . $v[0] . ' ? and ?  and ';
                    $executeParam[] = $v[1];
                    $executeParam[] = $v[2];
                } elseif ($v[0] == 'in') {
                    $searchSql .=  $key . ' in(';
                    foreach ($v[1] as $val) {
                        $searchSql .= " ?,";
                        $executeParam[] = $val;
                    }
                    $searchSql = rtrim($searchSql, ',');
                    $searchSql .= ') and ';
                } else {
                    $searchSql .= '`' . $key . '`' . ' ' . $v[0] . ' ? and ';
                    $executeParam[] = $v[1];
                }
            } else {
                $searchSql .= '`' . $key . '`' . ' = ? and ';
                $executeParam[] = $v;
            }
        }
        $sql = trim($searchSql, 'and ');
        $pageF = ($pageNum - 1) * $pageSize;
        $sql .= " order by mbTb.action_time desc LIMIT {$pageF},$pageSize";
        if ($getSql == true) {
            $result['prepareSql'] = $sql;
            $result['executeParam'] = $executeParam;
        }
        $doPrepare = $mysql->prepare($sql);
        log_message(self::$app, 'info', 'SQL :' . $sql);
        if ($doPrepare == false) {
            log_message(self::$app, 'info', '错误信息 : ' . $mysql->errno . $mysql->error . PHP_EOL);
            return $doPrepare;
        } else {
            log_message(self::$app, 'info', 'executeParam :' . json_encode($executeParam));
            return $result['result'] = $doPrepare->execute($executeParam);
        }
    }


    /**
     * @Desc:   获取粉丝条数
     * @param array $searchArray
     * @return int
     * @throws \Exception
     */
    public function countFans(array $searchArray)
    {
        $path = ROOT_PATH . DS . 'app' . DS . ucfirst(self::$app) . DS . 'Config' . DS . 'database.php';
        $databaseInfo = require $path;
        $config = $databaseInfo['db_master'];
        $mysql = new \Swoole\Coroutine\MySQL();
        $mysql->connect($config);
        $searchSql = "SELECT count(1) as ct FROM " . self::$tableName;
        $searchSql .= ' WHERE ';
        foreach ($searchArray as $key => $v) {
            if (is_array($v)) {
                if ($v[0] == 'between') {
                    $searchSql .= '' . $key . '' . ' ' . $v[0] . ' ? and ?  and ';
                    $executeParam[] = $v[1];
                    $executeParam[] = $v[2];
                } elseif ($v[0] == 'in') {
                    $searchSql .= $key . ' in(';
                    foreach ($v[1] as $val) {
                        $searchSql .= " ?,";
                        $executeParam[] = $val;
                    }
                    $searchSql = rtrim($searchSql, ',');
                    $searchSql .= ') and ';
                } else {
                    $searchSql .= '`' . $key . '`' . ' ' . $v[0] . ' ? and ';
                    $executeParam[] = $v[1];
                }
            } else {
                $searchSql .= '`' . $key . '`' . ' = ? and ';
                $executeParam[] = $v;
            }
        }
        $sql = trim($searchSql, 'and ');
        $doPrepare = $mysql->prepare($sql);
        if ($doPrepare == false) {
            //echo '错误信息 : ' . $mysql->errno . $mysql->error . PHP_EOL;
            log_message(self::$app, 'info', '错误信息 : ' . $mysql->errno . $mysql->error . PHP_EOL);
            return $doPrepare;
        } else {
            $result = $doPrepare->execute($executeParam);
            return $result[0]['ct'];
        }
    }

    /**
     * @Desc  根据openid获取粉丝信息
     * @param string $openid
     * @param string $fields
     * @return mixed
     * @throws \Exception
     */
    public function getFansInfoByOpenid(string $openid, $fields='*')
    {
        $path = ROOT_PATH . DS . 'app' . DS . ucfirst(self::$app) . DS . 'Config' . DS . 'database.php';
        $databaseInfo = require $path;
        $config = $databaseInfo['db_master'];
        $mysql = new \Swoole\Coroutine\MySQL();
        $mysql->connect($config);
        $searchSql = "SELECT $fields  FROM " . self::$tableName;
        $searchSql .= " left join client_organization on wechat_member_info.unit_id = client_organization.id left join client_user on wechat_member_info.qrcode_user_id = client_user.id";
        $searchSql .= ' WHERE wechat_member_info.openid = ? limit 1';
        $doPrepare = $mysql->prepare($searchSql);
        log_message(self::$app, 'info', $searchSql);
        if ($doPrepare == false) {
            log_message(self::$app, 'info', '错误信息 : ' . $mysql->errno . $mysql->error . PHP_EOL);
            return $doPrepare;
        } else {
            $result = $doPrepare->execute([$openid]);
            return $result;
        }
    }


    /**
     * 拉取指定openid的数据
     * @param string $openid
     * @return array
     */
    public function getUserInfo(string $openid): array
    {
        $doSql = "SELECT * FROM " . self::$tableName . " WHERE `openid` = '{$openid}'";
        return $this->mysql->query($doSql, self::$app);
    }

}