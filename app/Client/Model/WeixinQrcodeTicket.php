<?php


namespace App\Client\Model;


use Secxun\Core\Mysql;

class WeixinQrcodeTicket
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
    private $table = 'weixin_qrcode_ticket';

    /**
     * 模块
     * @var string
     */
    private $module = 'Client';

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

}