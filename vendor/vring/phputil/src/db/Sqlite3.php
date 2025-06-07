<?php
/**
 * Created by util.
 * User: Vnser
 * Date: 2020/5/1 0001
 * Time: 10:38
 */

namespace vring\db;


class Sqlite3 extends Db
{

//    protected $_pdo;
    protected $db_filename;

    public function __construct($options)
    {
        parent::__construct();
        $this->setDbFilename($options['db_file']);
        $this->connect();
    }

    /**
     * 取得PDO链接dns
     * @return string
     * */
    protected function getDbDsn()
    {
        return 'sqlite:' . $this->getDbFilename();
    }


    /**
     * @return mixed
     */
    public function getDbFilename()
    {
        return $this->db_filename;
    }

    /**
     * @param mixed $db_filename
     */
    public function setDbFilename($db_filename)
    {
        $this->db_filename = $db_filename;
    }

}