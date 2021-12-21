<?php
declare(strict_types=1);
/**
 * @category: Secxun\Core
 * @description: 框架提供的路由解析
 * @author yourway <lyw@secxiun.com>
 * @copyright 深圳安巽科技有限公司 <https://www.secxun.com>
 * @create: 2020 - 03 - 16
 */


namespace Secxun\Core;


class Route
{
    /**
     * http路由配置信息
     * @var array
     */
    protected static $httpRouteConfig;

    /**
     * websocket 路由配置信息
     * @var array
     */
    protected static $webSocketRouteConfig;

    /**
     * 获取各个项目Http路由配置
     * Author: yourway <lyw@secxun.com>
     */
    public function setHttpConfig()
    {
        $filepath = ROOT_PATH . DS . 'app' . DS;
        $temp = scandir($filepath);
        $projectRouteConfigInitialize = array();
        $httpRoute = array();
        foreach ($temp as $v) {
            $a = $filepath . '/' . $v;
            if (is_dir($a)) {
                if ($v == '.' || $v == '..') {
                    continue;
                }
                $projectRouteConfigInfo = require ROOT_PATH . DS . 'app' . DS . $v . DS . 'Route/httpRoute.php';
//                $projectRouteConfigInitialize = array_merge($projectRouteConfigInitialize, $projectRouteConfigInfo);
//                var_dump($projectRouteConfigInitialize);
                foreach ($projectRouteConfigInfo as $key => $value) {
                    foreach ($value as $r => $m) {
                        $httpRoute[strtolower($key)][$r] = $m;
                    }
                }
            }
        }
        var_dump($httpRoute);
        self::$httpRouteConfig = $httpRoute;
    }

    /**
     * 获取各个项目websocket路由配置
     * Author: yourway <lyw@secxun.com>
     */
    public function setWebSocketConfig()
    {
        $filepath = ROOT_PATH . DS . 'app' . DS;
        $temp = scandir($filepath);
        $projectRouteConfigInitialize = array();
        foreach ($temp as $v) {
            $a = $filepath . '/' . $v;
            if (is_dir($a)) {
                if ($v == '.' || $v == '..') {
                    continue;
                }
                $projectRouteConfigInfo = require ROOT_PATH . DS . 'app' . DS . $v . DS . 'Route/websocketRoute.php';
                $projectRouteConfigInitialize = array_merge($projectRouteConfigInitialize, $projectRouteConfigInfo);
            }
        }
        self::$webSocketRouteConfig = $projectRouteConfigInitialize;
    }

    /**
     * 验证路由是否存在
     * Author: yourway <lyw@secxun.com>
     * @param $requestMethod
     * @param $requestUri
     * @param $request
     * @param $response
     * @param string $step
     * @return array
     */
    public function isSetRoute($requestMethod, $requestUri, $request, $response, $step = '', $resources)
    {
        if ($requestMethod == 'http') {
            $requestUri = trim($requestUri, '/');
            $requestUri = trim($requestUri, '\\');
            $requestUri = trim($requestUri, '/');
            $request_method = strtolower($request->server['request_method']);
            self::setHttpConfig();
            if (empty(self::$httpRouteConfig[$request_method][$requestUri])) {
                return 404;
            } else {
                return $this->SearchHttpApi(self::$httpRouteConfig[$request_method][$requestUri], $request, $response);
            }
        } elseif ($requestMethod == 'websocket') {
            $requestUri = trim($requestUri, '/');
            $requestUri = trim($requestUri, '\\');
            $requestUri = trim($requestUri, '/');
            self::setWebSocketConfig();
            if (empty(self::$webSocketRouteConfig[$requestUri])) {
                $arr['code'] = 404;
                $arr['msg'] = 'ERROR:未找到指定路由接口,请确认相关路由信息';
                return $arr;
            } else {
                return $this->SearchWebSocketApi(self::$webSocketRouteConfig[$requestUri], $request, $step, $resources);
            }
        }
    }

    /**
     * 匹配API入口并执行
     * Author: yourway <lyw@secxun.com>
     * @param $route
     * @param $route
     * @param $request
     * @param $response
     * @description 修改 如果抛出异常 -- 状态码 为 999 | 则 提取出来
     * @return string
     * @author yourway
     * @date 2020/4/30 15:11
     */
    protected function SearchHttpApi($route, $request, $response)
    {
        try {
            $httpRouteConfig = explode('/', $route);
            $api = "\\App\\" . ucfirst($httpRouteConfig['0']) . '\\' . ucfirst($httpRouteConfig['1']) . '\\' . ucfirst($httpRouteConfig['2']) . '\\' . ucfirst($httpRouteConfig['3']);
            $newFunction = new $api($request, $response);
            $functionName = $httpRouteConfig[4];
            $result = $newFunction->$functionName($request, $response);
            return $result;
        } catch (\Exception $exception) {
            if ($exception->getCode() == 999) {
                $result = $exception->getMessage();
                return $result;
            }
            $result = $exception->getMessage();
            return $result;
        }
    }

    protected function SearchWebSocketApi($route, $request, $step, $resources)
    {
        switch ($step) {
            case 'onOpen' :
                $webSocketRouteConfig = $route[0];
                break;
            case 'onMessage' :
                $webSocketRouteConfig = $route[1];
                break;
            case 'onClose' :
                $webSocketRouteConfig = $route[2];
                break;
        }
        $routeConfig = explode('/', $webSocketRouteConfig);
        $api = "\\App\\" . ucfirst($routeConfig['0']) . '\\' . ucfirst($routeConfig['1']) . '\\' . ucfirst($routeConfig['2']) . '\\' . ucfirst($routeConfig['3']);
        $newFunctionName = new $api($request);
        $functionName = $routeConfig[4];
        $result = $newFunctionName->$functionName($request, $resources);
        return $result;
    }
}