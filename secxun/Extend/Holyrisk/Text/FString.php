<?php
/**
 * @description 字符串 查找 替换
 * @author Holyrisk
 * @date 2020/5/6 18:06
 */

namespace Secxun\Extend\Holyrisk\Text;


use Secxun\Extend\Holyrisk\Db;

class FString
{

    protected $dbConfig = [
        // 服务器地址
        'host' => '127.0.0.1',
        //数据库名
        'database' => 'weixin',
        //用户名
        'username' => 'root',
        //密码
        'password' => 'secxun@2019X',
        //端口
        'port' => 3306,
        //字符集
        'charset' => 'utf8mb4'
    ];

    /**
     * @description demo
     * @author Holyrisk
     * @date 2020/5/8 18:10
     * @throws \Exception
     */
    public function run()
    {
        $model = new Db($this->dbConfig);
        $sql = 'SELECT * FROM xc_city WHERE delete_time = 0 and  NAME LIKE "北京市%" ';
        $list = $model->query($sql);
        $res = $this->findArray($list, 'path');
    }

    /**
     * @description 将被处理数组
     * @author Holyrisk
     * @date 2020/5/7 16:13
     * @var array
     */
    protected $takeArr = [];

    /**
     * @description 结果数组
     * @author Holyrisk
     * @date 2020/5/7 16:13
     * @var array
     */
    protected $resArr = [];

    /**
     * @description 查找
     * @param $haystackArray
     * @param $field
     * @return mixed
     * @author Holyrisk
     * @date 2020/5/7 14:57
     */
    public function findRemoveArray($haystackArray, $field)
    {
        if (empty($haystackArray)) {
            return $haystackArray;
        } else {
            $this->takeArr = $haystackArray;
            //拉取到 要处理的 数组
            //判断 path 截取之后 数组 是否 大于 1
            foreach ($haystackArray as $key => $value) {
                if (isset($value[$field]) == true) {
                    $fieldCheck = $this->handle($value[$field], $field);
                    if ($fieldCheck == true)
                    {
                        unset($haystackArray[$key]);
                    }
                }
            }
            return $haystackArray;
        }
    }


    /**
     * @description 遍历
     * @author Holyrisk
     * @date 2020/5/8 16:08
     * @param $string
     * @param $field
     * @return bool
     */
    public function handle($string, $field)
    {
        $result = false;
        foreach ($this->takeArr as $key => $value) {
            $isFinf = $this->findStart($string, $value[$field]);
            if ($isFinf == true)
            {
                $result = true;
                break;
            }
        }
        return $result;
    }

    /**
     * @description 判断 字符串 是否 在字符串的里面  | 如果 在里面 、 后面 还有字符串  则 返回 true | 否则 返回 false
     * @author Holyrisk
     * @date 2020/5/8 16:05
     * @param $handleString
     * @param $findString
     * @param int $start
     * @return bool|int
     */
    public function findStart($handleString, $findString, $start = 0)
    {
        //返回字符串在另一字符串中第一次出现的位置，如果没有找到字符串则返回 FALSE
        $result = stripos($handleString, $findString, $start);
        if ($result !== false) {
            //返回开始位置
            //截取 从 开始 到 字符串 长度之后  的  字符串
            $len = substr($handleString, strlen($findString));
            //判断 字符串是否 为空 为空 则 表示相等  不为空  则 表示 后面 还有字符
            if ($len == "") {
                //$result = "两个字符串 相等";
                $result = false;
            } else {
                //$result = "不相等";
                $result = true;
            }
            //$result = stristr($handleString,$findString,true);
            //$result = $len;
        } else {
            $result = false;
        }
        return $result;
    }

}