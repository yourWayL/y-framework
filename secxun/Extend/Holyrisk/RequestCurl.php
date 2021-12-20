<?php
/**
 * @description CURL 请求类
 * @author Holyrisk
 * @date 2019/6/15 16:19
 */

namespace Secxun\Extend\Holyrisk;


class RequestCurl
{

    /**
     * @description 转发 微信公众号 信息 服务
     * @author Holyrisk
     * @date 2020/5/15 15:39
     * @param $url
     * @param $data
     * @return bool|string
     */
    public function requestCurlGetWeixin($url, $data)
    {
        $headers = [
            'User-Agent: Mozilla/5.0 (Windows NT 5.1; rv:14.0) Gecko/20100101 Firefox/14.0.1',
            'Accept-Language: en-us,en;q=0.5',
            'Referer:http://mp.weixin.qq.com/',
            'Content-type: text/xml'
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,1);
        curl_setopt($ch, CURLOPT_TIMEOUT,3);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    /**
     * @description  curl请求
     * @author Holyrisk
     * @date 2019/4/5 2:32
     * @param $url 要请求的 URL
     * @return bool|string
     */
    public function requestCurlGet($url)
    {
        if (empty($url)) return false;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_TIMEOUT,10);   //只需要设置一个秒的数量就可以  超时设置
        curl_setopt($ch, CURLOPT_URL, $url);
        $response =  curl_exec($ch);
        curl_close($ch);
        //-------请求为空
        //处理
        return $response;
    }

    /**
     * @description curl put 请求
     * @author Holyrisk
     * @date 2019/5/21 15:30
     * @param string $url 请求的 URL
     * @param bool $put_data 请求的参数
     * @param array $header 请求的 header 头
     * @return bool|string
     */
    public function requestCurlPut($url = '', $put_data = false,$header=[])
    {
        //初始化CURL句柄
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url); //定义请求地址
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);//定义是否直接输出返回流
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT'); //定义请求类型，必须为大写
        curl_setopt($ch, CURLOPT_HEADER,0); //定义是否显示状态头 1：显示 ； 0：不显示
        //定义header 设置HTTP头信息 这里接收的的是 数组 一维数组
        /**
        $header = array(
        'Content-Type: application/json',
        );
         */
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        //定义提交的数据 提交的字符串
        curl_setopt($ch, CURLOPT_POSTFIELDS, $put_data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。
        $res = curl_exec($ch);
        curl_close($ch);//关闭
        return $res;
    }

    /**
     * @description curl post
     * @author Holyrisk
     * @date 2019/4/16 15:25
     * @param $url 请求URL
     * @param $postData 请求post数据
     * @param array $header
     * @return bool|string
     */
    public function requestCurlPost($url,$postData,$header = []){
        if (empty($url)) return false;
        //初始化
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
        //curl_setopt($curl, CURLOPT_HEADER, 1);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //设置post方式提交
        curl_setopt($curl, CURLOPT_POST, 1);
        //设置post数据
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//绕过ssl验证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//CURLOPT_RETURNTRANSFER - 不以文件流返回，带1
        curl_setopt($curl,CURLOPT_HTTPHEADER,$header);
        //执行命令
        $response = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        //显示获得的数据
        return $response;
    }

    /**
     * @description post 方式请求
     * @author Holyrisk
     * @date 2019/7/31 12:06
     * @param $url string 要请求的 URL
     * @param  bool $postData 数组 或者
     * @param array $header 一位数组  设置 header 头
     * @return bool|string
     */
    public function postHeader($url,$postData = false,$header = [])
    {
        if (empty($url)) return false;
        //初使化init方法
        $ch = curl_init();
        //指定URL
        curl_setopt($ch, CURLOPT_URL, $url);
        //忽略证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
        //执行结果是否被返回，0是返回，1是不返回
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //设置超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        //忽略header头信息 参数设置，是否显示头部信息，1为显示，0为不
        curl_setopt($ch, CURLOPT_HEADER, false);
        //设定请求后返回结果
        curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"POST");
        curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
        //发送的数据
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        //上传后的任何重定向
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        //执行发送请求
        $output = curl_exec($ch);
        //关闭curl
        curl_close($ch);
        //返回数据
        return $output;
    }

}