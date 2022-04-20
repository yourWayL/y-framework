<?php
declare(strict_types=1);
/**
 * @category: Secxun\Core
 * @description: 框架提供的基础操作支撑
 * @author yourway <lyw@secxiun.com>
 * @copyright 深圳安巽科技有限公司 <https://www.secxun.com>
 * @create: 2020 - 03 - 16
 */


namespace Secxun\Core;


class Action
{
    /**
     * 取出项目中的全局配置项
     * @param string $type
     * @return false|string|array
     */
    public static function getConfig(string $type)
    {
        $path = ROOT_PATH . DS . 'config' . DS . $type . '.php';
        return include_once($path);
    }

    /**
     * 取出项目中的指定配置项
     * @param string $app
     * @param string $type
     * @return false|string|array
     */
    public static function getAppConfig(string $app, string $type)
    {
        $path = ROOT_PATH . DS . 'app' . DS . ucfirst($app) . DS . 'Config' . $type . 'php';
        return file_get_contents($path);
    }

    /**
     * curl请求方法 第二个参数为空则get方式请求
     * @param string $url
     * @param array $postData
     * @param bool $header
     * @return bool|string
     */
    public static function curlUrl(string $url,  $postData = array(), bool $header = false)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //返回数据不直接输出
        curl_setopt($ch, CURLOPT_ENCODING, "gzip"); //指定gzip压缩
        //add header
        if (!empty($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        //add ssl support
        if (substr($url, 0, 5) == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    //SSL 报错时使用
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);    //SSL 报错时使用
        }
        //add 302 support
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        //add post data support
        if (!empty($postData)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }
        try {
            $result = curl_exec($ch); //执行并存储结果
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        $curlError = curl_error($ch);
        if (!empty($curlError)) {
            echo $curlError;
        }
        curl_close($ch);

        return $result;
    }

    /**
     * curl_multi curl批量请求方法
     * @param array $data
     * @return array
     */
    public static function  multiCurlUrl(array $data): array
    {
        //先拼装请求
        // 创建批处理cURL句柄
        $chArr=[];
        $result = [];
        //创建多个cURL资源
        foreach($data as $key =>$val){
            $chArr[$key]=curl_init();
            curl_setopt($chArr[$key], CURLOPT_URL, $val['url']);
            curl_setopt($chArr[$key], CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($chArr[$key], CURLOPT_TIMEOUT, 100);
            if (!empty($val['data'])) {
                curl_setopt($chArr[$key], CURLOPT_POST, 1);
                curl_setopt($chArr[$key], CURLOPT_POSTFIELDS, json_encode($val['data']));
            }
        }

        $mh = curl_multi_init(); //1 创建批处理cURL句柄

        foreach($chArr as $k => $ch){
            curl_multi_add_handle($mh, $ch); //2 增加句柄
        }
        $active = null;
        //在$active > 0,执行curl_multi_exec($mh,$active)而整个批处理句柄没有全部执行完毕时，系统会不停地执行curl_multi_exec()函数。
        do{
            $mrc = curl_multi_exec($mh, $active); //3 执行批处理句柄
        }while($mrc == CURLM_CALL_MULTI_PERFORM);

        //$active 为true，即$mh批处理之中还有$ch句柄正待处理，$mrc==CURLM_OK,即上一次$ch句柄的读取或写入已经执行完毕。
        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($mh) != -1) {//$mh批处理中还有可执行的$ch句柄，curl_multi_select($mh) != -1程序退出阻塞状态。
                do {
                    $mrc = curl_multi_exec($mh, $active);//继续执行需要处理的$ch句柄。
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }

        foreach($chArr as $k => $ch){
            $result[$k]= curl_multi_getcontent($ch); //5 获取句柄的返回值
            curl_multi_remove_handle($mh, $ch);//6 将$mh中的句柄移除
        }

        curl_multi_close($mh); //7 关闭全部句柄

        return $result;
    }

}