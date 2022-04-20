<?php
declare(strict_types=1);
/**
 * @category: Secxun\Core
 * @description: 框架提供的Websocket类
 * @author yourway <lyw@secxiun.com>
 * @copyright 深圳安巽科技有限公司 <https://www.secxun.com>
 */

namespace Secxun\Core;

use Secxun\Core\Route;
use Secxun\Core\Cache;
use Secxun\Core\Mysql;

class Websocket
{
    /**
     * 响应资源
     * @var Resources
     */
    private $webSocketServer;
    /**
     * websocket 系统参数信息
     *
     */
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
    protected function webSocketStart()
    {
        $Config = require_once ROOT_PATH . DS . 'config' . DS . 'app.php';
        $this->webSocketServer = new \Swoole\WebSocket\Server($Config['host'], $Config['port']);
        $this->webSocketServer->set(array(
            'worker_num' => $Config['http_workerNum'],
            'daemonize' => $Config['http_daemonize'],
            'max_request' => $Config['http_maxRequest'],
        ));
        $this->Config = $Config;
        $this->webSocketOpen();
        echo '---------------------------------------------------' . "\r\n";
        echo '| Websocket Service open successfully!            |' . "\r\n";
        echo '---------------------------------------------------' . "\r\n";
        $this->webSocketMessage();
        $this->httpRequest();
        echo '| httpRequest Service open successfully!          |' . "\r\n";
        echo '---------------------------------------------------' . "\r\n";
        echo "| Service addr:{$Config['host']}:{$Config['port']}                       |" . "\r\n";
        echo '---------------------------------------------------' . "\r\n";
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
            $this->updateRequestUriFdInfo($requestUri, $request->fd);
            $cache::coreSet('fd' . $request->fd, $requestUri, 'fd');
            $result = $routeData->isSetRoute('websocket', $requestUri, $request, $server, 'onOpen', $this->webSocketServer);
            if ($result['code'] == 200) {
                if ($this->webSocketServer->exist($request->fd)) {
                    $server->push($request->fd, json_encode($result));
                }
            } else {
                $this->webSocketCloseFd($request->fd, $result['msg']);
            }
        });
    }

    /**
     * 当服务器收到来自客户端的数据帧时会回调此函数。
     */
    protected function webSocketMessage()
    {
        $this->webSocketServer->on('message', function (\Swoole\WebSocket\Server $server, $frame) {
            $routeData = new Route();
            $cache = new Cache();
            $requestUri = $cache::coreGet('fd' . $frame->fd, 'fd');
            $result = $routeData->isSetRoute('websocket', $requestUri, $frame, $server, 'onMessage', $this->webSocketServer);
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
            $this->fillGlobalRequest($request);
            if ($requestUri == '/favicon.ico') {
                $response->end();
            } else {
                $routeData = new Route();
                $result = $routeData->isSetRoute('http', $requestUri, $request, $response, 'http', $this->webSocketServer);
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
            $requestUri = $cache::coreGet('fd' . $fd, 'fd');
            $connect = $server->connection_info($fd);
            if ($connect['websocket_status'] > 0) {
                $routeData->isSetRoute('websocket', $requestUri, $fd, $server, 'onClose', $this->webSocketServer);
            }
        });
    }


    /**
     * 更新缓存中对应路由的客户端池
     * @param $requestUri
     * @param $fdId
     * @return array|bool|mixed
     */
    protected function updateRequestUriFdInfo($requestUri, $fdId)
    {

        $cache = new Cache();
        $requestUri = str_replace("/", ",", $requestUri);
        $requsetCacheInfo = $cache::coreGet($requestUri, 'uri');
        if ($requsetCacheInfo) {
            if (empty($requsetCacheInfo[$fdId])) {
                $requsetCacheInfo[$fdId] = $fdId;
                $cache::coreSet($requestUri, $requsetCacheInfo, 'uri');
            }
        } else {
            $requsetCacheInfo[$fdId] = $fdId;
            $cache::coreSet($requestUri, $requsetCacheInfo, 'uri');
        }
        return $requsetCacheInfo;
    }

    /**
     * @param $requestUri
     * @param $fdId
     * @return bool|mixed
     */
    protected function getRequsetUrlFdInfo($requestUri)
    {
        $cache = new Cache();
        $requestUri = str_replace("/", ",", $requestUri);
        return $cache::coreGet($requestUri, 'uri');
    }

    /**
     * 标注header头
     * Author: yourway <lyw@secxun.com>
     * @param $request
     * @param $response
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
     * @param $response
     * @param string $result
     * @param int $type
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
     * @param $fd
     * @param $msg
     */
    protected function webSocketCloseFd($fd, $msg)
    {
        $server = $this->webSocketServer;
        @$server->push($fd, $msg);
        @$server->disconnect($fd);
    }

    /**
     * 路由级别的群发事件
     * @param $uri
     * @param $msg
     * @param $resources
     * @param $fd
     */

    public function sendRouteMessage($uri, $msg, $resources, $fd)
    {
        $requsetUri = $this->getRequsetUrlFdInfo($uri);
        foreach ($requsetUri as $key => $value) {
            if ($key == $fd) {
                continue;
            }
            if ($resources->exist($key)) {
                $resources->push($key, $msg);
            }
        }
    }

    /**
     * 全员级别的群发事件
     * @param $msg
     * @param $resources
     * @param $fd
     */

    public function sendAllMessage($msg, $resources, $fd)
    {
        $cache = new Cache();
        $allRequestUri = $cache::coreGetAll('uri');
        foreach ($allRequestUri as $key => $value) {
            if ($key == $fd) {
                continue;
            }
            if ($resources->exist($key)) {
                $resources->push($key, $msg);
            }
        }
    }

    /**
     * 给指定用户的信息推送事件
     * @param $msg
     * @param $resources
     * @param $fd
     */

    public function sendFdidMessage($msg, $resources, $fd)
    {
        if ($resources->exist($fd)) {
            $resources->push($fd, $msg);
        }
    }

    /**
     * 根据 FD 获取 路由信息
     * @param Int $fdid
     * @return string
     */
    public static function byFdidGetRequestURI(int $fdid)
    {
        $cache = new Cache();
        $requsetUri = Cache::coreGet('fd' . $fdid, 'fd');
        return (string)$requsetUri;
    }

    /**
     * 填充全局请求数据
     *
     * @param $request
     */
    protected function fillGlobalRequest($request)
    {
        $_GET = $request->get ?? [];
        $_POST = $request->post ?? [];
        $_COOKIE = $request->cookie ?? [];
        $_FILES = $request->files ?? [];
        $_SERVER = array_merge($request->server ?? [], $request->header ?? []);
    }

}