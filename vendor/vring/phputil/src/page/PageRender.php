<?php
/**
 * Created by yrspider.
 * User: Vnser
 * Date: 2021/4/27 0027
 * Time: 15:56
 */

namespace vring\page;

use vring\util\Url;

abstract class PageRender implements \Iterator
{
    protected $page;
    protected $url;
    protected $data;
    protected $dataI = 0;

    /**
     * PageRender constructor.
     * @param Page $page
     * @param Url $url
     */
    public function __construct(Page $page, Url $url)
    {
        $this->url = $url;
        $this->page = $page;
    }

    public function isEmpty()
    {
        return (bool)($this->data);
    }

    abstract public function render();

    /**
     * 设置查询数据
     * @param $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Return the current element
     * @link https://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return $this->data[$this->dataI];
    }

    /**
     * Move forward to next element
     * @link https://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        $this->dataI++;
    }

    /**
     * Return the key of the current element
     * @link https://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return $this->dataI;
    }

    /**
     * Checks if current position is valid
     * @link https://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return ($this->dataI) !== count($this->data);
    }

    /**
     * Rewind the Iterator to the first element
     * @link https://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->dataI = 0;
    }
}