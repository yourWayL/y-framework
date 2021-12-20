<?php
/**
 * @description 缓存 token
 * @author Holyrisk
 * @date 2020/4/30 17:30
 */

namespace App\Client\Model;

use Secxun\Core\Mysql;

class WeixinAccessToken
{

    private static $mysqlObject;

    public function __construct()
    {
        self::$mysqlObject = new Mysql();
    }

    /**
     * 表
     * @var string
     */
    private $table = 'wechat_access_token';

    /**
     * 模块
     * @var string
     */
    private $module = 'Client';

    /**
     * @description 获取单条数据
     * @author Holyrisk
     * @date 2020/4/30 17:51
     * @return array
     * @throws \Exception
     */
    public function getDataLast()
    {
        $sql = "SELECT * FROM `".$this->table."` ORDER BY id DESC LIMIT 1";
        $result = self::$mysqlObject->fetch($sql,[],$this->module);
        return $result;
    }

    /**
     * @description 创建
     * @author Holyrisk
     * @date 2020/4/30 17:59
     * @param $insertArr
     * @return mixed
     * @throws \Exception
     */
    public function insert($insertArr)
    {
        $result = self::$mysqlObject->create($insertArr,$this->table,$this->module);
        return $result;
    }

    /**
     * @description 修改
     * @author Holyrisk
     * @date 2020/4/30 18:01
     * @param $updateArr
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function update($updateArr,$id)
    {
        $sql = "UPDATE `".$this->table."` SET `access_token`='".$updateArr['access_token']
            ."' ,`expires_in`='".$updateArr['expires_in']."',`token_create_time`='".$updateArr['token_create_time']
            ."' WHERE `id`='".$id."';";
        $result = self::$mysqlObject->queryExecute($sql,[],$this->module);
        return $result;
    }

    /**
     * @description 设置 token
     * @author Holyrisk
     * @date 2020/4/30 18:17
     * @param $insertArr
     * @return mixed
     * @throws \Exception
     */
    public function save($insertArr)
    {
        $isData = $this->getDataLast();
        if (!empty($isData))
        {
            $result = $this->update($insertArr,$isData['id']);
        }
        else
        {
            $result = $this->insert($insertArr);
        }
        return $result;
    }

}