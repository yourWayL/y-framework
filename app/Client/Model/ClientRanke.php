<?php
/**
 * Note:
 * User: rcj
 * Date: 2020/6/17
 * Time: 16:21
 */

namespace App\Client\Model;
use Secxun\Core\Mysql as OriMysql;

class ClientRanke
{
    //定义表名
    protected $table = 'client_ranke';

    const APP_NAME = 'Client';

    public function __construct()
    {
        $this->mysql = new OriMysql();
    }

    /**
     * @Desc:  获取一条反诈等级设置
     * @param array $searchArray
     * @param string $field
     * @return mixed
     * @throws \Exception
     */
    public function getRanke(array $searchArray,string $field = '*'):array
    {
        $sql = "select $field from ".$this->table." where ";
        foreach ($searchArray as $key => $v) {
            if (is_array($v)) {
                if ($v[0] == 'between') {
                    $sql .= '' . $key . '' . ' ' . $v[0] . ' ? and ?  and ';
                    $executeParam[] = $v[1];
                    $executeParam[] = $v[2];
                } elseif ($v[0] == 'in') {
                    $sql .=  $key . ' in(';
                    foreach ($v[1] as $val) {
                        $sql .= " ?,";
                        $executeParam[] = $val;
                    }
                    $sql = rtrim($sql, ',');
                    $sql .= ') and ';
                } else {
                    $sql .= '`' . $key . '`' . ' ' . $v[0] . ' ? and ';
                    $executeParam[] = $v[1];
                }
            } else {
                $sql .= '`' . $key . '`' . ' = ? and ';
                $executeParam[] = $v;
            }
        }
        $sql = trim($sql, 'and ');
        $sql.=" limit 1";
        $result = $this->mysql->queryExecute($sql,$executeParam,self::APP_NAME);
        return isset($result[0])?$result[0]:[];
    }

    /**
     * @Desc:
     * @param array $searchArray
     * @param string $field
     * @param int $pageSize
     * @param int $limit
     * @return mixed
     * @throws \Exception
     */
    public function getRankes(array $searchArray,string $field = '*',int $pageSize, int $limit):array
    {
        $sql = "select $field from ".$this->table." where ";
        foreach ($searchArray as $key => $v) {
            if (is_array($v)) {
                if ($v[0] == 'between') {
                    $sql .= '' . $key . '' . ' ' . $v[0] . ' ? and ?  and ';
                    $executeParam[] = $v[1];
                    $executeParam[] = $v[2];
                } elseif ($v[0] == 'in') {
                    $sql .=  $key . ' in(';
                    foreach ($v[1] as $val) {
                        $sql .= " ?,";
                        $executeParam[] = $val;
                    }
                    $sql = rtrim($sql, ',');
                    $sql .= ') and ';
                } else {
                    $sql .= '`' . $key . '`' . ' ' . $v[0] . ' ? and ';
                    $executeParam[] = $v[1];
                }
            } else {
                $sql .= '`' . $key . '`' . ' = ? and ';
                $executeParam[] = $v;
            }
        }
        $sql = trim($sql, 'and ');
        $pageF = ($pageSize - 1) * $limit;
        $sql.=" order by sort asc limit {$pageF},$limit";
        return $this->mysql->queryExecute($sql,$executeParam,self::APP_NAME);
    }

    /**
     * @Desc:
     * @param array $searchArray
     * @return int
     * @throws \Exception
     */
    public function countRankes(array $searchArray):int
    {
        $sql = "select count(1) as ct from ".$this->table." where ";
        foreach ($searchArray as $key => $v) {
            if (is_array($v)) {
                if ($v[0] == 'between') {
                    $sql .= '' . $key . '' . ' ' . $v[0] . ' ? and ?  and ';
                    $executeParam[] = $v[1];
                    $executeParam[] = $v[2];
                } elseif ($v[0] == 'in') {
                    $sql .=  $key . ' in(';
                    foreach ($v[1] as $val) {
                        $sql .= " ?,";
                        $executeParam[] = $val;
                    }
                    $sql = rtrim($sql, ',');
                    $sql .= ') and ';
                } else {
                    $sql .= '`' . $key . '`' . ' ' . $v[0] . ' ? and ';
                    $executeParam[] = $v[1];
                }
            } else {
                $sql .= '`' . $key . '`' . ' = ? and ';
                $executeParam[] = $v;
            }
        }
        $sql = trim($sql, 'and ');
        $result = $this->mysql->queryExecute($sql,$executeParam,self::APP_NAME);
        return isset($result[0]['ct'])?$result[0]['ct']:0;
    }

    /**
     * @Desc:新增反诈等级设置
     * @param array $addData
     * @return bool
     */
    public function addRanke(array $addData):bool
    {
        $result = $this->mysql->insert($addData,$this->table,self::APP_NAME);
        if ($result['result']) {
            return true;
        } else {
            return false;
        }
    }

    public function upRanke(array $upWhere,array $upData)
    {
        $sql = "UPDATE " . $this->table . ' SET ';
        foreach ($upData as $k => $v) {
            $sql .= "`$k` = ? , ";
            $execute[] = $v;
        }
        $sql = trim($sql, ', ');
        $sql .= ' WHERE ';
        foreach ($upWhere as $key => $v) {
            if (is_array($v)) {
                if ($v[0] == 'between') {
                    $sql .= '' . $key . '' . ' ' . $v[0] . ' ? and ?  and ';
                    $execute[] = $v[1];
                    $execute[] = $v[2];
                } elseif ($v[0] == 'in') {
                    $sql .=  $key . ' in(';
                    foreach ($v[1] as $val) {
                        $sql .= " ?,";
                        $execute[] = $val;
                    }
                    $sql = rtrim($sql, ',');
                    $sql .= ') and ';
                }
            } else {
                $sql .= '`' . $key . '`' . ' = ? and ';
                $execute[] = $v;
            }
        }
        $sql = trim($sql, 'and ');
        $result = $this->mysql->queryExecute($sql,$execute,self::APP_NAME);
        return $result;
    }

}