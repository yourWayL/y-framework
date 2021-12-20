<?php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

if (!function_exists('convert_term_search')) {
    /**
     * 转换ES精确查询数组
     * @param array $search_data
     * @return array
     */
    function convert_term_search(array $search_data)
    {
        $return_array = [];
        $i = 0;
        foreach ($search_data as $key => $value) {
            if (empty($value)) continue;
            $return_array[$i]['term'] = [$key => $value];
            $i++;
        }
        return $return_array;
    }

}


if (!function_exists('convert_post_condition')) {

    /**
     * 转换提交参数
     * @param $post_data
     * @return string
     */
    function convert_post_condition($post_data)
    {
        if (is_array($post_data) && FALSE !== $post_data) {
            foreach ($post_data as $key => $value) {
                $convert_data[$key] = ($value !== FALSE) ? trim($value) : $value;
            }
            return $convert_data;
        } else {
            return trim($post_data);
        }
    }
}


if (!function_exists('convert_string_to_datetime')) {

    /**
     *
     * 将字符串转换成datetime [ '20150520122200' => '2015-05-20 12:22:00']
     * @param $string
     */
    function convert_string_to_datetime($string)
    {

        return date("Y-m-d H:i:s", strtotime($string));

    }


}


if (!function_exists('format_number')) {


    function format_number($number)
    {

        return ($number == false) ? 0 : $number;

    }

}


if (!function_exists('return_array')) {

    /**
     * 返回
     * @param array $data 内容
     * @param string $code 代码
     * @return array
     */
    function return_array(array $data = [], $msg = '操作成功', $code = '200')
    {

        return !empty($data) ? array_merge($data, ['code' => $code, 'msg' => $msg]) : ['code' => $code, 'msg' => $msg];

    }

}
if (!function_exists('log_message')) {
    /**
     * 写入日志
      @param $app
     * @param $level
     * @param $message
     * @param $logFile
     * @throws Exception
     */
    function log_message($app,$level, $message,$logFile = ROOT_PATH.'/runtime/log/process/app.log')
    {
        //(new \Sec\Library\Log())->writeLog($level, $message,$prefix);
        $leves = ['debug','info','notice','warning','error'];
        if (!in_array($level,$leves)){
            return false;
        }
        $dateFormat = "Y-m-d H:i s";
        $output = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";
        $formatter = new LineFormatter($output, $dateFormat);
        $log = new Logger($app);
        $stream = new StreamHandler($logFile, Logger::DEBUG);
        $stream->setFormatter($formatter);
        $log->pushHandler($stream);
        $func = strtolower($level);
        $log->$func($message);
    }

}




if (!function_exists('is_php')) {
    function is_php($version)
    {
        static $_is_php;
        $version = (string)$version;

        if (!isset($_is_php[$version])) {
            $_is_php[$version] = version_compare(PHP_VERSION, $version, '>=');
        }
        return $_is_php[$version];
    }
}


if (!function_exists('is_really_writable')) {
    function is_really_writable($file)
    {
        if (DIRECTORY_SEPARATOR === '/' && (is_php('5.4') OR !ini_get('safe_mode'))) {
            return is_writable($file);
        }
        if (is_dir($file)) {
            $file = rtrim($file, '/') . '/' . md5(mt_rand());
            if (($fp = @fopen($file, 'ab')) === FALSE) {
                return FALSE;
            }
            fclose($fp);
            @chmod($file, 0777);
            @unlink($file);
            return TRUE;

        } elseif (!is_file($file) OR ($fp = @fopen($file, 'ab')) === FALSE) {
            return FALSE;
        }
        fclose($fp);
        return TRUE;
    }
}

/**
 * @description 标准输出JSON
 * @author Holyrisk
 * @date 2020/4/16 14:49
 * @param string $meessage 提示信息
 * @param int $code 状态码 200 成功 400 失败 500 系统错误 | 非 200 为失败 - 特殊声明例外
 * @param string $data 结果集 | 附加参数
 * @return false|string
 */
function anJson($meessage = 'success', $code = 200, $data = '')
{
    $format = [
        'code' => $code,
        'msg'  => $meessage,
        'data' => $data
    ];
    return json_encode($format, JSON_UNESCAPED_UNICODE);
}

/**
 * @description 标准输出 数组
 * @author Holyrisk
 * @date 2020/4/30 17:04
 * @param string $meessage 提示信息
 * @param int $code 状态码 200 成功 400 失败 500 系统错误 | 非 200 为失败 - 特殊声明例外
 * @param string $data 结果集 | 附加参数
 * @return array
 */
function returnArray($meessage = 'success', $code = 200, $data = '')
{
    $format = [
        'code' => $code,
        'msg'  => $meessage,
        'data' => $data
    ];
    return $format;
}

/**
 * curlPost
 * @param string $url
 * @param array $postData
 * @return mixed
 */
function requestPost($url = "", $postData = []){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);//设置为POST方式
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));//POST数据
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    $result = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($result, true);
    return $result;
}

/**
 * 获取图片的Base64编码(不支持url)
 * @date 2017-02-20 19:41:22
 * @param $img_file 传入本地图片地址
 * @return string
 */

function imgToBase64($img_file)
{
    $img_base64 = '';
    if (file_exists($img_file)) {
        $app_img_file = $img_file; // 图片路径
        $img_info = getimagesize($app_img_file); // 取得图片的大小，类型等
        ////echo '<pre>' . print_r($img_info, true) . '</pre><br>';
        $fp = fopen($app_img_file, "r"); // 图片是否可读权限
        ///
        if ($fp) {
            $filesize = filesize($app_img_file);
            $content = fread($fp, $filesize);
            $file_content = chunk_split(base64_encode($content)); // base64编码
            switch ($img_info[2]) {
                case 1:
                    $img_type = "gif";
                    break;
                case 2:
                    $img_type = "jpg";
                    break;
                case 3:
                    $img_type = "png";
                    break;
            }
            $img_base64 = 'data:image/' . $img_type . ';base64,' . $file_content;//合成图片的base64编码
        }
        fclose($fp);
    }
    return $img_base64; //返回图片的base64编码
}

if (!function_exists('env')){
    function env($key = '', $default = '')
    {
        static $configs;
        if (empty($configs)){
            $path = rtrim(ROOT_PATH) . DIRECTORY_SEPARATOR;
            $envPath = $path.'.env';
            if (!file_exists($envPath)){
                die('缺少.env环境配置文件');
            }
            $appEnv = parse_ini_file($envPath, true);
            $appEnv = !empty($appEnv['APP_ENV']) ? $appEnv['APP_ENV'] : '';
            if (!$appEnv){
                die('.env配置错误');
            }

            $filename = '.env_' . $appEnv;
            $filePath = $path . $filename;
            if (!file_exists($filePath)){
                die('缺少对应环境的.env配置文件');
            }

            $configs  = parse_ini_file($filePath, true);
        }

        $res = $configs;
        if (!empty($key)){
            $keyArr = explode('.', $key);
            $flag = false;
            $tempConf = $configs;
            foreach ($keyArr as $keyV){
                foreach ($tempConf as $vKey => $vVal){
                    if (strtolower($keyV) == strtolower($vKey)){
                        $tempConf = $vVal;
                        $flag = true;
                    }
                }
            }
            $res = !$flag ? $default : $tempConf;
        }
        return $res;
    }
}








