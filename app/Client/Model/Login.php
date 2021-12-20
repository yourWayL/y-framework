<?php


namespace App\Client\Model;


use Secxun\Core\Mysql;
use Secxun\Extend\Holyrisk\Handle\SqlCreate;

class Login
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
        //if (empty($loginStatus)){
            $sql = "UPDATE `".$this->table."` SET `token` = '".$token."',`tonkem_create_time` = ".$tonkem_create_time." WHERE `id` = ".$id;

        //}else{
         //   $sql = "UPDATE `".$this->table."` SET `token` = '".$token."',`tonkem_create_time` = ".$tonkem_create_time." `login_status` = ".$loginStatus." WHERE `id` = ".$id;

        //}

        try{
            return self::$mysqlObject->queryExecute($sql,[],$this->module);
        }catch (\Exception $exception)
        {
            throw new \Exception($exception->getMessage(),$exception->getCode());
        }
    }

}