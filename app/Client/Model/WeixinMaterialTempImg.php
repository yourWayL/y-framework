<?php


namespace App\Client\Model;

use Secxun\Core\Mysql;

class WeixinMaterialTempImg
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
    private $table = 'wechat_material_temp_img';

    /**
     * 模块
     * @var string
     */
    private $module = 'Wechat';

    /**
     * @description 获取 单个详情
     * @author Holyrisk
     * @date 2020/5/13 17:11
     * @param $id
     * @return array
     * @throws \Exception
     */
    public function getDataId($id)
    {
        $sql = "SELECT * FROM `".$this->table."` WHERE id = '".$id."' LIMIT 1";
        $result = self::$mysqlObject->fetch($sql,[],$this->module);
        return $result;
    }

    /**
     * @description 添加
     * @author Holyrisk
     * @date 2020/5/13 17:12
     * @param $insertArr
     * @return mixed
     * @throws \Exception
     */
    public function insert($insertArr)
    {
        try{
            $result = self::$mysqlObject->create($insertArr,$this->table,$this->module);
            return $result;
        }catch (\Exception $exception)
        {
            throw new \Exception($exception->getMessage(),$exception->getCode());
        }
    }

}