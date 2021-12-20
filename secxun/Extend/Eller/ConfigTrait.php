<?php


namespace Secxun\Extend\Eller;


trait ConfigTrait
{
    protected static $conf = [];
    public static function setConfig($config)
    {
        self::$conf = $config;
    }
}