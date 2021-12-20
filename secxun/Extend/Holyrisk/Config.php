<?php

namespace Secxun\Extend\Holyrisk;

class Config
{

    /**
     * @var array 获取的配置参数
     */
    private static $config = [];

    /**
     * @var string 设置默认值
     */
    protected static $arrKey = 'default';

    /**
     * @description 获取 配置
     * @author Holyrisk
     * @date 2019/12/18 12:12
     * @param string $fileName 指定的配置文件 | 最外层 必须 是 数组
     * @param string $arrKey 获取配置数组的 key | 二级配置
     * @param string $fileKey 获取配置文件的 key | 一级配置
     * @param string $app 获取对应的应用，不填则获取最外层的Config文件夹下的配置文件
     * @return array|mixed
     */
    public static function get($fileName = 'database',$arrKey = '',$fileKey = 'default',$app='')
    {
        $config = [];
        //获取  配置
        $arr = self::requestData($fileName,$app);
        if (!empty($arr))
        {
            //过滤
            //$checkArr = self::check();
            $checkArr = $arr;
            if (!empty($checkArr))
            {
                if (!empty($fileKey))
                {
                    //获取指定的 key
                    //判断 key 是否 存在
                    if (isset($checkArr[$fileKey]))
                    {
                        $fileArr = $checkArr[$fileKey];
                        if (!empty($arrKey))
                        {
                            if (isset($fileArr[$arrKey]))
                            {
                                $config = $fileArr[$arrKey];
                            }
                        }
                        else
                        {
                            $config = $fileArr;
                        }
                        unset($fileArr);
                    }
                }
                else
                {
                    $config = $checkArr;
                }
            }
            unset($checkArr);
        }
        return $config;
    }

    /**
     * @description 导入配置文件
     * @author Holyrisk
     * @date 2019/12/17 19:40
     * @param string $fileName
     * * @param string $app
     * @return array|mixed
     */
    public static function requestData($fileName = 'database',$app='')
    {
        $pathConfig = $app?ROOT_PATH . DS.'app/'.ucfirst($app).'/Config'. DS . $fileName.'.php':ROOT_PATH . DS . 'config' . DS . $fileName.'.php';
        if (isset(self::$config[$fileName]) == false)
        {
            if (is_file($pathConfig))
            {
                $config = require_once $pathConfig;
                if (is_array($config))
                {
                    self::$config[$fileName] = $config;
                }
                unset($config);
            }
        }
        return self::$config[$fileName];
    }

}