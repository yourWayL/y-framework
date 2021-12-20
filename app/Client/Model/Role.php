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
class Role
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
//    private $table = 'xc_module';
    private $table = 'client_role';

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
    public function find($wherearray)
    {
        $sqlObj = new SqlCreate();
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
    public function update($updateData,$whereData)
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
        return self::$mysqlObject->insert($paramArr, $this->table,$this->module);
    }
    public function getRoleList($paramArr){
        $obj = new Build();
        $result = $obj->table($this->table)
        ->where('type', '=', $paramArr['type'])
        ->where('p_id', '=', $paramArr['p_id'])
        ->where('isadmin', '=', 2)
        ->where('name', 'like', '%' . $paramArr['keyword'] . '%')
        ->order('id', 'desc')
        ->page($paramArr['page'], $paramArr['page_num']);

        $data['data']= self::$mysqlObject->query($result['page'],$this->module);
        $data['count']= self::$mysqlObject->query($result['count'],$this->module);
        return $data;
    }
    public function getcliRoleList($paramArr){
        $obj = new Build();
        $result = $obj->table($this->table)
            ->where('type', '=', 2)
            ->where('p_id', '=', $paramArr['p_id'])
            ->order('id', 'desc')
            ->page($paramArr['page'], $paramArr['page_num']);

        $data['data']= self::$mysqlObject->query($result['page'],$this->module);
        $data['count']= self::$mysqlObject->query($result['count'],$this->module);
        return $data;
    }
    public function allListData($paramArr)
    {
//        $where['keyword'][]="=";
//        $where['keyword'][]="2";
//        $where['type'][]="=";
//        $where['type'][]="1";
//        return self::$mysqlObject->select($where,$this->table,'Client',true);
        $type= $paramArr['type'];
        $sql = "SELECT * FROM client_role where `isadmin` = 2 and  `type` =".$type."  and  `status` = 1 and `p_id` = '".$paramArr['p_id']."'";
        return self::$mysqlObject->query($sql,$this->module);

    }


    public function countBy($paramArr)
    {
//        $where['keyword'][]="=";
//        $where['keyword'][]="2";
//        $where['type'][]="=";
//        $where['type'][]="1";
//        return self::$mysqlObject->select($where,$this->table,'Client',true);

        $keyword = $paramArr['keyword'];
        $type= $paramArr['type'];
        $sql = "SELECT * FROM client_role where `isadmin` = 2 and  `type` =".$type." and `p_id` = ".$paramArr['p_id']."  and `name` like '%".$keyword."%'";
        return self::$mysqlObject->query($sql,$this->module);

    }
    public function delete($id)
    {
        if ($id==""){
            return false;
        }
        $sql = "DELETE FROM client_role where `id` =".$id;
        return self::$mysqlObject->query($sql,$this->module);

    }


    public function getRoleByID($id){
        $sql = "select `id`,`name`,`power` from " . $this->table . ' where status=1 and id=' . $id;
        return self::$mysqlObject->query($sql,$this->module);
    }
    public function IdSelect($string)
    {
//        $where['keyword'][]="=";
//        $where['keyword'][]="2";
//        $where['type'][]="=";
//        $where['type'][]="1";
//        return self::$mysqlObject->select($where,$this->table,'Client',true);
        $sql = "SELECT * FROM client_role where `p_id` in (".$string.")";

        return self::$mysqlObject->query($sql,$this->module);

    }
}