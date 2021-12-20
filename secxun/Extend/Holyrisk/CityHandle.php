<?php
/**
 * @description 城市处理表
 * @author Holyrisk
 * @date 2020/4/28 20:22
 */

namespace Secxun\Extend\Holyrisk;

use Secxun\Extend\Holyrisk\Db;

class CityHandle
{

    //设置 操作库
    protected $dbObj;
    //设置 读取 表
    protected $table = 'xc_city';

    public function run()
    {
      $this->modifyPath(0);
    }

    protected $dbConfig =  [
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
     * @description 获取列表
     * @author Holyrisk
     * @date 2020/4/28 21:19
     * @param $parent_id
     * @return array
     * @throws \Exception
     */
    protected function getParentIdList($parent_id)
    {
        $mdoel = new Db($this->dbConfig);
        $sql = 'select * from `'.$this->table.'` where `parent_id` = '."'".$parent_id."'";
        $data = $mdoel->query($sql,[],'select');
        $mdoel->close();
        return $data;
    }

    /**
     * @description 获取详情
     * @author Holyrisk
     * @date 2020/4/28 21:21
     * @param $pid
     * @return array
     * @throws \Exception
     */
    protected function getIdData($pid)
    {
        $mdoel = new Db($this->dbConfig);
        $sql = 'select * from `'.$this->table.'` where `id` = '."'".$pid."'";
        $data = $mdoel->query($sql,[],'find');
        $mdoel->close();
        return $data;
    }

    /**
     * @description 修改
     * @author Holyrisk
     * @date 2020/4/28 21:21
     * @param $pid
     * @param $path
     * @return array|bool
     * @throws \Exception
     */
    protected function modifyPathPid($pid,$path)
    {
        $sql = "UPDATE `".$this->table."` SET `path` = '".$path."' WHERE `parent_id` = ".$pid;
        $mdoel = new Db($this->dbConfig);
        $data = $mdoel->query($sql,[],'update');
        $mdoel->close();
        return $data;
    }

    /**
     * @description 修改操作
     * @author Holyrisk
     * @date 2020/4/28 21:22
     * @param $pid
     * @param string $rule
     * @throws \Exception
     */
    protected function modifyPath($pid,$rule = '-')
    {
        //获取 是否 有 要修改的列表
        $list = $this->getParentIdList($pid);
        if (!empty($list))
        {
            $path = $pid;
            if ($pid != 0)
            {
                $pidData = $this->getIdData($pid);
                if (!empty($pidData))
                {
                    $path = $pidData['path'].$rule.$pid;
                }
            }
            $isUpdate = $this->modifyPathPid($pid,$path);
            echo $pid." 修改 ".$isUpdate.PHP_EOL;
            //循环
            foreach ($list as $key => $value)
            {
                echo $value['level']."下级继续".date("Y-m-d H:i:s",time());
                echo PHP_EOL;
                //下级继续
                $this->modifyPath($value['id']);
            }
        }
        else
        {
            echo "没有下级".PHP_EOL;
        }
    }

}