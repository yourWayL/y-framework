<?php


namespace Secxun\Extend\Eller\Database;


/**
 * Class Model
 * @package Secxun\Extend\Eller\Database
 *
 * @method static array get()
 * @method static mixed find($id = null)
 * @method static array|bool paginate($pageSize = 10)
 * @method static int count()
 * @method static Build orderBy($order)
 * @method static Build groupBy($order)
 * @method static Build limit($limit, $skip = 0)
 * @method static Connect getConnect()
 * @method static bool|int create($data, $isMulit = false)
 * @method static int sum($column)
 * @method static mixed update($data)
 * @method static mixed delete()
 * @method static Build table($name)
 * @method static mixed column(array $columns)
 * @method static Build leftJoin($table, $first, $second = null)
 * @method static Build rightJoin($table, $first, $second = null)
 * @method static Build join($table, $first, $second = null, $type = 'inner')
 * @method static Build where($key, $operator = null, $val = null, $link = ' and ')
 * @method static Build whereOr($key, $operator = null, $val = null)
 * @method static Build whereIn($key, array $val)
 * @method static Build whereNotIn($key, array $val)
 * @method static Build whereRaw($str, array $build_data = null, $link = ' and ')
 * @method static Build whereNotNull($key)
 * @method static Build whereNull($key)
 * @method static array getFields()
 *
 * @see Build
 */
class Model extends ArrayModel
{

    protected $_build = null;
    protected $table = "";

    private function build()
    {
        if (!$this->_build) {
            $this->_build = new Build($this, get_called_class(), $this->table);
        }
        return $this->_build;
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this, $name) === false) {
            return $this->build()->$name(...$arguments);
        } else {
            throw new DbException('call method ' . $name . ' fail , is not public method');
        }
    }

    public static function __callStatic($method, $parameters)
    {
        return (new static)->$method(...$parameters);
    }

    public function toArray()
    {
        $obj = get_object_vars($this);
        foreach ($obj as &$v) {
            if (is_object($v)) {
                $v = $v->toArray();
            }
        }
        return $obj;
    }
}