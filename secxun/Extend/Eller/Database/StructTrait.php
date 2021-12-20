<?php


namespace Secxun\Extend\Eller\Database;


trait StructTrait
{
    private static $struct = [];

    /**
     * 获取表结构
     * @return mixed
     * @author ELLER
     */
    protected function getStruct()
    {
        $dns = $this->connect->getKey();
        if (!isset(self::$struct[$dns][$this->table])) {
            $connect    = $this->getConnect();
            $arr    = $connect->findAll('desc `' . $this->table . '`');
            $fields = [];
            $pri    = '';
            foreach ($arr as $v) {
                if ($v['Key'] == 'PRI') {
                    $pri = $v['Field'];
                } else if ($v['Null'] == 'YES') {
                    $fields[$v['Field']] = 0;
                } else {
                    $fields[$v['Field']] = 1;
                }
            }
            self::$struct[$dns][$this->table] = ['field' => $fields, 'pri' => $pri];
        }
        return self::$struct[$dns][$this->table];
    }

    /**
     * 获取主键
     * @return mixed
     * @author ELLER
     */
    protected function getPriKey()
    {
        if ($this->primaryKey !== '') {
            return $this->primaryKey;
        }
        return $this->getStruct()['pri'];
    }

    /**
     * 过滤查询条件
     * @param $data
     * @param bool $include_pri 是否过滤主键
     * @return mixed
     * @author ELLER
     */
    public function filter($data, $include_pri = false)
    {
        $field = $this->getStruct()['field'];
        if ($include_pri) {
            $p = $this->getPriKey();
            if ($p) {
                $field[$p] = 1;
            }
        }
        foreach ($data as $k => $v) {
            if (!isset($field[$k])) {
                unset($data[$k]);
            }
        }
        return $data;
    }

    /**
     * 获取所有字段
     * @return array
     * @author ELLER
     */
    public function getFields()
    {
        return array_merge([$this->getPriKey()], $this->getStruct()['field']);
    }
}