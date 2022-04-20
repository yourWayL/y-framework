<?php
// ----------------------------------------------------------------------
// | 应用设置
// ----------------------------------------------------------------------

return [
    // 应用是否启动
    'http_enabled' => true,
    // 应用地址
    'host' => '0.0.0.0',
    // 应用端口
    'port' => 9501,
    // 应用是否开启HTTP调试
    'http_debug' => true,
    // 应用是否开启HTTP调试
    'websocket_debug' => true,
    //HTTP服务是否开启守护模式
    'http_daemonize' => false,
    /**
     * HTTP服务开启work数量
     * 全异步非阻塞服务器 worker_num配置为CPU核数的1-4倍即可。
     * 同步阻塞服务器，worker_num配置为100或者更高，具体要看每次请求处理的耗时和操作系统负载状况
     */
    'http_workerNum' => 4,
    /**
     * 此参数表示worker进程在处理完n次请求后结束运行。manager会重新创建一个worker进程。此选项用来防止worker进程内存溢出。
     * 设置为0表示不自动重启。在Worker进程中需要保存连接信息的服务，需要设置为0.
     */
    'http_maxRequest' => 20,
    // 指定swoole错误日志文件。在swoole http运行期发生的异常信息会记录到这个文件中
    'http_logFile' => ROOT_PATH . DS . 'runtime/log/http/httpError.log',
    // 指定swoole错误日志文件。在swoole websocket运行期发生的异常信息会记录到这个文件中
    'websocket_logFile' => ROOT_PATH . DS . 'runtime/log/http/websocketError.log',
    // 钉钉预警是否启动
    'app_ding_enabled' => true,
    // 钉钉机器人webhook
    'app_webhook' => '',
];