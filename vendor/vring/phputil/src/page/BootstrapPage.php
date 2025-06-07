<?php
/**
 * Created by yrspider.
 * User: Vnser
 * Date: 2021/3/6 0006
 * Time: 16:52
 */

namespace vring\page;

class BootstrapPage extends PageRender
{


    public function render($showPageBtnNum = 7)
    {
        $pageHtml = "";
        if ($this->page->getTotalPage() > 1) {

            $pageNum = $this->page->getPage();
            $total = $this->page->getTotalPage();
//            $start = $end = 0;
            $avg = floor($showPageBtnNum / 2);
            $start = $pageNum - $avg;
            $end = $pageNum + $avg;
//            var_dump($start,$end);
            if ($showPageBtnNum % 2 === 0) {
                //偶数
                $start += 1;
            }

            if ($end > $total) {
                $start = $total - $showPageBtnNum + 1;
                $end = $total;
            }
            if ($start <= 0) {
                $start = 1;
                if ($total >= $showPageBtnNum) {
                    $end = $showPageBtnNum;
                } else {
                    $end = $total;
                }
            }
//                $end = $showPageBtnNum;
            $pageHtml .= '<ul class="pagination">' . '<li><a href="' . $this->url->url(['page' => $this->page->prevPage()],true) . '">&laquo;</a></li>';
            if (1 != $start) {
                $pageHtml .= '<li><a href="' . $this->url->url(['page' => 1],true) . '">...</a></li>';
            }
            for ($i = $start; $i <= $end; $i++) {
                $pageHtml .= '<li class=" ' . ($this->page->isCurrent($i) ? 'active' : '') . '"><a  href="' . $this->url->url(['page' => $i],true) . '">' . $i . '</a></li>';
            }
            if ($total != $end) {
                $pageHtml .= '<li class=" ' . ($this->page->isCurrent($i) ? 'active' : '') . '"><a  href="' . $this->url->url(['page' => $total],true) . '">... ' . $total . '</a></li>';
            }
            $pageHtml .= '<li><a href="' . $this->url->url(['page' => $this->page->nextPage()],true) . '">&raquo;</a></li>'
                . '</ul>';

        }
        echo $pageHtml;
    }


}