<?php
/**
 * @description 管理端 管理账号
 * @author Holyrisk
 * @date 2020/4/16 14:24
 */

namespace App\Client\Model;


use Secxun\Core\Mysql;
use Secxun\Extend\Holyrisk\Handle\SqlCreate;

class PlatformQrCode
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
    private $table = 'client_qrcode';

    /**
     * 模块
     * @var string
     */
    private $module = 'Client';


    /**判断是否存在同名二维码
     * @param $name
     * @return mixed
     */
    public function isName($name){
        $sql = "select * from " . $this->table . ' where qrcode_name="' . $name  . '"';
        return self::$mysqlObject->query($sql,$this->module);
    }

    /**数据插入
     * @param $insertData
     * @return mixed
     */
    public function insert($insertData)
    {
//        $path = ROOT_PATH . DS . 'app' . DS . ucfirst($this->module) . DS . 'Config' . DS . 'database.php';
//        $databaseInfo = require $path;
//        $config = $databaseInfo['db_master'];
//        $mysql = new \Swoole\Coroutine\MySQL();
//        $mysql->connect($config);
//        $mysql->query('set names ' . $config['charset']);
//        $startSql = "INSERT INTO `{$this->table}` ";
//        $field = '';
//        $values = '';
//        foreach ($insertData as $k => $v) {
//            $field .= '`' . $k . '`,';
//            $values .= '\'' . $v . '\' ,';
//        }
//       $doSql = $startSql . '(' . trim($field, ',') . ') VALUES (' . trim($values, ',') . ')';
//        echo $doSql;
        return self::$mysqlObject->insert($insertData, $this->table,$this->module);
    }

    /**根据用户ID查它所拥有的二维码
     * @param $userID
     * @return mixed
     */
    public function getQrCodeByUserID($userID){
        $sql = " select * from " . $this->table . ' where user_id=' . $userID . ' and `status` < 4';
        return self::$mysqlObject->query($sql,$this->module);
    }

    /** 根据二维码ID查
     * @param $qrCodeID
     * @return mixed
     */
    public function getQrCodeByQrCodeID($qrCodeID){
        $sql = " select * from " . $this->table . ' where id=' . $qrCodeID;
        return self::$mysqlObject->query($sql,$this->module);
    }

    /**
     * 插入二维码地址
     * @param $id
     * @param $imgUrl
     * @return mixed
     */
    public function setImgUrl($id,$imgUrl){
        $sql = "update " . $this->table . " set qrcode_img_url='" . $imgUrl . "' where id=" . $id ;
        return self::$mysqlObject->query($sql,$this->module);
    }

    /**查询二维码列表
     * @param $paramArr
     * @return mixed
     */
    public function getQrCodeList($paramArr){
        $sql = 'select * from ' . $this->table . ' where  1=1 ';

        // 根据状态在进行搜索
        if ( isset($paramArr['status']) && $paramArr['status']){
            $sql .= ' and status=' . $paramArr['status'];
        }
        // 根据创建时间进行搜索
        if (isset($paramArr['start_time']) && isset($paramArr['end_time'])){
            $sql .= 'and  created_time BETWEEN  "' . $paramArr['start_time'] . '" and "' . $paramArr['end_time'] . '"';
        }

        if (isset($paramArr['qrcode_name'])){
            $sql .= ' and  qrcode_name like "%' . $paramArr['qrcode_name'] .'%" ';
        }


        //分页
        $page = $paramArr['page'] -1 > 0 ? $paramArr['page'] -1 : $paramArr['page'];

        $sql .= " ORDER BY created_time DESC limit " . $page*$paramArr['limit'] . ',' . $paramArr['limit'] ;

        return self::$mysqlObject->query($sql,$this->module);

    }

    /**修改二维码
     * @param $paramArr
     */
    public function update($paramArr){

        $sql = "update " . $this->table . ' set updated_time="' . $paramArr['updated_time']  . '" ';


        if (isset($paramArr['user_id'])){
            $sql.= ",user_id='" . $paramArr['user_id'] . "' ";
        }

        if (isset($paramArr['path'])){
            $sql.= ",path='" . $paramArr['path'] . "' ";
        }

        if (isset($paramArr['qrcode_name'])){
            $sql.= ",qrcode_name='" . $paramArr['qrcode_name'] . "' ";
        }

        if (isset($paramArr['qrcode_logo'])){
            $sql.= ",qrcode_logo='" . $paramArr['qrcode_logo'] . "' ";
        }

        if (isset($paramArr['logo_file'])){
            $sql.= ",logo_file='" . $paramArr['logo_file'] . "' ";
        }

        if (isset($paramArr['replay_type'])){
            $sql.= ",replay_type='" . $paramArr['replay_type'] . "' ";
        }

        if (isset($paramArr['replay_content'])){
            $sql.= ",replay_content='" . $paramArr['replay_content'] . "' ";
        }

        if (isset($paramArr['replay_text'])){
            $sql.= ",replay_text='" . $paramArr['replay_text'] . "' ";
        }

        if (isset($paramArr['replay_img_url'])){
            $sql.= ",replay_img_url='" . $paramArr['replay_img_url'] . "' ";
        }

        if (isset($paramArr['expire_time'])){
            $sql.= ",expire_time='" . $paramArr['expire_time'] . "' ";
        }

        if (isset($paramArr['label_id'])){
            $sql.= ",label_id='" . $paramArr['label_id'] . "' ";
        }

        if (isset($paramArr['jurisdiction_id'])){
            $sql.= ",jurisdiction_id='" . $paramArr['jurisdiction_id'] . "' ";
        }

        if (isset($paramArr['jurisdiction_path'])){
            $sql.= ",jurisdiction_path='" . $paramArr['jurisdiction_path'] . "' ";
        }

        if (isset($paramArr['expire'])){
            $sql.= ",expire='" . $paramArr['expire'] . "' ";
        }

        if (isset($paramArr['wechat_qrcode_url'])){
            $sql.= ",wechat_qrcode_url='" . $paramArr['wechat_qrcode_url'] . "' ";
        }

        if (isset($paramArr['img_url'])){
            $sql.= ",qrcode_img_url='" . $paramArr['img_url'] . "' ";
        }

        if (isset($paramArr['updated_time'])){
            $sql.= ",updated_time='" . $paramArr['updated_time'] . "' ";
        }

        if (isset($paramArr['template_id'])){
            $sql.= ",template_id='" . $paramArr['template_id'] . "' ";
        }


        $sql.= ' where id=' . $paramArr['id'];

        return self::$mysqlObject->query($sql,$this->module);

    }

    /** 伪删除二维码
     * @param $paramArr
     */
    public function delete($id){
//        $sql = "update " . $this->table  . ' set status=4,deleted_time="' . time().'"  where id=' . $id ;
        $sql = 'delete from ' . $this->table . ' where id=' . $id;
        return self::$mysqlObject->query($sql,$this->module);
    }

    public function getQrcodeByPath($organPath){
        $sql = "select * from " . $this->table . ' where status=1 and organ_path like "' . $organPath . '%" ';
        return self::$mysqlObject->query($sql,$this->module);
    }


}