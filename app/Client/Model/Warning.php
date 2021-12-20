<?php
/**
 * Created by PhpStorm.
 * User: luoteng
 * Date: 2020/5/25
 * Time: 18:47
 */

namespace App\Client\Model;

use Secxun\Core\Mysql;
use think\Exception;

class Warning
{
    protected $table = "client_warning";

    protected $module = "Client";
    protected $field = [];
    protected $levelArr = [
        '0' => '普',
        '1' => '中',
        '2' => '高',
        '3' => '紧'
    ];

    protected $typeArr = [
        '0' => '刷单诈骗',
        '1' => '贷款诈骗',
        '2' => '仿冒公检法诈骗',
        '3' => '交友诈骗',
        '4' => '网购订单诈骗',
        '6' => '仿冒他人诈骗',
        '9' => '其他诈骗',
        '10' => '游戏充值诈骗',
        '12' => '赌博/投资/交友诈骗',
        '13' => '银行卡诈骗'
    ];

    protected $statusArr = [
        '1' => '待劝阻',
        '2' => '劝阻中',
        '3' => '已劝阻',
        '4' => '已被骗'
    ];

    protected $sex = [
        '1' => '男',
        '2' => '女'
    ];


    /**
     * 预警列表
     * @param $where
     * @param int $page
     * @param int $pageSize
     * @param string $order
     * @return array
     * @throws \Exception
     */
    public function getList($where, $page = 1, $pageSize = 10, $order = "create_time desc"): array
    {
        $model = new Mysql();
        $sql = "select  * from " . $this->table . " where 1=1";
        $countSql = "select  count(id) as `count` from " . $this->table . " where 1=1";
        $execute = [];
        if (isset($where['phone'])) {
//            $sql .= ' and  `phone` LIKE "%?%"';
//            $countSql .= ' and  `phone` LIKE "%?%"';
            $sql .= ' and  `phone` LIKE "%' . $where['phone'] . '%"';
            $countSql .= ' and  `phone` LIKE "%' . $where['phone'] . '%"';
//            $execute[] = $where['phone'];
        }

//        if (isset($where['unit'])){
//            $sql .= " and  `unit` like '?%'";
//            $countSql .= " and  `unit` like '?%'";
//            $execute[] = $where['unit'];
//            $sql .= ' and  `unit` LIKE "%'.$where['unit'].'%"';
//            $countSql .= ' and  `unit` LIKE "%'.$where['unit'].'%"';
//        }

        if (isset($where['unit'])) {
            if($where['flag'] == "="){
                $sql .= " and  `unit_path` = ?";
                $countSql .= " and  `unit_path` = ?";
                $execute[] = $where['unit'];
            }else{
                $sql .= ' and  `unit_path` LIKE "'.$where['unit'].'%"';
                $countSql .= ' and  `unit_path` LIKE "'.$where['unit'].'%"';
            }
        }

        if(isset($where['ctl_user_id'])){
            $sql .= " and  `ctl_user_id` = ?";
            $countSql .= " and  `ctl_user_id` = ?";
            $execute[] = $where['ctl_user_id'];
        }

        if (isset($where['create_time']) && !empty($where['create_time'])) {
            $sql .= " and  (`create_time` between ? and ?)";
            $countSql .= " and  (`create_time` between ? and ?)";
            $execute[] = $where['create_time']['between'][0];
            $execute[] = $where['create_time']['between'][1];
        }

        if (isset($where['username'])) {
            $sql .= ' and  `username` LIKE "%'.$where['username'].'%"';
            $countSql .= ' and  `username` LIKE "%'.$where['username'].'%"';
        }

        if (isset($where['level'])) {
            $level = explode(",", $where['level']);
            $inArr = [];
            foreach ($level as $k => $v) {
                $inArr[] = "?";
            }
            $instr = implode(",", $inArr);
            $sql .= " and  `level` IN ({$instr})";
            $countSql .= " and  `level` IN ({$instr})";
            $execute = array_merge($execute, $level);
        }

        if (isset($where['type'])) {
            $type = explode(",", $where['type']);
            $inArr = [];
            foreach ($type as $k => $v) {
                $inArr[] = "?";
            }
            $instr = implode(",", $inArr);
            $sql .= " and  `type` IN ({$instr})";
            $countSql .= " and  `type` IN ({$instr})";
            $execute = array_merge($execute, $type);
        }

        if (isset($where['status'])) {
            $status = explode(",", $where['status']);
            $inArr = [];
            foreach ($status as $k => $v) {
                $inArr[] = "?";
            }
            $instr = implode(",", $inArr);
            $sql .= " and  `status` IN ({$instr})";
            $countSql .= " and  `status` IN ({$instr})";
            $execute = array_merge($execute, $status);
        }

        $countRes = $model->fetch($countSql, $execute, $this->module);
        $count = $countRes['count'];
        $start = ($page - 1) * $pageSize;
        if (!empty($order)) {
            $sql .= " ORDER BY {$order}";
        }
        $sql .= " LIMIT {$start},{$pageSize}";
//var_dump($execute);
//        var_dump($sql);

        $list['list'] = $model->fetchAll($sql, $execute, $this->module);

        $list['count'] = $count;
        return $list;
    }

    /**
     * 前端需要的诈骗类型格式
     * @return array
     */
    public function typesData()
    {
        $data = [];
        foreach ($this->typeArr as $k => $v) {
            $data[] = [
                'label' => $v,
                'value' => $k
            ];
        }
        return $data;
    }

    /**
     * 前端需要的预警等级格式
     * @return array
     */
    public function levelData()
    {
        $data = [];
        foreach ($this->levelArr as $k => $v) {
            $data[] = [
                'label' => $v,
                'value' => $k
            ];
        }
        return $data;
    }

    /**
     * 前端需要的预警状态格式
     * @return array
     */
    public function statusData()
    {
        $data = [];
        foreach ($this->statusArr as $k => $v) {
            $data[] = [
                'label' => $v,
                'value' => $k
            ];
        }
        return $data;
    }

    /**
     * 返回性别对应的文字
     * @param $key
     * @return mixed
     */
    public function sexStr($key)
    {
        return $this->sex[$key];
    }

    /**
     * 返回等级对应的文字
     * @param $key
     * @return mixed
     */
    public function levelStr($key)
    {
        return $this->levelArr[$key];
    }

    /**
     * 返回预警状态对应的文字
     * @param $key
     * @return mixed
     */
    public function statusStr($key)
    {
        return $this->statusArr[$key];
    }

    /**
     * 返回诈骗类型对应的文字
     * @param $key
     * @return mixed
     */
    public function typeStr($key)
    {
        return $this->typeArr[$key];
    }

    /**
     * 预警饼状统计
     * @param $where
     * @param int $page
     * @param int $pageSize
     * @return array
     * @throws \Exception
     */
    public function getStatisticsPie($where, $page = 1, $pageSize = 10): array
    {
        $model = new Mysql();
        $sql = "select `type`,count(id) as `count` from " . $this->table . " where 1=1";

        $execute = [];
        if (isset($where['phone'])) {
//            $sql .= ' and  `phone` LIKE "%?%"';
//            $countSql .= ' and  `phone` LIKE "%?%"';
            $sql .= ' and  `phone` LIKE "%' . $where['phone'] . '%"';
//            $execute[] = $where['phone'];
        }

//        if (isset($where['unit'])){
//            $sql .= " and  `unit` like '?%'";
//            $countSql .= " and  `unit` like '?%'";
//            $execute[] = $where['unit'];
//            $sql .= ' and  `unit` LIKE "%'.$where['unit'].'%"';
//        }

        if (isset($where['unit'])) {
            if($where['flag'] == "="){
                $sql .= " and  `unit_path` = ?";
                $execute[] = $where['unit_path'];
            }else{
                $sql .= ' and  `unit_path` LIKE "'.$where['unit'].'%"';
            }
        }

        if (isset($where['create_time'])) {
            $sql .= " and  `create_time` between ? and ?";
            $execute[] = $where['create_time']['between'][0];
            $execute[] = $where['create_time']['between'][1];
        }

        $sql .= " GROUP BY `type`";

        $list = $model->fetchAll($sql, $execute, $this->module);

        $temp = [];
        foreach ($list as $key => $value) {
            $temp[$value['type']] = $value['count'];
        }

        $data = [];
        foreach ($this->typeArr as $k => $val) {
            $data['fraudTypeList'][] = [
                'name' => $val,
                'value' => $temp[$k] ?? 0
            ];
            $data['fraudType'][] = $val;
        }

        return $data;
    }

    /**
     * 预警折线统计
     * @param $where
     * @param int $page
     * @param int $pageSize
     * @return array
     * @throws \Exception
     */
    public function getStatisticsLine($where, $page = 1, $pageSize = 10): array
    {
        $model = new Mysql();
        $sql = "select `type`,FROM_UNIXTIME(create_time,'%Y-%m-%d') as date_time,count(id) as `count` from " . $this->table . " where 1=1";

        $execute = [];
        if (isset($where['phone'])) {
//            $sql .= ' and  `phone` LIKE "%?%"';
//            $countSql .= ' and  `phone` LIKE "%?%"';
            $sql .= ' and  `phone` LIKE "%' . $where['phone'] . '%"';
//            $execute[] = $where['phone'];
        }

//        if (isset($where['unit'])){
//            $sql .= " and  `unit` like '?%'";
//            $countSql .= " and  `unit` like '?%'";
//            $execute[] = $where['unit'];
//            $sql .= ' and  `unit` LIKE "%'.$where['unit'].'%"';
//        }

        if (isset($where['unit'])) {
            if($where['flag'] == "="){
                $sql .= " and  `unit_path` = ?";
                $execute[] = $where['unit'];
            }else{
                $sql .= ' and  `unit_path` LIKE "'.$where['unit'].'%"';
            }
        }

        if (isset($where['create_time'])) {
            $sql .= " and `create_time` between ? and ?";
            $execute[] = $where['create_time']['between'][0];
            $execute[] = $where['create_time']['between'][1];
        }

        $sql .= " GROUP BY date_time,`type`";

        $list = $model->fetchAll($sql, $execute, $this->module);

        $temp = [];
        foreach ($list as $key => $value) {
            $temp[$value['type'] . "_" . $value['date_time']] = $value['count'];
        }

        $data = [];
        $data['typeList'] = array_values($this->typeArr);

        $startTime = $where['create_time']['between'][0];
        $endTime = $where['create_time']['between'][1];
        $dateArr = [];
        while (true) {
            $date = date("Y-m-d", $startTime);
            $dateArr[] = $date;
            $startTime += 86400;
            if ($startTime > $endTime) {
                break;
            }
        }


        foreach ($this->typeArr as $k => $v) {
            $oneDate = [];
            foreach ($dateArr as $ke => $va) {
                $tempKey = $k . _ . $va;
                $oneDate[] = $temp[$tempKey] ?? 0;
            }
            $data['dataList'][] = $oneDate;
        }

        $data['timeList'] = $dateArr;

        return $data;
    }

    /**
     * 预警详情
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function getDetail($id = 0)
    {
        if ($id > 0) {
            $model = new Mysql();
            $sql = "select  *  from " . $this->table . " where 1=1";
            $sql .= " and  `id` = ?";
            $execute[] = $id;
            $res = $model->fetch($sql, $execute, $this->module);
            return $res;
        }
    }

    /**
     * 领取预警记录
     * @param array $paramArr
     * @return mixed
     */
    public function receiveUpdate($paramArr = [])
    {
        $model = new Mysql();
        $sql = ' update ' . $this->table . ' set `status`="' . $paramArr['status'] . '",`ctl_user`="' . $paramArr['ctl_user'] . '",ctl_user_id="' . $paramArr['ctl_user_id'] . '",receive_time="' . $paramArr['receive_time'] . '"  where id=' . $paramArr['id'];
        return $model->query($sql, $this->module);
    }

    /**
     * 修改预警状态
     * @param array $paramArr
     * @return mixed
     */
    public function updateStatus($paramArr = [])
    {
        $model = new Mysql();
        $sql = ' update ' . $this->table . ' set `status`="' . $paramArr['status'] . '",`record_time`="' . $paramArr['record_time'] . '"  where id=' . $paramArr['id'];
        return $model->query($sql, $this->module);
    }

    /**
     * 调试日志
     * @param string $sql
     */
    public function writeSql($sql = "")
    {
        $path = "/data/wwwroot/ceshi/swoole/gzh/runtime/log/database/luoteng.txt";
        file_put_contents($path, date("Y-m-d H:i:s") . " :" . $sql . PHP_EOL, FILE_APPEND);
    }
}