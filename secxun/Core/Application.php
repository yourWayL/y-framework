<?php
declare(strict_types=1);
// ----------------------------------------------------------------------
// | secPHP                                                             |
// ----------------------------------------------------------------------
// | Copyright (c) 2016-2021 https://www.secxun.com All rights reserved.|
// ----------------------------------------------------------------------
// | Author: yourway <lyw@secxun.com>                                   |
// ----------------------------------------------------------------------

namespace Secxun\Core;


use ReflectionClass;

class Application
{
    // 获得类的对象实例
    public static function getInstance($className)
    {
        $paramArr = self::getMethodParams($className);
        try {
            return (new ReflectionClass($className))->newInstanceArgs($paramArr);
        } catch (\ReflectionException $e) {
        }
    }

    /**
     * 执行类的方法
     * @param $className
     * @param $methodName
     * @param array $params
     * @return mixed [type]             [description]
     */
    public static function make($className, $methodName, array $params = [])
    {
        // 获取类的实例
        $instance = self::getInstance($className);
        // 获取该方法所需要依赖注入的参数
        $paramArr = self::getMethodParams($className, $methodName);
        return $instance->{$methodName}(...array_merge($paramArr, $params));
    }

    /**
     * 获得类的方法参数，只获得有类型的参数
     * @param  [type] $className   [description]
     * @param string $methodsName
     * @return array [type]              [description]
     */
    protected static function getMethodParams($className, string $methodsName = '__construct')
    {

        // 通过反射获得该类
        $class = new ReflectionClass($className);
        $paramArr = []; // 记录参数，和参数类型
        // 判断该类是否有构造函数
        if ($class->hasMethod($methodsName)) {
            // 获得构造函数
            $construct = $class->getMethod($methodsName);
            // 判断构造函数是否有参数
            $params = $construct->getParameters();
            if (count($params) > 0) {
                // 判断参数类型
                foreach ($params as $key => $param) {
                    if ($paramClass = $param->getClass()) {
                        // 获得参数类型名称
                        $paramClassName = $paramClass->getName();
                        // 获得参数类型
                        $args = self::getMethodParams($paramClassName);
                        try {
                            $paramArr[] = (new ReflectionClass($paramClass->getName()))->newInstanceArgs($args);
                        } catch (\ReflectionException $e) {
                        }
                    }
                }
            }
        }
        return $paramArr;
    }
}