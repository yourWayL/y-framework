<?php
// ----------------------------------------------------------------------
// | secPHP                                                             |
// ----------------------------------------------------------------------
// | Copyright (c) 2016-2019 https://www.secxun.com All rights reserved.|
// ----------------------------------------------------------------------
// | Author: yourway <lyw@secxun.com>                                   |
// ----------------------------------------------------------------------
ini_set('date.timezone', 'Asia/Shanghai'); // 'Asia/Shanghai' 为上海时区
ini_set('memory_limit', -1);
define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', __DIR__);
define('APP_DIR', ROOT_PATH . '/app/');
require_once ROOT_PATH . DS . "secxun" . DS . "Helper" . DS . "function.php";
require_once ROOT_PATH . DS . 'vendor/autoload.php';
$return_var = '';
//先简单写一下 后面在详细丰富到core
if (!empty($argv[1])) {
    if ($argv[1] == 'reload') {
        $masterServerIsset = exec("ps -ef | grep php-ps:master | grep -v grep | awk '{print $2}'", $return_var);
        if (empty($masterServerIsset)) {
            echo '服务未启动,无法重启服务!请检查执行参数'.PHP_EOL;
            die;
        }
        $killHttpWorkerList = implode(' ', $return_var);
        $masterServer = exec("kill -9 {$killHttpWorkerList}", $return_var);
        echo 'master重启成功!' . PHP_EOL;
    }elseif ($argv[1] == 'help' or $argv[1] == '-h'){
        echo '等待后续添加!'.PHP_EOL;
        die;
    }else{
        echo '无效参数!'.PHP_EOL;
        die;
    }
}

//清除 服务 僵尸进程
$masterServerIsset = exec("ps -ef | grep php-ps:master | grep -v grep | awk '{print $2}'", $return_var);
if (empty($masterServerIsset)) {
    $masterServer = exec("ps -ef | grep php-ps:WebSocket | grep -v grep | awk '{print $2}'", $return_var);
    if (!empty($return_var)) {
        $killHttpWorkerList = implode(' ', $return_var);
        $masterServer = exec("kill -9 {$killHttpWorkerList}", $return_var);
    }
} else {
    echo 'master正在运行中请勿重复运行!' . PHP_EOL;
    die;
}
//$process::daemon(true, true);
$process = new \Secxun\Core\Process;

