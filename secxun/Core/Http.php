<?php
declare(strict_types=1);
/**
 * @category: Secxun\Core
 * @description: 框架提供的Http服务类
 * @author yourway <lyw@secxiun.com>
 * @copyright 深圳安巽科技有限公司 <https://www.secxun.com>
 * @create: 2020 - 03 - 16
 */

namespace Secxun\Core;

use Secxun\Core\Route;

class Http
{
//    /**
//     * 响应资源
//     * @var object
//     */
//    static private $response;
//    static private $request;
//
    private $httpServer;
    private $httpConfig;

    public function __construct()
    {
        $httpConfig = require_once ROOT_PATH . DS . 'config' . DS . 'app.php';
        $this->httpServer = new \Swoole\Http\Server($httpConfig['http_host'], $httpConfig['http_port']);
        $this->httpServer->set(array(
            'worker_num' => $httpConfig['http_workerNum'],
            'daemonize' => $httpConfig['http_daemonize'],
            'max_request' => $httpConfig['http_maxRequest'],
            'heartbeat_check_interval ' => 30,
            'heartbeat_idle_time' => 60,
            'open_cpu_affinity ' => 1,
            'dispatch_mode  ' => 1,
        ));
        $this->httpConfig = $httpConfig;
    }

    public function httpServerStart()
    {
        $this->httpServer->on('request', function ($request, $response) {
            $requestUri = $request->server['request_uri'];
            if ($requestUri == '/favicon.ico') {
                $response->end();
            } else {
                $routeData = new Route($requestUri);
                try {
                    $result = $routeData->isSetRoute($requestUri, $request);
                } catch (\Throwable $exception) {
                    if ($this->httpConfig['http_debug'] == false) {
                        file_put_contents(
                            $this->httpConfig['http_logFile'],
                            $exception->getMessage() . PHP_EOL,
                            FILE_APPEND);
                        $result = '系统出错';
                    } else {
                        $result = $exception->getMessage();
                    }
                    $this->header($request, $response);
                    $this->end($response, $result, 500);
                    return;
                }
                if ($result == 404) {
                    $this->header($request, $response);
                    $this->end($response, '', 404);
                } else {
                    $this->header($request, $response);
                    $this->end($response, $result);
                }
            }
        });
        \swoole_set_process_name(sprintf('php-ps:%s', 'httpWorker'));
        $this->httpServer->start();
    }

    /**
     * 标注header头
     * Author: yourway <lyw@secxun.com>
     */
    public function header($request, $response)
    {
        $response->header("access-control-allow-credentials", "true，true");
        $response->header("access-control-allow-headers", "Origin, X-Requested-With, Content-Type, Accept,Authorization");
        $response->header("access-control-allow-origin", "*");
        $response->header("Content-Type", "application/json;charset=utf-8");
        $response->header("date", date('Y-m-d H:i:s'));
    }

    public function end($response, $result = '', $type = 200)
    {
        if ($type == 404) {
            $response->end("ERROR:未找到指定路接口,请确认相关信息");
        } else {
            $response->end($result);
        }

    }

}