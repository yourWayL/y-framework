<?php
declare(strict_types=1);
/**
 * @category: Secxun\Core
 * @description: 框架提供的PROCESS管理类
 * @author yourway <lyw@secxiun.com>
 * @copyright 深圳安巽科技有限公司 <https://www.secxun.com>
 * @create: 2020 - 03 - 16
 */

namespace Secxun\Core;


use Exception;

class Process
{
    /**
     * 进程ID
     * @var int
     */
    public $mpid = 0;
    /**
     * 运行的进程池信息
     * @var array
     */
    public $works = [];
    /**
     * 进程数量
     * @var int
     */
    public $maxPrecess;
    /**
     * Crontab配置信息
     * @var array
     */
    public $getProcessConfig;
    /**
     * HTTP服务配置信息
     * @var array
     */
    public $getHttpProcessConfig;
    /**
     * 进程是否开启日志
     * @var array
     */
    public $workLogEnabled = array();

    /**
     * PHP BIN
     * @var string
     */
    private $phpBin;


    /**
     * Process constructor.
     */
    public function __construct()
    {
        try {
            $this->phpBin = $this->getEnvPHP();
            $this->getProcessConfig = require_once ROOT_PATH . DS . 'crontab/crontab.php';
            $this->getHttpProcessConfig = require_once ROOT_PATH . DS . 'config/app.php';
            \swoole_set_process_name(sprintf('php-ps:%s', 'master'));
            $this->mpid = getmypid();
            $this->run();
            $this->processWait();
        } catch (Exception $e) {
            die('ALL ERROR: ' . $e->getMessage());
        }
    }

    /**
     * 执行创建进程操作
     * @return void
     */
    public function run()
    {
        $this->maxPrecess = count($this->getProcessConfig) + 1;
        if ($this->getHttpProcessConfig['http_enabled']) {
            $runStart = 1;
        } else {
            $runStart = 2;
        }
        for ($i = $runStart; $i <= $this->maxPrecess; $i++) {
            if ($i == 1) {
                $this->CreateHttpProcess($i);
            } else {
                $this->workLogEnabled[$i - 2] = $this->getProcessConfig[$i - 2]['workLog'];
                $this->CreateProcess($i);
            }
        }
    }

    /**
     * 创造子进程
     * @param int $index
     * @return mixed
     */
    public function CreateProcess(int $index)
    {
        $process = new \swoole_process(function (\swoole_process $worker) use ($index) {
            if(!empty($this->getProcessConfig[$index - 2]['interval'])){
                sleep((int)$this->getProcessConfig[$index - 2]['interval']);
            }
            $worker->exec($this->phpBin, array($this->getProcessConfig[$index - 2]['workDir']));
        }, false, 1, false);
        $pid = $process->start();
        $this->works[$index] = $pid;
        if ($this->workLogEnabled[$index - 2]) {
            $workName = $this->getProcessConfig[$index - 2]['workName'];
            $log = date('Y-m-d H:i:s') . " Worker {$this->getProcessConfig[$index - 2]['workName']} process successful activation, process id is [{$pid}]\n";
            $this->createLog($workName, $log);
        }
        return $pid;
    }

    /**
     * 创建http服务进程
     * @param int $index
     * @return int
     */
    public function CreateHttpProcess(int $index): int
    {
        $process = new \swoole_process(function (\swoole_process $worker) use ($index) {
            $worker->exec($this->phpBin, array(ROOT_PATH . '/bin/WebSocket.php'));
        }, false, 1, false);
        $pid = $process->start();
        $workName = 'WebSocket';
        $log = date('Y-m-d H:i:s') . " httpWorker process successful activation, process id is [{$pid}]\n";
        $this->createLog($workName, $log);
        $this->works[$index] = $pid;
        return $pid;
    }

    /**
     * 验证进程是否下线
     * @param $worker
     * @return void
     */
    public function checkMpid(&$worker)
    {
        if (!\swoole_process::kill($this->mpid, 0)) {
            $worker->exit();
            // 这句提示,实际是看不到的.需要写到日志中(nohup)
            echo "Master process exited, I [{$worker['pid']}] also quit\n";
        }
    }

    /**
     * 生成执行日志
     * @param string $workName
     * @param string $log
     * @return void
     */
    public function createLog(string $workName, string $log)
    {
        $logFilePath = ROOT_PATH . DS . 'runtime/log/process/';
        $filePath = $logFilePath . $workName . '.log';
        if (!file_exists($filePath)) {
            \Swoole\Coroutine::create(function () use ($filePath, $log) {
                file_put_contents($filePath, $log);
            });
        } else {
            \Swoole\Coroutine::create(function () use ($filePath, $log) {
                file_put_contents($filePath, $log, FILE_APPEND);
            });
        }
    }

    /**
     * 重启服务进程
     * @param array $ret
     * @throws Exception
     */
    public function rebootProcess(array $ret)
    {
        $pid = $ret['pid'];
        $index = array_search($pid, $this->works);
        if ($index !== false and $index !== 1) {
            $index = intval($index);
            $new_pid = $this->CreateProcess($index);
            //echo "rebootProcess: {$index}={$new_pid} Done\n";
            return;
        } else {
            $index = intval($index);
            $new_pid = $this->CreateHttpProcess($index);
            //echo "rebootProcess: {$index}={$new_pid} Done\n";
            return;
        }
        throw new Exception('rebootProcess Error: no pid');
    }

    /**
     * 回收结束的子进程进程
     * @throws Exception
     */
    public function processWait()
    {
        while (1) {
            if (count($this->works)) {
                $ret = \swoole_process::wait();
                if ($ret) {
                    $this->rebootProcess($ret);
                }
            } else {
                break;
            }
        }
    }

    /**
     * 获取当前环境PHP
     * @return string|null
     * @author ELLER
     */
    public function getEnvPHP()
    {
        return trim(shell_exec(sprintf('realpath /proc/%s/exe', getmypid())));
    }
}