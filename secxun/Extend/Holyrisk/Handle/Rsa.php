<?php
/**
 * @description Rsa 加密 业务处理 | 公钥加密 私钥解密 | 服务端 使用私钥 | 客户端 使用的是 公钥 | 当然这里 服务端 公钥私钥都有
 * @author Holyrisk
 * @date 2020/4/16 18:24
 */

namespace Secxun\Extend\Holyrisk\Handle;

use Secxun\Extend\Holyrisk\OpenSSL;

class Rsa
{

    /**
     * @description  公钥 加密
     * @author Holyrisk
     * @date 2020/3/12 12:05
     * @param $encryption
     * @return mixed
     */
    public function encryptionPublic($encryption)
    {
        $rsaObj = new OpenSSL('wd');
        $result = $rsaObj->encryptionPublicLong($encryption);
        return $result;
    }

    /**
     * @description 公钥解密
     * @author Holyrisk
     * @date 2020/4/16 19:14
     * @param $decrypt
     * @return string
     */
    public function decryptPublic($decrypt)
    {
        $rsaObj = new OpenSSL('wd');
        $result = $rsaObj->decryptPublicLong($decrypt);
        return $result;
    }

    /**
     * @description 私钥 加密
     * @author Holyrisk
     * @date 2020/4/16 19:15
     * @param $encryption
     * @return string
     */
    public function encryptionPrivate($encryption)
    {
        $rsaObj = new OpenSSL('wd');
        //密文
        $result = $rsaObj->encryptionPrivateLong($encryption);
        return $result;
    }

    /**
     * @description 私钥解密 | 给前端 解密| 前端 使用的 是 公钥 | 这里 使用 私钥
     * @author Holyrisk
     * @date 2020/4/16 18:35
     * @param $decrypt
     * @return mixed
     */
    public function decryptPrivate($decrypt)
    {
        $rsaObj = new OpenSSL('wd');
        //密文
        $result = $rsaObj->decryptPrivateLong($decrypt);
        return $result;
    }


}