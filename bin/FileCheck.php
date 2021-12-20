<?php
// ----------------------------------------------------------------------
// | secPHP                                                             |
// ----------------------------------------------------------------------
// | Copyright (c) 2016-2019 https://www.secxun.com All rights reserved.|
// ----------------------------------------------------------------------
// | Author: yourway <lyw@secxun.com>                                   |
// ----------------------------------------------------------------------

/**
 * 场景：
 * 进程监控文件改动
 */
include_once 'Base.php';
$workerName = sprintf('php-ps:%s', 'FileCheckWorker');
date_default_timezone_set('PRC');
cli_set_process_title($workerName);
$filepath = ROOT_PATH . DS . 'app' . DS;
if (!is_dir($filepath)) {
    $log = date('Y-m-d H:i:s') . ' 监控文件夹不存在,启动监控失败';
    file_put_contents(ROOT_PATH . DS . 'runtime' . DS . 'log' . DS . 'process' . DS . 'FileCheck.log', $log);
    die;
}
//遍历文件夹
$temp = scandir($filepath);
$filePathArray = array();
foreach ($temp as $v) {
    $a = $filepath . '/' . $v;
    if (is_dir($a)) {
        if ($v == '.' || $v == '..') {
            continue;
        }
        $getFileArray = getFileList($a);
        if (!empty($getFileArray)) {
            foreach ($getFileArray as $value) {
                $filePathArray[] = $value;
            }
            unset($value);
        }
    } else {
        $filePathArray[] = $a;
    }
}
unset($v);
$getFileMd5 = array();
//获取所有文件md5
foreach ($filePathArray as $v) {
    $getFileMd5[md5_file($v)] = 1;
}
unset($filePathArray);
unset($temp);
//以上内容均为进程初始化 以下代码开始匹配文件是否更新
$i = 0;
while (true) {
    //验证自己是否成为孤儿了
    checkFatherIsset($workerName);
    //遍历文件夹
    $temp = scandir($filepath);
    $filePathArray = array();
    foreach ($temp as $v) {
        $a = $filepath . '/' . $v;
        if (is_dir($a)) {
            if ($v == '.' || $v == '..') {
                continue;
            }
            $getFileArray = getFileList($a);
            if (!empty($getFileArray)) {
                foreach ($getFileArray as $value) {
                    $filePathArray[] = $value;
                }
                unset($value);
            }
        } else {
            $filePathArray[] = $a;
        }
    }
    unset($v);
    //验证文件是否存在更新
    foreach ($filePathArray as $v) {
        if (empty($getFileMd5[md5_file($v)])) {
            //执行初始化进程
            //$masterServer = exec("ps -ef | grep php-ps:httpWorker | grep -v grep | awk '{print $2}'", $return_var);
            $masterServer = exec("ps -ef | grep php-ps:WebSocket | grep -v grep | awk '{print $2}'", $return_var);
            if (!empty($return_var)) {
                $killHttpWorkerList = implode(' ', $return_var);
                $masterServer = exec("kill -9 {$killHttpWorkerList}", $return_var);
                echo '[FileCheck Tips: ' . date('Y-m-d H:i:s') . ']' . $v . ' file change , service restarted.'.PHP_EOL;
                exit;
            }
        }
    }
    unset($filePathArray);
    unset($temp);
    sleep(1);
}
