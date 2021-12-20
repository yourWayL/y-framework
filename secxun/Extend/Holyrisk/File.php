<?php
/**
 * @description 文件操作
 * @author Holyrisk
 * @date 2019/12/18 15:50
 */

namespace Secxun\Extend\Holyrisk;


class File
{
    //文件名称
    protected $_fileName = null;
    //文件路径
    protected $_dirPath = '/';

    /**
     * @description 设置 文件夹
     * @author Holyrisk
     * @date 2019/12/18 15:53
     * @param $name 文件夹名称
     * @return $this
     */
    public function folder($name)
    {
        $this->_dirPath = $name;
        return $this;
    }

    /**
     * @description 设置 文件名称
     * @author Holyrisk
     * @date 2019/12/18 15:54
     * @param $name
     * @return $this
     */
    public function fileName($name)
    {
        $this->_fileName = $name;
        return $this;
    }

    /**
     * @description 获取设置的文件路径
     * @author Holyrisk
     * @date 2019/12/18 15:59
     */
    public function getPath()
    {
        //文件夹路径
        $dirList = $this->_dirPath;
        //保存的文件
        $file = $this->_fileName;
        if (!empty($dirList)){
            if (!file_exists($dirList)){
                @mkdir ($dirList,0777,true);
            }
            $path = $dirList.'/'.$file;
        }else{
            $path = $file;
        }
        return $path;
    }

    /**
     * @description 写入
     * @author Holyrisk
     * @date 2019/12/18 16:10
     * @param $txt 写入的内容
     * @param string $model 方式  默认 a 追加 w 覆盖替换
     * @param bool $isJson 是否 需要 转换成 json 写入文件
     */
    public function write($txt,$model = 'w',$isJson = true)
    {
        if ($this->_fileName != null)
        {
            //获取路径
            $path = $this->getPath();
            $handle = fopen($path, $model) or die("Unable to open file!");
            if ($isJson == true)
            {
                $data = json_encode($txt,JSON_UNESCAPED_UNICODE);
            }
            else
            {
                $data = $txt;
            }
            $str = trim($data);
            fwrite($handle, $str);
            fclose($handle);
        }
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

}