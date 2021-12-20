<?php
/**
 * @description 请求处理
 * @author Holyrisk
 * @date 2019/12/17 19:47
 */

namespace Secxun\Extend\Holyrisk;


class Request
{

    /**
     * @description GET 的 请求处理
     * @author Holyrisk
     * @date 2019/12/17 19:50
     * @param $request
     * @return mixed
     */
    public static function get($request)
    {
        $getArr = self::returnResult($request,'get',[]);
        return $getArr;
    }

    /**
     * @description POST 的 请求处理
     * @author Holyrisk
     * @date 2019/12/17 19:53
     * @param $request
     * @return array|mixed
     */
    public static function post($request)
    {
        $postArr = self::returnResult($request,'post',[]);
        return $postArr;
    }

    /**
     * @description streamId 的 请求处理
     * @author Holyrisk
     * @date 2019/12/17 19:53
     * @param $request
     * @return array|mixed
     */
    public static function streamId($request)
    {
        $streamId = self::returnResult($request,'streamId',0);
        return $streamId;
    }

    /**
     * @description header 的 请求处理
     * @author Holyrisk
     * @date 2019/12/17 19:53
     * @param $request
     * @return array|mixed
     */
    public static function header($request)
    {
        $headerArr = self::returnResult($request,'header',[]);
        return $headerArr;
    }

    /**
     * @description server 的 请求处理
     * @author Holyrisk
     * @date 2019/12/17 19:53
     * @param $request
     * @return array|mixed
     */
    public static function server($request)
    {
        $serverArr = self::returnResult($request,'server',[]);
        return $serverArr;
    }

    /**
     * @description cookie 的 请求处理
     * @author Holyrisk
     * @date 2019/12/17 19:53
     * @param $request
     * @return array|mixed
     */
    public static function cookie($request)
    {
        $cookieArr = self::returnResult($request,'cookie',[]);
        return $cookieArr;
    }

    /**
     * @description files 的 请求处理
     * @author Holyrisk
     * @date 2019/12/17 19:53
     * @param $request
     * @return array|mixed
     */
    public static function files($request)
    {
        $filesArr = self::returnResult($request,'files',[]);
        return $filesArr;
    }

    /**
     * @description tmpfiles 的 请求处理
     * @author Holyrisk
     * @date 2019/12/17 19:53
     * @param $request
     * @return array|mixed
     */
    public static function tmpfiles($request)
    {
        $tmpfilesArr = self::returnResult($request,'tmpfiles',[]);
        return $tmpfilesArr;
    }

    /**
     * @description 获取用户请求 id
     * @author Holyrisk
     * @date 2019/12/17 20:06
     * @param $request
     * @return null
     */
    public static function fd($request)
    {
        $fd = self::returnResult($request,'fd',null);
        return $fd;
    }

    /**
     * @description 输出结果
     * @author Holyrisk
     * @date 2019/12/17 20:10
     * @param $request 请求对象
     * @param string $query 请求方式
     * @param null $data 默认返回数据格式
     * @return null
     */
    protected static function returnResult($request,$query = 'fd',$data = null)
    {
        $result = $data;
        if (property_exists($request,$query) == true)
        {
            $result = $request->$query;
        }
        return $result;
    }

    /**
     * @description 获取请求 IP
     * @author Holyrisk
     * @date 2019/12/17 20:23
     * @param $request
     * @return mixed|string
     */
    public static function ip($request)
    {
        $ip = '';
        $headerArr = self::header($request);
        if (!empty($headerArr) and  isset($headerArr['x-real-ip']))
        {
            $ip = $headerArr['x-real-ip'];
        }
        return $ip;
    }


    /**
     * @description 移除数组的 key-value 的左右空格
     * @author Holyrisk
     * @date 2020/4/16 11:56
     * @param array $arr
     * @return array
     */
    public static function removeArray($arr = [])
    {
        if (empty($arr))
        {
            return $arr;
        }
        else
        {
            $newArr = [];
            foreach ($arr as $key => $value)
            {
                $newArr[$key] = trim($value);
            }
            return $newArr;
        }
    }

    /**
     * @description 移除 空数组
     * @author Holyrisk
     * @date 2019/12/19 11:43
     * @param array $arr
     * @return array
     */
    public static function unsetArray($arr = [])
    {
        if (empty($arr))
        {
            return $arr;
        }
        else
        {
            $newArr = [];
            foreach ($arr as $key => $value)
            {
                $value = trim($value);

                if ($value === 0 or $value === '0' or $value === false)
                {
                    $newArr[$key] = $value;
                }
                else
                {
                    if (empty($value))
                    {
                        continue;
                    }
                    else
                    {

                        $newArr[$key] = $value;
                    }
                }
            }
            unset($arr);
            return $newArr;
        }
    }

}