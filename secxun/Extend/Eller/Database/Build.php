<?php


namespace Secxun\Extend\Eller\Database;


class Build
{
    use WhereTrait;
    use StructTrait;

    protected $table = '';
    protected $model_name = '';
    private $column = "*";
    private $columns = [];
    private $joins = [];
    private $group_by = [];
    private $order_by = [];
    private $limit = 10;

    protected $build = [];


    protected $primaryKey = 'id';



    /**
     * @var Connect
     */
    private $connect;

    public $model;

    public function __construct($model, $model_name, $table)
    {
        //$this, get_called_class(), $this->table
        $this->table = $table;
        $this->model = $model;
        $this->model_name = $model_name;
        $this->connect = new Connect();
    }

    /**
     * 获取数据列表
     * @throws DbException
     * @author ELLER
     */
    public function get()
    {
        $sql = $this->getSelectSql();
        return $this->connect->findAll($sql, $this->build);
    }

    /**
     * 获取一条数据
     * @param $id 主键ID
     * @return mixed
     * @throws DbException
     * @author ELLER
     */
    public function find($id = null)
    {
        if ($id) {
            $this->where($this->getPriKey(), $id);
        }
        $sql = $this->getSelectSql();
        $info = $this->connect->find($sql,$this->build);
        return $info;
    }

    /**
     * 自动分页功能
     * @param int $pageSize
     * @return array|bool
     * @throws DbException
     * @author ELLER
     */
    public function paginate($pageSize = 10)
    {
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $beginRow = $page * $pageSize - $pageSize;
        $this->limit = $beginRow . "," . $pageSize;
        $sql = $this->getSelectSql();
        $list = $this->connect->findAll($sql, $this->build);
        $data['list'] = $list;
        $data['count'] = $this->count();
        $data['page'] = intval($page);
        $data['page_size'] = intval($pageSize);
        return $data;
    }

    /**
     * 获取总记录条数
     * @return int
     * @throws DbException
     * @author ELLER
     */
    public function count()
    {
        $temp = ['columns' => $this->columns, 'column' => $this->column, 'group_by' => $this->group_by, 'limit' => $this->limit,'order' => $this->order_by,'joins'=>$this->joins];//暂存
        $this->column = "count(*) as count";
        $this->columns = [];
        $this->group_by = [];
        $this->order_by = [];
        $this->joins = [];
        $this->limit = 1;

        $sql = $this->getSelectSql();
        
        $this->columns = $temp['columns']; //恢复
        $this->column = $temp['column']; //恢复
        $this->group_by = $temp['group_by']; //恢复
        $this->order_by = $temp['order']; //恢复
        $this->joins = $temp['joins']; //恢复
        $this->limit = $temp['limit']; //恢复

        $row = $this->connect->find($sql, $this->build);
        if($row){
            return intval($row['count']);
        }
        return  0;
    }

    /**
     * 排序
     * @param $order
     * @return $this
     * @author ELLER
     */
    public function orderBy($order)
    {
        $this->order_by[] = $order;
        return $this;
    }

    /**
     * 分组
     * @param $order
     * @return $this
     * @author ELLER
     */
    public function groupBy($order)
    {
        $this->group_by[] = $order;
        return $this;
    }

    /**
     * 查询条数
     * @param $limit
     * @param int $skip
     * @return $this
     * @author ELLER
     */
    public function limit($limit, $skip = 0)
    {
        $this->limit = $skip . ',' . $limit;
        return $this;
    }


    /**
     * 获取查询语句
     * @return string
     * @author ELLER
     */
    protected function getSelectSql()
    {
        $sql = 'select';
        if(count($this->columns)){
            $column = implode(',', $this->columns);
        }else{
            $column = $this->column;
        }
        $sql .= ' ' . $column . ' from `' . $this->table . '`';
        foreach ($this->joins as $v) {
            $sql .= ' ' . $v;
        }
        $sql .= $this->getWhere();
        if ($this->group_by) {
            $sql .= ' group by ' . implode(',', $this->group_by);
        }

        if ($this->order_by) {
            $sql .= ' order by ' . implode(',', $this->order_by);
        }
        if ($this->limit) {
            $sql .= ' limit ' . $this->limit;
        }
        return $sql;
    }

    /**
     * 获取where条件字符串
     * @return string
     * @author ELLER
     */
    private function getWhere()
    {
        list($this->build, $where) = $this->toWhere();
        if ($where) {
            $where = ' where ' . $where;
        }
        return $where;
    }

    /**
     * 获得MySQL链接
     * @return Connect
     * @author ELLER
     */
    public function getConnect()
    {
        return $this->connect;
    }

    /**
     * 获取插入SQL语句
     * @param $data
     * @param bool $isMulit
     * @return string
     * @author ELLER
     */
    private function getInsertSql($data, $isMulit = false)
    {
        $sql = 'insert into `' . $this->table . '`';
        if ($isMulit) {
            $build  = [];
            $keys   = array_keys($this->filter($data[0], true));
            $sql    .= ' (' . implode(',', $keys) . ')';
            $values = [];
            foreach ($data as $v) {
                $v        = $this->filter($v, true);
                $build    = array_merge($build, array_values($v));
                $values[] = '(' . substr(str_repeat(',?', count($keys)), 1) . ')';
            }
            $sql .= ' values ' . implode(',', $values);
        } else {
            $data  = $this->filter($data, true);
            $keys  = array_keys($data);
            $sql   .= ' (' . implode(',', $keys) . ')';
            $build = array_values($data);
            $sql   .= ' values (' . substr(str_repeat(',?', count($keys)), 1) . ')';
        }
        $this->build = $build;
        return $sql;
    }

    /**
     * 获取更新语句
     * @param $data
     * @return bool|string
     * @author ELLER
     */
    private function getUpdateSql($data)
    {
        $sql   = 'update `' . $this->table . '` set ';
        $build = [];
        $data  = $this->filter($data);
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $sql .= "{$k}={$v[0]},";
            } else {
                $sql     .= "{$k}=?,";
                $build[] = $v;
            }
        }
        $sql = substr($sql, 0, -1);
        $this->setPriWhere();
        $sql         .= $this->getWhere();
        $this->build = array_merge($build, $this->build);
        return $sql;
    }

    /**
     * 设置主键WHERE条件
     * @author ELLER
     */
    private function setPriWhere()
    {
        if (!$this->where) {
            $pri = $this->getPriKey();
            if (property_exists($this->model, $pri)) {
                $this->where($pri, $this->model->$pri);
            }
        }
    }

    /**
     * 获取删除语句
     * @return string
     * @author ELLER
     */
    private function getDeleteSql()
    {
        $sql = 'delete from `' . $this->table . '`';
        $this->setPriWhere();
        $sql .= $this->getWhere();
        return $sql;
    }

    /**
     * 插入数据
     * @param $data
     * @param bool $isMulit
     * @return mixed
     * @throws DbException
     * @author ELLER
     */
    public function create($data, $isMulit = false)
    {
        $r = $this->connect->exec($this->getInsertSql($data, $isMulit), $this->build, true);
        return $r;
    }

    /**
     * 求和
     * @param $column
     * @return mixed
     * @throws DbException
     * @author ELLER
     */
    public function sum($column)
    {
        $this->column = "sum({$column}) as sum_value";
        $res              = $this->find();
        return $res->sum_value;
    }

    /**
     * 更新数据
     * @param $data
     * @return mixed
     * @throws DbException
     * @author ELLER
     */
    public function update($data)
    {
        $r = $this->connect->exec($this->getUpdateSql($data), $this->build);
        return $r;
    }

    /**
     * 删除数据
     * @return mixed
     * @throws DbException
     * @author ELLER
     */
    public function delete()
    {
        $r = $this->connect->exec($this->getDeleteSql(), $this->build);
        return $r;
    }

    /**
     * 设置表名
     * @param $name
     * @return $this
     * @author ELLER
     */
    public function table($name)
    {
        $this->table = $name;
        return $this;
    }

    /**
     * 设置查询字段
     * @param array $columns
     * @return $this
     * @author ELLER
     */
    public function column(array $columns)
    {
        $this->columns = array_merge($this->columns, $columns);
        return $this;
    }

    /**
     * 左关联
     * @param string $table
     * @param string $first 条件a
     * @param string $second 条件b
     * @return $this
     */
    public function leftJoin($table, $first, $second = null)
    {
        return $this->join($table, $first, $second, 'left');
    }

    /**
     * 右关联
     * @param string $table
     * @param string $first
     * @param string $second
     * @return $this
     */
    public function rightJoin($table, $first, $second = null)
    {
        return $this->join($table, $first, $second, 'right');
    }

    /**
     * 关联查询
     * @param string $table
     * @param string|\Closure $first
     * @param string $second
     * @param string $type
     * @return $this
     */
    public function join($table, $first, $second = null, $type = 'inner')
    {
        $join = new Join($table, $first, $second, $type);
        list($data, $str) = $join->get();
        $this->joins[] = $str;
        if ($data) {
            $this->whereRaw('', $data, '');
        }
        return $this;
    }


    public function __call($name, $arguments)
    {
        if (method_exists($this->model, $name)) {
            return $this->model->$name(...$arguments);
        } else {
            throw new DbException('Undefined method ' . $name, 556);
        }
    }


}