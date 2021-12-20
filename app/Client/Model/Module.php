<?php
/**
 * @description 管理端 管理账号
 * @author Holyrisk
 * @date 2020/4/16 14:24
 */

namespace App\Client\Model;


use Secxun\Core\Mysql;
use Secxun\Extend\Holyrisk\Handle\SqlCreate;
use Secxun\Extend\Holyrisk\Sql\Build;

class Module
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
    private $table = 'manager_module';

    /**
     * 模块
     * @var string
     */
    private $module = 'Client';

    /**
     * @description 登录
     * @author Holyrisk
     * @date 2020/4/16 17:50
     * @param $username 账号
     * @param $password 密码
     * @return mixed
     * @throws \Exception
     */
    public function moduleFind($name,$type)
    {
        $sqlObj = new SqlCreate();
        $wherearray['name']=$name;
        $wherearray['isdelete']=1;
        if (isset($type)){
            $wherearray['type']=$type;
        }
        $sql = $sqlObj->find($this->table,'*',$wherearray,false);
        return self::$mysqlObject->query($sql,$this->module);


    }

    /**
     * @description 修改 token
     * @author Holyrisk
     * @date 2020/4/16 19:37
     * @param $id 修改的用户
     * @param $token token
     * @param $tonkem_create_time 过期时间
     * @return mixed
     * @throws \Exception
     */
    public function moduleUpdate($updateData,$whereData)
    {
        $sqlObj = new SqlCreate();
        $sql = $sqlObj->update($this->table,$updateData,$whereData);
        return self::$mysqlObject->query($sql,$this->module);


    }
    /**
     * @description 插入
     * @author Holyrisk
     * @date 2020/4/16 14:25
     * @param $insertData
     * @return mixed
     */
    public function insert($paramArr)
    {
        $addarray['url'] = str_replace("/","\\/",$paramArr['url']);;
        $addarray['name'] = $paramArr['name'];
        $addarray['type'] = $paramArr['type'];
        $addarray['status'] = 1;
        $addarray['isadmin'] = $paramArr['isadmin'];
        $addarray['nav_id'] = $paramArr['nav_id'];
        return self::$mysqlObject->insert($addarray, $this->table,$this->module);

    }
    public function getModule($isadmin){
        $sql = "select * from `manager_module` where `isadmin` = ".$isadmin ." and `isdelete` = 1 ORDER BY level asc";
        return self::$mysqlObject->query($sql,$this->module);
    }
    public function getPageModule($paramArr){
        $obj = new Build();
        $result = $obj->table($this->table)
            ->where('isadmin','=',$paramArr['isadmin'])
            ->where('type','=',3)
            ->where('isdelete','=',1)
            ->where('name', 'like', '%' . $paramArr['keyword'] . '%')
            ->order('id', 'desc')
            ->page($paramArr['page'], $paramArr['page_num']);
        $data['data']= self::$mysqlObject->query($result['page'],$this->module);
        $data['count']= self::$mysqlObject->query($result['count'],$this->module);
        return $data;
    }

    public function getModuleNav(){
        $sql = "select * from manager_module where type<3 and isadmin=2 and status=1  and isdelete=1  ORDER BY level ASC ";
        return self::$mysqlObject->query($sql,$this->module);
    }


}