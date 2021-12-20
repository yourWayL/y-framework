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
class Cliuser
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
    private $table = 'client_user';

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
    public function getLogin($username,$password)
    {
        $sqlObj = new SqlCreate();
        $whereArr['username'] = $username;
        $whereArr['password'] = $password;
        $sql = $sqlObj->find($this->table,'*',$whereArr);
        try{
            return self::$mysqlObject->queryExecute($sql['sql'],$sql['data'],$this->module);
        }catch (\Exception $exception)
        {
            throw new \Exception($exception->getMessage(),$exception->getCode());
        }
    }
    public function getqrcode($username,$password,$openid)
    {
        $sqlObj = new SqlCreate();
        $whereArr['username'] = $username;
        $whereArr['openid'] = $openid;
        $sql = $sqlObj->find($this->table,'*',$whereArr);
        try{
            return self::$mysqlObject->queryExecute($sql['sql'],$sql['data'],$this->module);
        }catch (\Exception $exception)
        {
            throw new \Exception($exception->getMessage(),$exception->getCode());
        }
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
    public function modifyToken($id,$token,$tonkem_create_time)
    {
        $sql = "UPDATE `".$this->table."` SET `token` = '".$token."',`tonkem_create_time` = ".$tonkem_create_time." WHERE `id` = ".$id;
        try{
            return self::$mysqlObject->queryExecute($sql,[],$this->module);
        }catch (\Exception $exception)
        {
            throw new \Exception($exception->getMessage(),$exception->getCode());
        }
    }
    /**
     * @description 插入
     * @author Holyrisk
     * @date 2020/4/16 14:25
     * @param $insertData
     * @return mixed
     */
    public function insert($insertData)
    {
        return self::$mysqlObject->insert($insertData, $this->table,$this->module,true);
    }
    public function getUsername($where)
    {
        $sqlObj = new SqlCreate();

        $sql = $sqlObj->find($this->table,'*',$where,false);
        return self::$mysqlObject->query($sql,$this->module);
//        try{
//            return self::$mysqlObject->queryExecute($sql,[],$this->module);
//        }catch (\Exception $exception)
//        {
//            var_dump("dasd");
//            var_dump($exception->getCode());
//            var_dump($exception->getMessage());
//        }
    }
    public function getadmin($where)
    {
        $sqlObj = new SqlCreate();
        $sql = $sqlObj->find('client_user','*',$where,false);
        return self::$mysqlObject->query($sql,$this->module);
//        try{
//            return self::$mysqlObject->queryExecute($sql,[],$this->module);
//        }catch (\Exception $exception)
//        {
//            var_dump("dasd");
//            var_dump($exception->getCode());
//            var_dump($exception->getMessage());
//        }
    }
    public function update($updateData,$whereData)
    {
        $sqlObj = new SqlCreate();
        $sql = $sqlObj->update($this->table,$updateData,$whereData);
        return self::$mysqlObject->query($sql,$this->module);
    }
    public function getList()
    {
        $sql ="SELECT * FROM client_user ";
        return $sql = self::$mysqlObject->query($sql,'Client');
    }
    public function getRoleUserList($role)
    {
        $sql ="SELECT * FROM client_user where `role` = ".$role;
        return $sql = self::$mysqlObject->query($sql,'Client');
    }
    public function getcliList($id)
    {
        if ($id==""){
            $sql ="SELECT * FROM client_user";

        }else{
            $sql ="SELECT * FROM client_user where `admin_id` = ".$id;


        }
        return $sql = self::$mysqlObject->query($sql,'Client');
    }
    public function getPageList($paramArr)
    {


        $obj = new Build();
            $result = $obj->table($this->table)
                ->where('o_path', 'like', $paramArr['o_path'] . '%')
                ->where('name', 'like', '%' . $paramArr['name'] . '%')
                ->where('phone', 'like', '%' . $paramArr['phone'] . '%')
                ->where('type', '=',  $paramArr['type'])
                ->order('id', 'desc')
                ->page($paramArr['page'], $paramArr['page_num']);
        $data['data']= self::$mysqlObject->query($result['page'],$this->module);
        $data['count']= self::$mysqlObject->query($result['count'],$this->module);


        return $data;
    }
    public function adminGetPageList($paramArr)
    {


        $obj = new Build();
        $result = $obj->table($this->table)
        ->where('o_path', 'like', $paramArr['o_path'] . '%')
        ->where('name', 'like', '%' . $paramArr['name'] . '%')
        ->where('phone', 'like', '%' . $paramArr['phone'] . '%')
        ->where('type', '=',  $paramArr['type'] )
        ->order('id', 'desc')
        ->page($paramArr['page'], $paramArr['page_num']);
        $data['data']= self::$mysqlObject->query($result['page'],$this->module);
        $data['count']= self::$mysqlObject->query($result['count'],$this->module);


        return $data;
    }

    public function getCliUser($paramArr = []){
        $sql = "select id,`username`,`name`,`organ_id`,`o_path`,`power`,`role_id`  from " . $this->table . " where 1=1 ";

        if (isset($paramArr['organ_id'])){
            $sql .= " and  `organ_id`=" . $paramArr['organ_id'];
        }

        if (isset($paramArr['username'])){
            $sql .= ' and `name` like "%' . $paramArr['username'] .  '%"';
        }

        if (isset($paramArr['o_path'])){
            $sql .= ' and `o_path` like "' . $paramArr['o_path'] .  '%"';
        }
        if (isset($paramArr['id'])){
            $sql .= " and  `id`=" . $paramArr['id'];
        }

        if (isset($paramArr['type'])){
            $sql .= " and  `type`=" . $paramArr['type'];
        }

//        $sql .= ' ORDER BY name DESC LIMIT 10';
        return self::$mysqlObject->queryExecute($sql,[],$this->module);

    }
    public function delClientUser($oPath){
        $sql = "delete from " . $this->table . ' where o_path like "' . $oPath . '%" and type=2';
        return self::$mysqlObject->queryExecute($sql,[],$this->module);
    }

    public function delClientUserByID($organID){
        $sql = "delete from " . $this->table . ' where organ_id = "' . $organID . '" and type=2';
        return self::$mysqlObject->queryExecute($sql,[],$this->module);
    }
    public function getCliUserQrcode($paramArr = []){
        $sql = "select username,id,pc_open,wechat_open,organ_id,o_path,organ_name,`name`,`openid`  from " . $this->table . " where 1=1 ";

//        if (isset($paramArr['organ_id'])){
//            $sql .= " and  `organ_id`=" . $paramArr['organ_id'];
//        }

        if (isset($paramArr['name'])){
            $sql .= ' and `name` like "' . $paramArr['name'] .  '%"';
        }
        if (isset($paramArr['phone'])){
            $sql .= ' and `phone` like "' . $paramArr['phone'] .  '%"';
        }

        if (isset($paramArr['o_path'])){
            $sql .= ' and `o_path` like "' . $paramArr['o_path'] .  '%"';
        }

        if (isset($paramArr['id'])){
            $sql .= " and  `id`=" . $paramArr['id'];
        }

        if (isset($paramArr['type'])){
            $sql .= " and  `type`=" . $paramArr['type'];
        }

        if (isset($paramArr['p_pass'])){
            $sql .= " and  `p_pass`=" . $paramArr['p_pass'];
        }


        if(isset($paramArr['page']) && isset($paramArr['limit'])){
            $sql .= ' LIMIT  ' . $paramArr['page'] . ',' . $paramArr['limit'];
        }

        return self::$mysqlObject->query($sql,$this->module);
    }

    public function getCliUserCount($paramArr){
        $sql = "select  count(1) as count from " . $this->table . " where 1=1 ";

//        if (isset($paramArr['organ_id'])){
//            $sql .= " and  `organ_id`=" . $paramArr['organ_id'];
//        }

        if (isset($paramArr['name'])){
            $sql .= ' and `name` like "%' . $paramArr['name'] .  '%"';
        }
        if (isset($paramArr['phone'])){
            $sql .= ' and `phone` like "' . $paramArr['phone'] .  '%"';
        }


        if (isset($paramArr['type'])){
            $sql .= " and  `type`=" . $paramArr['type'];
        }

        if (isset($paramArr['username'])){
            $sql .= ' and `username` like  "%' . $paramArr['username'] .  '%"';
        }

        if (isset($paramArr['o_path'])){
            $sql .= ' and `o_path` like "' . $paramArr['o_path'] .  '%"';
        }

        if (isset($paramArr['jurisdiction_path'])){
            $sql .= ' and `jurisdiction_path` like "' . $paramArr['jurisdiction_path'] .  '%"';
        }
        if (isset($paramArr['p_pass'])){
            $sql .= " and  `p_pass`=" . $paramArr['p_pass'];
        }

        
        return self::$mysqlObject->query($sql,$this->module);
    }

    // 转移
    public function transfer($paramArr){
        $sql = ' update ' . $this->table . ' set `organ_id`="' .  $paramArr['organ_id'] .'",`jurisdiction_id`="' . $paramArr['jurisdiction_id'] . '",o_path="' . $paramArr['o_path'].'",organ_name="' . $paramArr['organ_name'] .'"  where id=' . $paramArr['id'];
        return self::$mysqlObject->query($sql,$this->module);
    }

    public function updateUserOrganNameByID($paramArr){
        $sql = 'update ' . $this->table . ' set organ_name="' . $paramArr['name'] . '" where organ_id=' . $paramArr['id'];
        return self::$mysqlObject->query($sql,$this->module);
    }



}