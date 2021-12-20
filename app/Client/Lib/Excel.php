<?php
/**
 * Note:
 * User: rcj
 * Date: 2020/6/2
 * Time: 19:24
 */

namespace App\Client\Lib;


class Excel
{
    protected static $response;

    protected static $content;

    public static function init($response)
    {
        self::$response =  $response;
        self::$content = chr(0xEF).chr(0xBB).chr(0xBF);
    }

    public static function setCvsHeader($headerData=[])
    {
         self::$content .= join(',',$headerData) ."\n";
    }

    public static function setData($data=[])
    {
         foreach ($data as $val){
             $str='';
             foreach ($val as $val1){
                 $str.= "$val1,";
             }
             self::$content .=  rtrim($str,',')."\n";
         }
    }

    public static function setHeader($header = [])
    {
        foreach ($header as $key=>$val){
            self::$response->header($key,$val,true);
        }
    }

    public static function getContent()
    {
        return self::$content;
    }

    public static function create()
    {
        self::$response->end(self::$content);
    }

}