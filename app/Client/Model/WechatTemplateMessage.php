<?php
/**
 * Created by PhpStorm.
 * User: ellermister
 * Date: 2020/5/27
 * Time: 21:35
 */

namespace App\Client\Model;


use Secxun\Extend\Eller\Database\Model;

class WechatTemplateMessage extends Model
{
    protected $table = 'wechat_template_message';

    /**
     * 写入错误到数据库
     *
     * @param $message
     * @param $error
     * @return bool|int
     */
    public static function writeErrorLog($message, $error)
    {
        $data = [

            'message'      => $message,
            'error'        => $error,
            'created_time' => time(),
            'updated_time' => time(),
        ];
        return self::create($data);
    }
}