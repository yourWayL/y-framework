<?php


namespace Secxun\Core;
use Monolog\Handler\FirePHPHandler;
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;

/**
 * 日志封装
 * Class Log
 * @method static emergency($message, array $context = array());
 * @method static alert($message, array $context = array());
 * @method static critical($message, array $context = array());
 * @method static error($message, array $context = array());
 * @method static warning($message, array $context = array());
 * @method static notice($message, array $context = array());
 * @method static info($message, array $context = array());
 * @method static debug($message, array $context = array());
 * @package Secxun\Core
 */

class Log
{

    /**
     * 日志文件路径
     * @var string
     */
    protected static $logPath =  ROOT_PATH . DS . 'runtime' . DS . 'log' . DS;

    /**
     * 默认日志所属模块
     * @var string
     */
    protected static $module = 'app';

    /**
     * 是否自动获取模块
     * @var bool
     */
    protected static $moduleUpdateFlag = true;

    /**
     * 当前项目的所有模块
     * @var string[]
     */
    protected static $moduleList = ['Client', 'Manager', 'Wechat'];

    private static function getInstance(string $file)
    {
        $logger = new Logger('');
        $filePath = self::getPath() . DS .$file.'.log';
        // 默认的日期格式是 "Y-m-d H:i:s"
        $dateFormat = "Y-m-d H:i:s";
        // 默认的输出格式是 "[%datetime%] %channel%.%level_name%: %message% %contex
        $output    = "[%datetime%]  %level_name%: %message%;\n";
        $formatter = new LineFormatter($output, $dateFormat);
        $stream    = new StreamHandler($filePath, Logger::INFO);
        $stream->setFormatter($formatter);
        $logger->pushHandler($stream);
        $logger->pushHandler(new FirePHPHandler());
        return $logger;
    }

    public static function __callStatic($name, $arguments)
    {
        $file = isset($arguments[1]) &&  $arguments[1] ? current($arguments[1]) : 'app';
        return call_user_func_array([Log::getInstance($file), $name], $arguments);
    }

    /**
     * 生成日志目录
     * @return string
     */
    private static function getPath()
    {
        $time = date('Ymd');
        self::getModule();
        $modulePath = self::$logPath . self::$module;
        if (!is_dir($modulePath)){
            mkdir($modulePath,0777,true);
        }
        $monthPath = $modulePath . DS . $time;
        if (!is_dir($monthPath)){
            mkdir($monthPath,0777,true);
        }
        return $monthPath;
    }

    /**
     * 获取当前调用模块
     */
    public static function getModule()
    {
        $classList =  array_column(debug_backtrace(),'class');
        $module = self::$module;
        if(self::$moduleUpdateFlag && !empty($classList)){
            krsort($classList);
            $i = 1;
            foreach($classList as $class){
                if($i > 4){
                    break;
                }

                $list = explode('\\', $class);
                if(in_array($list[1],self::$moduleList)){
                    $module = $list[1];
                    break;
                }

                $i ++;
            }
        }
        self::$module = $module;
    }

}