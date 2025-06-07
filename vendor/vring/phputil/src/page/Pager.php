<?php
/**
 * Created by PhpStorm.
 * User: vring
 * Date: 2022/3/7
 * Time: 16:09
 */

namespace vring\page;


abstract class Pager
{
    protected $where;
    abstract public function pageCount();
    abstract public function page($limit);
    public function setWhere($where){
        $this->where = $where;
    }
    public function __construct($where = '')
    {
        $this->where = $where;
    }
}