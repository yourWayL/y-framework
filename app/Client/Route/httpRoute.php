<?php
// ----------------------------------------------------------------------
// | secPHP                                                             |
// ----------------------------------------------------------------------
// | Copyright (c) 2016-2019 https://www.secxun.com All rights reserved.|
// ----------------------------------------------------------------------
// | Author: yourway <lyw@secxun.com>                                   |
// ----------------------------------------------------------------------
$modularName = 'Client/';
$modularApi = 'Client/Http/Api/';

/*GET（SELECT）：从服务器查询，可以在服务器通过请求的参数区分查询的方式。
POST（CREATE）：在服务器新建一个资源，调用insert操作。
PUT（UPDATE）：在服务器更新资源，调用update操作。
DELETE（DELETE）：从服务器删除资源，调用delete语句
HEAD：请求一个与GET请求的响应相同的响应，但没有响应体.
CONNECT ：建立一个到由目标资源标识的服务器的隧道。
OPTIONS ： 用于描述目标资源的通信选项。
TRACE ： 沿着到目标资源的路径执行一个消息环回测试。
PATCH ： 用于对资源应用部分修改。
*/

return [
    // 接参方式     前端访问地址                   后端对应方法

    'POST' => [
        $modularName . 'Login/index1' => $modularApi . 'Login/index',
        $modularName . 'Login/index2' => $modularApi . 'Login/index2',
        $modularName . 'Login/index3' => $modularApi . 'Login/index2',
    ],
    'GET' => [
        $modularName . 'Login/index3' => $modularApi . 'Login/index2',
    ],
    'PUT' => [
        $modularName . 'Login/index4' => $modularApi . 'Login/index2',
        $modularName . 'Login/index3' => $modularApi . 'Login/index2',
    ],
    'DELETE' => [
        $modularName . 'Login/index5' => $modularApi . 'Login/index2',
        $modularName . 'Login/index3' => $modularApi . 'Login/index2',
    ],
    'patch' => [
        $modularName . 'Login/index5' => $modularApi . 'Login/index2',
        $modularName . 'Login/index3' => $modularApi . 'Login/index2',
    ],
];
