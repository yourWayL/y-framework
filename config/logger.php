<?php
// ----------------------------------------------------------------------
// | 应用设置
// ----------------------------------------------------------------------

return [
    // 应用是否启动
    'app_enabled'               => true,
    // 应用名称
    'app_name'                  => 'test project2',
    // 应用地址
    'app_host'                  => '0.0.0.0',
    // 应用端口
    'app_port'                  => '9501',
    // 使用协议
    'app_mode'                  => 'http',
    // 钉钉预警是否启动
    'app_ding_enabled'          => true,
    // 钉钉机器人webhook
    'app_webhook'               => '',

    'database_log' => ROOT_PATH . DS . 'runtime/log/database/mysql.log',
    ];