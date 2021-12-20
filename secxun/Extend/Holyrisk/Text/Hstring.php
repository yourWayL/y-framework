<?php

namespace Secxun\Extend\Holyrisk\Text;

/**
 * 字符串 处理
 * Class String
 * @package Secxun\Extend\Holyrisk\Handle
 */

class Hstring
{

    /**
     * @description 字段类型 | 首字母 小写 中间内容 驼峰转 下划线
     * @author Holyrisk
     * @date 2020/3/14 17:41
     * @param $string
     * @return string
     */
    public  function fieldString($string)
    {
        $result = $this->camelize(lcfirst($string));
        return $result;
    }

    /**
     * @description 大写转换 下划线
     * @author Holyrisk
     * @date 2020/3/14 17:31
     * @param $string
     * @param string $ruleString
     * @return string
     */
    public function camelize($string)
    {
        $dstr = preg_replace_callback('/([A-Z]+)/',function($matchs)
        {
            return '_'.strtolower($matchs[0]);
        },$string);
        return trim(preg_replace('/_{2,}/','_',$dstr),'_');
    }

    /**
     * @description 大写转换 下划线
     * @author Holyrisk
     * @date 2020/3/14 17:32
     * @param $string
     * @param string $ruleString
     * @return string
     */
    public function camelize2($string,$ruleString='_')
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $ruleString . "$2", $string));
    }

    /**
     * @description 下划线 转换为 驼峰
     * @author Holyrisk
     * @date 2020/3/14 17:39
     * @param $string
     * @return string
     */
    public static function uncamelize($string)
    {
        $array = explode('_', $string);
        $result = $array[0];
        $len=count($array);
        if($len>1)
        {
            for($i=1;$i<$len;$i++)
            {
                $result.= ucfirst($array[$i]);
            }
        }
        return $result;
    }

}