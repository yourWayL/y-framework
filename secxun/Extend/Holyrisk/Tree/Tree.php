<?php
/**
 * @description 树形 计算 | 改造 PHPTree 类
 * @author Holyrisk
 * @date 2020/4/28 14:17
 */

namespace Secxun\Extend\Holyrisk\Tree;


class Tree
{

    /**
     * @description 配置项
     * @author Holyrisk
     * @date 2020/4/28 16:07
     * @var array
     */
    protected static $config = array(
        /* 主键 */
        'primary_key' => 'id',
        /* 父键 */
        'parent_key' => 'parent_id',//parent_id
        /* 展开属性 */
        'expanded_key' => 'expanded',
        /* 叶子节点属性 */
        'leaf_key' => 'leaf',
        /* 孩子节点属性 */
        'children_key' => 'children',
        /* 是否展开子节点 */
        'expanded' => false
    );

    /* 结果集 */
    protected static $result = array();

    /* 层次暂存 */
    protected static $level = array();

    /**
     * @description 生成树形结构
     * @author Holyrisk
     * @date 2020/4/28 15:13
     * @param $data 二维数组
     * @param int $index 下标 父级 id  用作 索引
     * @param array $options 多维数组
     * @return array
     */
    public static function makeTree($data,$index = 0, $options = array())
    {
        $dataset = self::buildData($data, $options);
        $r = self::makeTreeCore($index, $dataset, 'normal');
        return $r;
    }

    /* 格式化数据, 私有方法 */
    private static function buildData($data, $options)
    {
        $config = array_merge(self::$config, $options);
        self::$config = $config;
        extract($config);
        $r = array();
        foreach ($data as $item) {
            $id = $item[$primary_key];
            $parent_id = $item[$parent_key];
            $r[$parent_id][$id] = $item;
        }
        return $r;
    }

    /* 生成树核心, 私有方法  */
    private static function makeTreeCore($index,$data,$type='linear')
    {
        extract(self::$config);
        foreach($data[$index] as $id=>$item)
        {
            if($type=='normal'){
                if(isset($data[$id]))
                {
                    $item[$expanded_key]= self::$config['expanded'];
                    $item[$children_key]= self::makeTreeCore($id,$data,$type);
                }
                else
                {
                    $item[$leaf_key]= true;
                }
                $r[] = $item;
            }else if($type=='linear'){
                $parent_id = $item[$parent_key];
                self::$level[$id] = $index==0?0:self::$level[$parent_id]+1;
                $item['level'] = self::$level[$id];
                self::$result[] = $item;
                if(isset($data[$id])){
                    self::makeTreeCore($id,$data,$type);
                }

                $r = self::$result;
            }
        }
        return $r;
    }

    /**
     * @description 直接引用 | 生成 树形 | 只满足一级
     * @author 绉耀存
     * @date 2020/4/28 16:04
     * @param $list
     * @param $parent
     * @return array
     */
    public function treeData(&$list, $parent){
        $tree = array();
        foreach($list as $row) {
            if($row['parent_id'] == $parent) {
                $row['children'] = $this->treeData($list, $row['id']);
                $tree[] = $row;
            }
        }
        return $tree;
    }

}