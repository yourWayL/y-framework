<?php
/**
 * Created by PhpStorm.
 * User: luoteng
 * Date: 2020/5/25
 * Time: 20:25
 */

namespace App\Client\Model;

use Secxun\Core\Mysql;

class Fans
{
    protected $table = "wechat_phone_user";

    protected $module = "Client";

    public function getUserInfoByPhone(string $phone):array{
        $model = new Mysql();
        $sql = "select  * from " . $this->table . " where 1=1";
        $sql .= " and  `phone` = ?";
        $execute[] = $phone;
        $list = $model->fetch($sql,$execute,$this->module);
        return $list;
    }

    public function getUserInfoByOpenid(string $openid):array{
        $model = new Mysql();
        $sql = "select  * from " . $this->table . " where 1=1 AND `type` = 0";
        $sql .= " and  `openid` = ?";
        $execute[] = $openid;
        $list = $model->fetch($sql,$execute,$this->module);
        return $list;
    }

    public function getCliUserInfoByOpenid(string $openid):array{
        $model = new Mysql();
        $sql = "select  * from `client_user` where 1=1";
        $sql .= " and  `openid` = ?";
        $execute[] = $openid;
        $list = $model->fetch($sql,$execute,$this->module);
        return $list;
    }
}