<?php

namespace Secxun\WebsocketProcess;

use Secxun\Core\Action as Action;

include_once 'Base.php';
error_reporting(0);
$coreCachePath = ROOT_PATH . DS . 'runtime' . DS . 'cache' . DS . 'core' . DS;
$workerName = sprintf('php-ps:%s', 'WebSocket');
cli_set_process_title($workerName);
//初始化wesocket缓存信息
recursiveDelete($coreCachePath);
try {
    new \Secxun\Core\Websocket(true);
} catch (\Throwable $exception) {
    echo $result = $exception->getMessage();
}

