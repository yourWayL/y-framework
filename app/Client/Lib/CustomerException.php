<?php


namespace App\Client\Lib;


use Secxun\Extend\Holyrisk\Config;

class CustomerException extends \Exception
{
    /**
     * @Desc  自定义异常
     * @param $code
     * @param string $message
     * @param bool $is_custom
     * @throws CustomerException
     */
    public static function throwExceptions($code,$message='',$is_custom=false,$modle='')
    {
        if($is_custom){
            throw new self($message,$code);
        }else{
            $app = $modle?$modle:'Client';
            $msg =  Config::get('error_code',$code,'',$app);
            throw new self($msg[$code],$code);
        }
    }
}