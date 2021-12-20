<?php

return [
    /**
     * DB数据库服务器集群
     */
        //服务标记
        'db_master' => array(
            'host'        => '127.0.0.1',
            'port'        => 3306,//MySQL端口 默认3306 可选参数
            'user'        => 'root',
            'password'    => 'secxun@2019X',
            'database'    => 'dfxc',
            'timeout'     => 120, // 仅影响connect超时时间，不影响query和execute方法,参考`客户端超时规则`
            'charset'     => 'utf8mb4',
        ),'db_node1' => array(
            'host'        => 'MySQL IP地址',
            'user'        => '数据用户',
            'password'    => '数据库密码',
            'database'    => '数据库名',
            'port'        => 'MySQL端口 默认3306 可选参数',
            'timeout'     => '建立连接超时时间', // 仅影响connect超时时间，不影响query和execute方法,参考`客户端超时规则`
            'charset'     => '字符集',
        )
];