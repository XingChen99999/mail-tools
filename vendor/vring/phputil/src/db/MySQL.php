<?php
/**
 * Created by util.
 * User: Vnser
 * Date: 2020/5/1 0001
 * Time: 10:38
 */

namespace vring\db;

class MySQL extends Db
{
    protected $options = [
        'host'=>'127.0.0.1',
        'port'=>'3306',
        'username'=>'root',
        'password'=>'root',
        'charset'=>'utf8',
        'dbname'=>'test'
    ];

    /**
     * 取得PDO链接dns
     * @return string
     * */
    protected function getDbDsn()
    {
        return "mysql:host={$this->options['host']};port={$this->options['port']};charset={$this->options['charset']};dbname={$this->options['dbname']}" ;

    }
}