<?php
declare(strict_types=1);
/**
 * @category: Secxun\Core
 * @description: 框架提供的Cache缓存类
 * @author yourway <lyw@secxiun.com>
 * @copyright 深圳安巽科技有限公司 <https://www.secxun.com>
 * @create: 2020 - 03 - 16
 */

namespace Secxun\Core;


class Cache
{
    /**
     * 生成底层缓存数据
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @param int $time
     */
    public static function coreSet(string $key, $value, string $type, int $time = 0)
    {
        $dir = ROOT_PATH . DS . 'runtime' . DS . 'cache' . DS . 'core' . DS . $type;
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        file_put_contents($dir . DS . $key, serialize($value));
    }

    /**
     * 取出底层所需缓存数据
     * @param string $key
     * @param string $type
     * @return bool|mixed
     */
    public static function coreGet(string $key, string $type)
    {
        $dir = ROOT_PATH . DS . 'runtime' . DS . 'cache' . DS . 'core' . DS . $type;
        if (file_exists($dir . DS . $key)) {
            $result = unserialize(file_get_contents($dir . DS . $key));
        } else {
            $result = false;
        }
        return @$result;
    }

    /**
     * 取出所需模块缓存的所有数据
     * @param string $type
     * @return array
     */
    public static function coreGetAll(string $type): array
    {
        $dir = ROOT_PATH . DS . 'runtime' . DS . 'cache' . DS . 'core' . DS . $type;
        $routeFile = scandir($dir);
        $returnArr = array();
        foreach ($routeFile as $v) {
            if ($v == '.' or $v == '..') {
                continue;
            }
            $getOutArr = unserialize(file_get_contents($dir . DS . $v));
            $returnArr = array_merge($returnArr, $getOutArr);
            unset($getOutArr);
        }
        return $returnArr;
    }

    public static function coreDel()
    {

    }
}