<?php


namespace App\Client\Model;

use Secxun\Core\Mysql;
use Secxun\Extend\Holyrisk\Handle\SqlCreate;

class City
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
    private $table = 'manager_city';

    /**
     * 模块
     * @var string
     */
    private $module = 'Client';

    /**
     * @description 获取详情 | 对应 pid
     * @author Holyrisk
     * @date 2020/4/17 16:31
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function getIdData($id)
    {
        $sql = 'select * from `'.$this->table.'` where  delete_time = 0 AND `id` = '."'".$id."'";
        try{
            return self::$mysqlObject->fetch($sql,[],$this->module);
        }catch (\Exception $exception)
        {
            throw new \Exception($exception->getMessage(),$exception->getCode());
        }
    }

}