<?php
/**
 * @description OpenSSL 加密
 * @author Holyrisk
 * @date 2019/8/1 0:01
 */

namespace Secxun\Extend\Holyrisk;

class OpenSSL
{

    /**
     * @description 默认
     * @author Holyrisk
     * @date 2019/8/1 19:01
     *
     * 1、使用情况 ： 公钥 加密 私钥 解密、或者 私钥 加密 公钥解密
     * 2、公钥 可对外 发送 ，但 私钥  要保留好  不要外泄
     * 3、使用加密解密前  请先生成 密钥 即 生成 公钥 和 私钥
     * 4、如果要生成 证书  -- 请 配置 受保护变量  $config 数组的 config，
     * 这个是生成证书 必须 配置  在PHP 环境 Apache 下
     * 如果 只是 单纯的 加密 解密  那么 不需要 配置 这一项
     * 5、加密 和 解密 ，有 受到 数据长度限制，
     * 如果 确认加密的 明文 数据不长，那么 可以使用默认的 解密
     * 否则 建议 使用 encryptionXxLong decryptXxLong 加密 解密
     * 不然 会 存在 加密数据太长 加密 false，或者 解密 false
     */

    //定义 资源 配置项
    protected static $config = array(
        'private_key_bits' => 1024,                     //可选 字节数    512 1024  2048   4096 等 --- 在本类 建议 不要动 会影响 长明文 数据  加密 解密
        'private_key_type' => OPENSSL_KEYTYPE_RSA,     //加密类型
        //'config' => 'F:\phpStudy\PHPTutorial\Apache\conf/openssl.cnf',//phpstudy 2018 配置路径
        'config' => 'E:\phpstudy\Apache\conf/openssl.cnf',//phpstudy 2016 配置路径
    );
    //证书 存放路径文件夹
    public static $dirPath = '/public/cer';
    //下属的模块 加密 文件夹 用作区分 多个证书
    public $cerDir;
    //私钥 保存文件
    public static $filePrivateName = 'private.cer';
    //公钥 保存文件
    public static $filePublicName = 'public.cer';
    //私钥 内容
    protected $privateKey;
    //公钥内容
    protected $publicKey;

    /**
     * @description 设置 公钥 密钥 默认值
     * @author Holyrisk
     * @date 2019/8/21 16:25
     * OpenSSL constructor.
     * @param string $cerName
     */
    public function __construct($cerName = '')
    {
        //php需要openssl扩展支持
        extension_loaded('openssl') or die('PHP needs OpenSSL extension support');
        if (!empty($cerName))
        {
            $this->cerDir = '/'.trim($cerName);
        }
        $this->setData();
    }

    /**
     * @description 设置 类的 初始化  公钥 密钥 默认值
     * @author Holyrisk
     * @date 2019/8/1 18:49
     */
    public function setData()
    {
        //赋值 公钥 私钥 信息
        if (empty($this->privateKey))
        {
            if (is_file(self::$dirPath.$this->cerDir.'/'.self::$filePrivateName))
            {
                $this->privateKey = file_get_contents(self::$dirPath.$this->cerDir.'/'.self::$filePrivateName);
            }
            else
            {
                //var_dump("私钥 证书 文件 不存在,请查看  ".self::$dirPath.$this->cerDir.'/'.self::$filePrivateName);
            }
        }
        if (empty($this->publicKey))
        {
            if (is_file(self::$dirPath.$this->cerDir.'/'.self::$filePublicName))
            {
                $this->publicKey = file_get_contents(self::$dirPath.$this->cerDir.'/'.self::$filePublicName);
            }
            else
            {
                //var_dump("公钥 证书 文件 不存在,请查看  ".self::$dirPath.$this->cerDir.'/'.self::$filePublicName);
            }
        }
    }

    /**
     * @description 对长明文 进行 加密 【私钥 加密  并且 base64_encode 输出 | 私钥加密  公钥 解密】
     * @author Holyrisk
     * @date 2019/8/1 19:39
     * @param $messages string  要加密的 明文数据
     * @return string 返回 base64_encode 加密 密文
     * @param int $checkSum 密文长度
     * @return string
     */
    public function encryptionPrivateLong($messages,$checkSum = 117)
    {
        $crypto = '';
        foreach (str_split(trim($messages), $checkSum) as $chunk) {
            openssl_private_encrypt($chunk, $encryptData, $this->privateKey);
            $crypto .= $encryptData;
        }
        return base64_encode($crypto);
    }

    /**
     * @description 对长密文 进行 解密 【公钥 解密 并且 先 base64_encode 解码 | 公钥解密 私钥 加密】
     * @author Holyrisk
     * @date 2019/8/1 19:41
     * @param $messages string 要解密的 密文
     * @return string 解密后的 明文
     * @param int $checkSum
     * @return string
     */
    public function decryptPublicLong($messages,$checkSum = 128)
    {
        $crypto = '';
        foreach (str_split(base64_decode(trim($messages)), $checkSum) as $chunk) {
            openssl_public_decrypt($chunk, $decryptData, $this->publicKey);
            $crypto .= $decryptData;
        }
        return $crypto;
    }

    /**
     * @description 对长明文 进行 加密 【公钥 加密  并且 base64_encode 输出 | 公钥加密   私钥解密】
     * @author Holyrisk
     * @date 2019/8/1 19:39
     * @param $messages string 要加密的 明文数据
     * @return string 返回 base64_encode 加密 密文
     * @param int $checkSum
     * @return string
     */
    public function encryptionPublicLong($messages,$checkSum = 117)
    {
        $crypto = '';
        foreach (str_split(trim($messages), $checkSum) as $chunk) {
            openssl_public_encrypt($chunk, $encryptData, $this->publicKey);
            $crypto .= $encryptData;
        }
        return base64_encode($crypto);
    }

    /**
     * @description 对长密文 进行 解密 【私钥 解密 并且 先 base64_encode 解码 | 私钥解密 公钥 加密】
     * @author Holyrisk
     * @date 2019/8/1 19:41
     * @param $messages string 要解密的 密文
     * @return string 解密后的 明文
     * @param int $checkSum 密文 解密 长度
     * @return string
     */
    public function decryptPrivateLong($messages,$checkSum = 128)
    {
        $crypto = '';
        foreach (str_split(base64_decode(trim($messages)), $checkSum) as $chunk) {
            openssl_private_decrypt($chunk, $decryptData, $this->privateKey);
            $crypto .= $decryptData;
        }
        return $crypto;
    }

    /**
     * @description 对长密文 进行 解密 【私钥 解密 并且 先 base64_encode 解码 | 私钥解密 公钥 加密】
     * @author Holyrisk
     * @date 2019/8/1 19:41
     * @param $messages string 要解密的 密文
     * @return string 解密后的 明文
     * @param int $checkSum 密文 解密 长度
     * @return string
     */
    public function decryptPrivateLongJava($messages,$checkSum = 128)
    {
        //$d =  $this->urlsafe_b64decode(I("data"));
        //$pi_key =  openssl_pkey_get_private($this->privateKey);// 可用返回资源id
        $crypto = '';
        foreach (str_split(base64_decode(trim($messages)), $checkSum) as $chunk) {
            openssl_private_decrypt($chunk, $decryptData,$this->privateKey );//私钥解密
            $crypto .= $decryptData;
        }
        return $crypto;
    }


    /**
     * @description 私钥 加密  并且 base64_encode 输出 | 私钥加密  公钥 解密
     * @author Holyrisk
     * @date 2019/8/1 18:58
     * @param $messages string 要加密的 明文数据
     * @return string 返回 base64_encode 加密 密文
     */
    public function encryptionPrivate($messages)
    {
        openssl_private_encrypt(trim($messages), $encrypted, $this->privateKey);
        return base64_encode($encrypted);
    }

    /**
     * @description 公钥 解密 并且 先 base64_encode 解码 | 公钥解密 私钥 加密
     * @author Holyrisk
     * @date 2019/8/1 18:59
     * @param $messages string 要解密的 密文
     * @return mixed 解密后的 明文
     */
    public function decryptPublic($messages)
    {
        openssl_public_decrypt(base64_decode(trim($messages)), $decrypted,$this->publicKey);
        return $decrypted;
    }

    /**
     * @description 公钥 加密  并且 base64_encode 输出 | 公钥加密  私钥 解密
     * @author Holyrisk
     * @date 2019/8/1 17:50
     * @param $messages string 要加密的 明文数据
     * @return string 返回 base64_encode 加密 密文
     */
    public function encryptionPublic($messages)
    {
        openssl_public_encrypt(trim($messages), $encrypted, $this->publicKey);
        return base64_encode($encrypted);
    }

    /**
     * @description 私钥 解密 并且 先 base64_encode 解码 | 私钥解密 公钥 加密
     * @author Holyrisk
     * @date 2019/8/1 17:53
     * @param $messages string 要解密的 密文
     * @return mixed  解密后的 明文
     */
    public function decryptPrivate($messages)
    {
        openssl_private_decrypt(base64_decode(trim($messages)), $decrypted,$this->privateKey);
        return $decrypted;
    }

    /**
     * @description 获取 公钥
     * @author Holyrisk
     * @date 2019/8/1 17:47
     * @return false|string
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * @description 获取私钥
     * @author Holyrisk
     * @date 2019/8/1 17:47
     * @return false|string
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * @description 生成公钥私钥资源 并保存到 相应文件 里面  生成成功 返回文件夹路径
     * @author Holyrisk
     * @date 2019/8/1 16:59
     * @return string
     */
    public function opensslPkeyNew()
    {
        if (extension_loaded('openssl'))
        {
            //生成公钥私钥资源
            $res = openssl_pkey_new(self::$config);
            //导出私钥 $priKey
            openssl_pkey_export($res, $priKey,null,self::$config);
            //导出公钥 $pubKey
            $pubKey = openssl_pkey_get_details($res);
            $pubKeyData = $pubKey['key'];
            //print_r($priKey); 私钥
            //print_r($pubKeyData); 公钥
            //创建密钥 存放文件夹 路径
            $this->createDir(self::$dirPath.$this->cerDir);
            //private
            file_put_contents(self::$dirPath.$this->cerDir.'/'.self::$filePrivateName,$priKey);
            //public
            file_put_contents(self::$dirPath.$this->cerDir.'/'.self::$filePublicName,$pubKeyData);
            $messages = 'cer path : '.self::$dirPath.$this->cerDir;
        }
        else
        {
            //没有加载 openssl 扩展
            $messages = "no loaded openssl";
        }
        return $messages;
    }

    /**
     * @description 创建文件夹
     * @author Holyrisk
     * @date 2019/5/25 14:39
     * @param $dirPath string 文件夹路径
     * @param int $power 文件夹权限 默认 0777
     * @return bool
     */
    public function createDir($dirPath,$power = 0777)
    {
        $result = false;
        if (!file_exists($dirPath)){
            $result = mkdir ($dirPath,$power,true);
        }
        return $result;
    }


    /**
     * @description Java 公私秘钥 转换 成 PHP | 即 java rsa秘钥(pkcs8格式)，PHP不能直接使用 只能将rsa秘钥转成pkcs1格式
     * @author Holyrisk
     * @date 2019/10/16 20:00
     * @param $secret_key
     * @param $type
     * @return string
     */
    public  function formatSecretKeyJavaToPhp($secret_key, $type){
        // 64个英文字符后接换行符"\n",最后再接换行符"\n"
        $key = (wordwrap($secret_key, 64, "\n", true))."\n";
        // 添加pem格式头和尾
        if ($type == 'pub') {
            $pem_key = "-----BEGIN PUBLIC KEY-----\n" . $key . "-----END PUBLIC KEY-----\n";
        }else if ($type == 'pri') {
            $pem_key = "-----BEGIN PRIVATE KEY-----\n" . $key . "-----END PRIVATE KEY-----\n";
        }else{
            echo('公私钥类型非法');
            exit();
        }
        return $pem_key;
    }

}