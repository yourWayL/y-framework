<?php
namespace App\Client\Model;
use Secxun\Extend\Eller\Database\Model;
use Secxun\Core\Mysql as OriMysql;

class FanStatistics extends Model
{

    protected $table = 'wechat_statistical_indicators_units_day';

    const APP_NAME = 'Client';

    /**
     * @Desc   统计指定单位指定时间段的相关指标值
     * @param int $unitlId
     * @param array $date
     * @return array|bool
     * @throws \Secxun\Extend\Eller\Database\DbException
     */
    public static function getStatistics(int $unitlId,array $date)
    {
         $res = self::whereRaw('unitl_id = '.$unitlId)->whereRaw('day >= \''.$date['start'].'\' and day <=\''.$date['end'].'\'')->groupBy('indicators')->column(['sum(num) as nums','indicators'])->get();
         return $res;
    }

    /**
     * @Desc  获取指定单位指定时间段的指标值列表
     * @param int $unitlId
     * @param array $date
     * @param null $page
     * @param null $pageSize
     * @return mixed
     */
    public static function getList(int $unitlId,array $date,$page=null,$pageSize=null)
    {
        $sql = "SELECT day,SUM(case indicators WHEN 'num_of_new_follow' THEN num ELSE 0 END)num_of_new_follow,
SUM(case indicators WHEN 'num_of_unfollow' THEN num ELSE 0 END )num_of_unfollow,SUM(case indicators WHEN 'net_incr_of_follow' THEN num ELSE 0 END ) net_incr_of_follow,
SUM(case indicators WHEN 'num_of_cumulat_follow' THEN num ELSE 0 END) num_of_cumulat_follow,SUM(case indicators WHEN 'num_of_jzz_register' THEN num ELSE 0 END)num_of_jzz_register from `wechat_statistical_indicators_units_day` 
where unitl_id = ".$unitlId." and day >= '".$date['start']."' and day <='".$date['end']."' GROUP BY day";
        if(!is_null($page) && !is_null($pageSize)){
            $sql.=' limit '.($page-1)*$pageSize.' ,'.$pageSize;
        }
        log_message(self::APP_NAME,'info','SQL :'.$sql);
        $model =  new OriMysql();
        return $model->query($sql,self::APP_NAME);
    }


    /**
     * @Desc: 统计条数
     * @param int $unitlId
     * @param array $date
     * @return int
     */
    public static function countList(int $unitlId,array $date)
    {
        $sql = "SELECT day from `wechat_statistical_indicators_units_day` where unitl_id = ".$unitlId." and day >= '".$date['start']."' and day <='".$date['end']."' GROUP BY day";
        log_message(self::APP_NAME,'info','SQL :'.$sql);
        $model =  new OriMysql();
        return count($model->query($sql,self::APP_NAME));
    }

}