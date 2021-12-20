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


class AccessToken
{
    //定义表名
    protected static $tableName = "wechat_access_token";
    protected static $app = 'Client';
    protected $mysql;

    public function __construct()
    {
        $this->mysql = new Mysql();
    }

    /**
     * 获取最新一条token记录
     * @return array
     */
    public function getAcccessToken(): array
    {
        $getAcccessTokenSql = "SELECT * from `wechat_access_token` order by id desc limit 1";
        $result = $this->mysql->query($getAcccessTokenSql, self::$app);
        return $result;
    }

    /**
     * 添加一条最新的token
     * @param array $insertData
     * @return mixed
     */
    public function addAccessToken(array $insertData): bool
    {
        return $this->mysql->insert($insertData, self::$tableName, self::$app)['result'];
    }
}