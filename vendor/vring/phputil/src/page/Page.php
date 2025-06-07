<?php
/**
 * Created by stock.
 * User: Vnser
 * Date: 2020/11/16 0016
 * Time: 15:49
 */

namespace vring\page;
class Page
{
    private $listPage;
    private $total;
    private $totalPage;
    private $offset;
    private $page;

    public function __construct($total = 0, $page = 0, $listPage = 10)
    {

        $this->page = (int)($page <= 0 ? 1 : $page);
//        var_dump($this->page);
        $this->listPage = $listPage;
//        if ($total >= 0){
        //计算分页
        $this->total = $total;
        $this->compute();


    }


    /**
     * @return int
     */
    public function getListPage()
    {
        return $this->listPage;
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return mixed
     */
    public function getTotalPage()
    {
        return (int)$this->totalPage;
    }

    /**
     * @return mixed
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    public function compute()
    {

        if ($this->total > 0) {
            $this->totalPage = ceil($this->total / $this->listPage);
            $this->totalPage = $this->totalPage <= 0 ? 1 : $this->totalPage;
            $this->page = $this->page >= $this->totalPage ? $this->totalPage : $this->page;
        }


        $this->offset = $this->listPage * ($this->page - 1);
    }

    public function prevPage()
    {
        $prev = $this->page - 1;
        return $prev > 0 ? $prev : 1;
    }

    public function nextPage()
    {
        $next = $this->page + 1;
        return $next > $this->getTotalPage() ? $this->getTotalPage() : $next;
    }

    public function getFindLimit()
    {
        return "{$this->getOffset()},{$this->getListPage()}";
    }

    /**
     * @param int $page
     * @return bool
     */
    public function isCurrent($page)
    {
        return $this->getPage() == $page;
    }


    /**
     * @param int $page
     */
    public function setPage($page)
    {
//        $this->page = $page;
        $this->page = $page <= 0 ? 1 : $page;
//        $this->compute();
    }

    /**
     * @param int $listPage
     */
    public function setListPage($listPage)
    {
        $this->listPage = $listPage;
//        $this->compute();
    }

    /**
     * @param int $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
//        $this->compute();
    }
}