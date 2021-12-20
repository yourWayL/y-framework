<?php


namespace Secxun\Extend\Eller\Database;

use Secxun\Extend\Eller\ConfigTrait;

use Swoole\Coroutine\MySQL;

class Connect
{
    use ConfigTrait;

    private $config = [];

    // 数据库配置连接标志
    private $key;

    private $transaction_id = null;
    private static $resource = null;

    public function __construct($key = "db_master")
    {
        $this->key = $key;
//        $this->config = self::$conf[$key];
    }

    /**
     * 获取数据库连接标志
     * @return string
     * @author ELLER
     */
    public function getKey()
    {
        return $this->key;
    }

    public function transactionId($id)
    {
        $this->transaction_id = $id;
    }

    /**
     * 执行SQL
     * @param $sql
     * @param array $data
     * @return array|bool
     * @throws DbException
     * @author ELLER
     */
    protected function execute($sql, $data = [])
    {
        try {
            $handle = $this->getRes();
            $res = $handle->prepare($sql);
            if (!$res) {
                $this->writeLog(json_encode([$handle->errno, $handle->error]));
                throw new DbException(json_encode([$handle->errno, $handle->error]));
                return false;
            } else {
                $this->writeLog($sql, $data);
                return [$res->execute($data), $res];
            }
        } catch (\Throwable $e) {
            $this->writeLog(json_encode(['info' => $e->getMessage(), 'sql' => $sql]));
            throw new DbException(json_encode(['info' => $e->getMessage(), 'sql' => $sql]), $e->getCode());
        }
    }

    /**
     * 获取MySQL链接
     * @return MySQL|null
     * @author ELLER
     */
    protected function getRes()
    {
        self::$resource = null;// 一步携程机制资源不能复用，必须重新建立sql链接
        if (self::$resource == null) {
            $httpConfig = require ROOT_PATH . DS . 'config' . DS . 'database.php';
            $swoole_mysql = new MySQL();
            $httpConfig[$this->key]['fetch_mode'] = true;
            try {
                $connectRes = $swoole_mysql->connect($httpConfig[$this->key]);
            } catch (\Exception $exception) {
                var_dump($exception->getMessage());
                throw new $exception;
            }
            self::$resource = $swoole_mysql;
        }
        return self::$resource;
    }

    /**
     * 获取一条数据
     * @param $sql
     * @param $data
     * @return mixed
     * @throws DbException
     * @author ELLER
     */
    public function find($sql, $data = [])
    {
        // 兼容swoole4.4的fetch单条问题
        // 参见：https://wiki.swoole.com/wiki/page/942.html
        if (strpos($sql, " limit ") == false) {
            $sql = rtrim($sql);
            if (substr($sql, -1, 1) == ";") {
                $sql = rtrim($sql, ";");
            }
            $sql .= " LIMIT 1";
        }
        list($result, $res) = $this->execute($sql, $data);
        return $result ? $res->fetch() : $result;
    }

    /**
     * 获取所有数据
     * @param $sql
     * @param $data
     * @return array|bool
     * @throws DbException
     * @author ELLER
     */
    public function findAll($sql, $data = [])
    {
        list($result, $res) = $this->execute($sql, $data);
        return $result ? $res->fetchAll() : $result;
    }

    /**
     * 执行SQL语句
     * @param $sql
     * @param array $data
     * @param bool $lastId 是否获取插入ID | 影响条数
     * @return mixed
     * @throws DbException
     * @author ELLER
     */
    public function exec($sql, $data = [], $lastId = false)
    {
        list($result, $res) = $this->execute($sql, $data);
        if (isset($res->error) && !empty($res->error)) {
            throw new DbException($res->error, $res->errno);
        }
        if ($lastId) {
            $r = $res->insert_id;
        } else {
            $r = $res->affected_rows;
        }
        return $r;
    }

    /**
     * 写入日志
     *
     * @param $sql
     * @param array $data
     */
    protected function writeLog($sql, $data = [])
    {
        $logger = require_once ROOT_PATH . DS . 'config' . DS . 'logger.php';

        if (isset($logger['database_log'])) {
            $dirname = dirname($logger['database_log']);
            if (is_dir($dirname)) {
                $filename = basename($logger['database_log']);
                if (strpos($filename, '.') !== false) {
                    $filename = preg_replace('/(\.)[^\.]+/is', date('-Ymd') . '$0', 'mysql.txt');
                } else {
                    $filename .= date('-Ymd');
                }
                $logPath = $dirname . DIRECTORY_SEPARATOR . $filename;
                $sql = vsprintf(str_replace('?', '%s', $sql), $data);
                $logText = sprintf('[%s] %s' . PHP_EOL, date('Y-m-d H:i:s'), $sql);
                file_put_contents($logPath, $logText, FILE_APPEND);
            }
        }
    }

}