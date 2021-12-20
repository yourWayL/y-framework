<?php

namespace Secxun\Core;

use Secxun\Core\Route;
use Secxun\Core\Cache;

class Websocket__Backup
{
//    /**
//     * 响应资源
//     * @var object
//     */
    private $webSocketServer;
    private $Config;

    public function __construct($initialize = false)
    {
        if ($initialize) {
            $this->webSocketStart();
        }
    }

    /**
     * 启动 websocket 服务
     */
    public function webSocketStart()
    {
        $Config = require_once ROOT_PATH . DS . 'config' . DS . 'app.php';
        $this->webSocketServer = new \Swoole\WebSocket\Server($Config['host'], $Config['port']);
        $this->webSocketServer->set(array(
            'worker_num' => $Config['http_workerNum'],
            'daemonize' => $Config['http_daemonize'],
            'max_request' => $Config['http_maxRequest'],
            'heartbeat_check_interval ' => 30,
            'heartbeat_idle_time' => 60,
            'open_cpu_affinity ' => 1,
            'dispatch_mode  ' => 1,
        ));
        $this->Config = $Config;
        $this->webSocketOpen();
        $this->webSocketMessage();
        $this->httpRequest();
        $this->webSocketClose();
        $this->webSocketServer->start();
    }

    /**
     * 当WebSocket客户端与服务器建立连接并完成握手后会回调此函数
     */
    protected function webSocketOpen()
    {
        $this->webSocketServer->on('open', function (\Swoole\WebSocket\Server $server, $request) {
            //客户端标识码 $request->fd;
            $requestUri = $request->server['request_uri'];
            $routeData = new Route();
            $cache = new Cache();
            try {
                $cache::coreSet('fd' . $request->fd, $requestUri);
                $result = $routeData->isSetRoute('websocket', $requestUri, $request, $server, 'onOpen');
            } catch (\Throwable $exception) {
                if ($this->Config['http_debug'] == false) {
                    file_put_contents(
                        $this->Config['websocket_logFile'],
                        $exception->getMessage() . PHP_EOL,
                        FILE_APPEND);
                    $result['msg'] = '系统出错';
                } else {
                    $result = $exception->getMessage();
                }
            }
            if ($result['code'] == 200) {
                if ($this->webSocketServer->exist($request->fd)) {
                    $server->push($request->fd, json_encode($result));
                    $timerId = \Swoole\Timer::tick(1000, function () use ($request) {
                        $this->timerCheck($request);
                    });
                    $cache::coreSet('timer' . $request->fd, $timerId);
                }
            } else {
                $this->webSocketCloseFd($request->fd, $result['msg']);
            }
        });
    }

    /**
     * 当服务器收到来自客户端的数据帧时会回调此函数。
     */
    public function webSocketMessage()
    {
        $this->webSocketServer->on('message', function (\Swoole\WebSocket\Server $server, $frame) {
            $routeData = new Route();
            try {
                $cache = new Cache();
                $requestUri = $cache::coreGet('fd' . $frame->fd);
                $result = $routeData->isSetRoute('websocket', $requestUri, $frame, $server, 'onMessage');
            } catch (\Throwable $exception) {
                if ($this->Config['http_debug'] == false) {
                    file_put_contents(
                        $this->Config['websocket_logFile'],
                        $exception->getMessage() . PHP_EOL,
                        FILE_APPEND);
                    $result['msg'] = '系统出错';
                } else {
                    $result = $exception->getMessage();
                }
            }
            if ($result['code'] == 200) {
                if ($this->webSocketServer->exist($frame->fd)) {
                    $server->push($frame->fd, json_encode($result));
                }
            } else {
                $this->webSocketCloseFd($frame->fd, $result['msg']);
            }
        });
    }

    /**
     * websocket中的 HTTP 监听
     */
    protected function httpRequest()
    {
        $this->webSocketServer->on('request', function ($request, $response) {
            $requestUri = $request->server['request_uri'];

            if ($requestUri == '/favicon.ico') {
                $response->end();
            } else {
                $routeData = new Route();
                try {
                    $result = $routeData->isSetRoute('http', $requestUri, $request, $response);
                } catch (\Throwable $exception) {
                    if ($this->Config['http_debug'] == false) {
                        file_put_contents(
                            $this->Config['http_logFile'],
                            $exception->getMessage() . PHP_EOL,
                            FILE_APPEND);
                        $result = '系统出错';
                    } else {
                        $result = $exception->getMessage();
                    }
                    $this->header($request, $response);
                    $this->httpEnd($response, $result, 500);
                }
                if ($result == 404) {
                    $this->header($request, $response);
                    $this->httpEnd($response, '', 404);
                } else {
                    $this->header($request, $response);
                    $this->httpEnd($response, $result);
                }
            }
        });
    }

    /**
     * websocket 客户端关闭连接事件
     */
    protected function webSocketClose()
    {
        $this->webSocketServer->on('close', function (\Swoole\WebSocket\Server $server, $fd) {
            $routeData = new Route();
            $cache = new Cache();
            try {
                $requestUri = $cache::coreGet('fd' . $fd);
                $routeData->isSetRoute('websocket', $requestUri, $fd, $server, 'onClose');
            } catch (\Throwable $exception) {
                if ($this->Config['http_debug'] == false) {
                    file_put_contents(
                        $this->Config['websocket_logFile'],
                        $exception->getMessage() . PHP_EOL,
                        FILE_APPEND);
                    $result['msg'] = '系统出错';
                } else {
                    $result = $exception->getMessage();
                }
            }
        });
    }

    /**
     * 定时广播事件
     */
    protected function timerCheck($request)
    {
        $cache = new Cache();
        if ($this->webSocketServer->exist($request->fd)) {
            $routeData = new Route();
            $requestUri = $cache::coreGet('fd' . $request->fd);
            try {
                $cache = new Cache();
                $cache::coreSet('fd' . $request->fd, $requestUri);
                $result = $routeData->isSetRoute('websocket', $requestUri, $request, $this->webSocketServer, 'timer');
            } catch (\Throwable $exception) {
                if ($this->Config['http_debug'] == false) {
                    file_put_contents(
                        $this->Config['websocket_logFile'],
                        $exception->getMessage() . PHP_EOL,
                        FILE_APPEND);
                    $result['msg'] = '系统出错';
                } else {
                    $result = $exception->getMessage();
                }
            }

            if ($result['code'] == 200) {
                if ($this->webSocketServer->exist($request->fd)) {
                    if ($this->webSocketServer->exist($request->fd)) {
                        $this->webSocketServer->push($request->fd, json_encode($result));
                    } else {
                        $timerId = $cache::coreGet('timer' . $request->fd);
                    }
                }
            } else {
                $this->webSocketCloseFd($request->fd, $result['msg']);
                $timerId = $cache::coreGet('timer' . $request->fd);
//                \Swoole\Timer::clear($timerId);
            }
        } else {
            echo $request->fd . '客户端下线' . PHP_EOL;
            $timerId = $cache::coreGet('timer' . $request->fd);
//            \Swoole\Timer::clear($timerId);
        }
    }

    /**
     * 标注header头
     * Author: yourway <lyw@secxun.com>
     */
    protected function header($request, $response)
    {
        $response->header("access-control-allow-credentials", "true，true");
        $response->header("access-control-allow-headers", "Origin, X-Requested-With, Content-Type, Accept,Authorization");
        $response->header("access-control-allow-origin", "*");
        $response->header("Content-Type", "application/json;charset=utf-8");
        $response->header("date", date('Y-m-d H:i:s'));
    }

    /**
     * http 输出内容
     */
    protected function httpEnd($response, $result = '', $type = 200)
    {
        if ($type == 404) {
            $response->end("ERROR:未找到指定路由接口,请确认相关路由信息");
        } else {
            $response->end($result);
        }

    }

    /**
     * Websocket 关闭客户端连接事件
     */
    protected function webSocketCloseFd($fd, $msg)
    {
        $server = $this->webSocketServer;
        $server->push($fd, $msg);
        $server->disconnect($fd);
    }
}