<?php
/**
 * Created by PhpStorm.
 * User: vring
 * Date: 2021/10/21
 * Time: 15:25
 */

namespace vring\db;


abstract class Db
{
//    protected static $_default;
    protected static $_instance = [];
    protected $options = [];
    protected $pdoOption = array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION);
    protected $pdo;


    /**
     * 单例模式
     * @param $options
     * @return mixed
     */
    static public function instance($options)
    {
        $_instance =& static::$_instance[join(',', $options)];
        if (!isset($_instance)) {
            $_instance = new static($options);
        }
        return $_instance;
    }


    /**
     * @param $options
     * @return Db
     * @throws \Exception
     */
    static public function init($options)
    {
        $method = '\vring\db\\' . "{$options['type']}";
        if (!class_exists($method)) {
            throw new \Exception("未找到‘{$options['type']}’的数据库驱动");
        }
        $instance = $method::instance($options);
        return $instance;
    }

    /**
     * Db constructor.
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->withOption($options);
    }

    /**
     * @param $options
     * @return $this
     */
    public function withOption($options)
    {
        $this->options = array_merge($this->options, $options);
        return $this;
    }


    /**
     * 多条查询
     * @param string $sql
     * @param array $param
     * @return array
     */
    public function select($sql, array $param = array())
    {
        return $this->query($sql, $param);
    }

    /**
     * 单条查询
     * @param string $sql
     * @param array $param
     * @return array
     */
    public function find($sql, array $param = array())
    {
        $res = $this->query($sql, $param);
        return $res[0] ? $res[0] : array();
    }

    /**
     * 执行sql,返回影响行数
     * @param string $sql
     * @param array $param
     * @return false|int
     */
    public function exec($sql, array $param = array())
    {
        $sta = $this->getPdo()->prepare($sql);
        if ($sta->execute($param)) {
            return $sta->rowCount();
        } else {
            return false;
        }

    }

    public function column($sql, $name = '')
    {
        $data = $this->columns($sql);
        if ($data) {
            if (empty($name)) {
                $keys = array_keys($data);
                return $data[$keys[0]];
            }
            return $data[$name];
        }
        return $data;

    }

    public function columns($sql)
    {
        $find = $this->select($sql);
        if (!$find) {
            return null;
        }
        $result = [];
//        foreach ($find[0] as $key=>$item){
        foreach ($find as $k => $v) {
            foreach ($v as $key => $val) {
                $result[$key][$k] = $val;
            }
//                $val = array_values($v);

        }
//        }

        return $result;
    }

    /**
     * 查询行操作
     * @param $sql
     * @param mixed $name
     * @return 0|mixed|null
     */
    public function findColumn($sql, $name = null)
    {
        $find = $this->find($sql);
        if (!$find) {
            return null;
        }
        if (!$name) {
            $result = array_values($find);
            return $result[0];
        }
        return $find[$name];
    }

    /**
     * 查询操作
     * @param string $sql
     * @param array $param
     * @return array
     */
    public function query($sql, array $param = array())
    {
        $sql = $this->getPdo()->prepare($sql);
        $sql->execute($param);
        return $sql->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * 开启事物
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->getPdo()->beginTransaction();
    }

    /**
     * 提交事物
     * @return mixed
     */
    public function commit()
    {
        return $this->getPdo()->commit();
    }

    /**
     * 回滚事物
     * @return mixed
     */
    public function rollback()
    {
        return $this->getPdo()->rollback();
    }

    /**
     * @return \PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * @param mixed $pdo
     */
    public function setPdo($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * 创建pdo数据库对象
     * @return void
     * */
    public function connect()
    {
        if ($this->pdo) {
            return;
        }
        $pdo = new \PDO($this->getDbDsn(), $this->options['username'], $this->options['password'], $this->pdoOption);
        $this->setPdo($pdo);
    }


    /**
     * 数据库链接Dsn
     * @return string
     * */
    abstract protected function getDbDsn();

}