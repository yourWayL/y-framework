<?php
// ----------------------------------------------------------------------
// | secPHP                                                             |
// ----------------------------------------------------------------------
// | Copyright (c) 2016-2019 https://www.secxun.com All rights reserved.|
// ----------------------------------------------------------------------
// | Author: yourway <lyw@secxun.com>                                   |
// ----------------------------------------------------------------------

define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', dirname(__DIR__));
ini_set('date.timezone', 'Asia/Shanghai'); // 'Asia/Shanghai' 为上海时区
require_once ROOT_PATH . DS . "secxun" . DS . "Helper" . DS . "function.php";

require_once ROOT_PATH . DS . 'vendor/autoload.php';

//验证子进程是否自立门户
function checkFatherIsset($workerName)
{
    $isSetFather = exec("ps -ef | grep {$workerName} | grep -v grep | awk '{print $3}'", $fatherPid);
    if (!empty($fatherPid)) {
        foreach ($fatherPid as $v) {
            if ($v == 1) {
                exit;
            }
        }
    }
}

//递归获取文件夹内文件
function getFileList($filepath)
{
    $temp = scandir($filepath);
    $filePathArray = array();
    foreach ($temp as $v) {
        $a = $filepath . '/' . $v;
        if (is_dir($a)) {
            if ($v == '.' || $v == '..') {
                continue;
            }
            $getFileArray = getFileList($a);
            if (!empty($getFileArray)) {
                foreach ($getFileArray as $value) {
                    $filePathArray[] = $value;
                }
                unset($value);
            }
        } else {
            $filePathArray[] = $a;
        }
    }
    unset($v);
    return $filePathArray;
}

//获取PHP执行命令
function getEnvPHP()
{
    return trim(shell_exec(sprintf('realpath /proc/%s/exe', getmypid())));
}

function recursiveDelete($dir)
{
    // 打开指定目录
    if ($handle = @opendir($dir)) {
        while (($file = readdir($handle)) !== false) {
            if (($file == ".") || ($file == "..")) {
                continue;
            }
            if (is_dir($dir . '/' . $file)) {
                // 递归
                recursiveDelete($dir . '/' . $file);
            } else {
                unlink($dir . '/' . $file); // 删除文件
            }
        }
        @closedir($handle);
    }
}

