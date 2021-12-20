<?php
// ----------------------------------------------------------------------
// | redis 配置相关
// ----------------------------------------------------------------------

return [
    // 驱动方式
    'type'   => 'File',
    // 如果是FILE HOST不生效
    'host' => '127.0.0.1',
    // 如果是FILE PORT不生效
    'port' => 6379,
    // 缓存前缀
    'prefix' => '',
    // 缓存有效期 0表示永久缓存
    'expire' => 0,
    ];