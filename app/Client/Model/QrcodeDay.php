<?php
/**
 * @description 管理端 管理账号
 * @author Holyrisk
 * @date 2020/5/27 14:24
 */
declare(strict_types=1);
namespace App\Client\Model;


use Secxun\Core\Mysql;
use Secxun\Extend\Holyrisk\Handle\SqlCreate;
use Secxun\Extend\Holyrisk\Sql\Build;
class QrcodeDay
{

    //定义表名
    protected static $tableName = "wechat_statistical_indicators_qrcode_day";
    protected static $app = 'Client';
    protected $mysql;

    public function __construct()
    {
        $this->mysql = new Mysql();
    }


    public function getDayNum($qrcodeID,$indicators,$day) {
        $sql = " select  num  FROM  " . self::$tableName . ' where 
        qrcode_id="'. $qrcodeID .'" and indicators="' . $indicators . '" and day="' . $day .'"';
        $result = $this->mysql->query($sql,self::$app);
        if (empty($result)) {
            return 0;
        } else {
            return $result['0']['num'];
        }
    }

    public function getStatisticsNum($qrcodeID,$indicators,$startTime,$endTime){
        $sql = " select sum(num) as num  FROM  " . self::$tableName . ' where 
        qrcode_id="'. $qrcodeID .'" and indicators="' . $indicators . '"';

        if ($startTime){
            $sql .=  ' and day >="' . $startTime . '"';
        }

        if ($endTime){
            $sql .=  ' and day <="' . $endTime . '"';
        }

        $result = $this->mysql->query($sql,self::$app);
        if (empty($result)) {
            return 0;
        } else {
            return $result['0']['num'];
        }
    }



}