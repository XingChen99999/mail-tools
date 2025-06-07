<?php
/**
 * Created by yrspider.
 * User: Vnser
 * Date: 2021/3/6 0006
 * Time: 16:21
 */

namespace vring\page;

class DataPage extends PageRender
{

    public function render()
    {
        $pageHtml = "";
        if ($this->page->getTotalPage() > 1) {
            $pageHtml .= '<div class="dataTables_wrapper no-footer">' .
                '<div class="dataTables_paginate paging_simple_numbers"><!--	<div class="dataTables_info"  role="status" aria-live="polite">显示 1 到 10 ，共 10 条</div>
-->' . '<a class="paginate_button previous disabled" href="'.$this->url->url(['page'=>$this->page->prevPage()]).'">上一页</a>';
                 if (1 != $this->page->getPage()) {
                     $pageHtml .= '<a class="paginate_button " href="'.$this->url->url(['page'=>1]).'">...</a>';
                 }

                    $pageNum = $this->page->getPage();
                    $showPageBtnNum = 5;
                    $start = $end = 0;
                    $start = $pageNum - floor($showPageBtnNum / 2);
                    $end = $pageNum + floor($showPageBtnNum / 2);
                    if ($showPageBtnNum % 2 == 0 and $end + $start > $showPageBtnNum) {
                        $start += 1;
                    }
                    $total = $this->page->getTotalPage();
                    if ($end > $total) {
                        $start = $total - $showPageBtnNum + 1;
                        $end = $total;
                    }
                    if ($start <= 0) {
                        $start = 1;
//                        $end = $showPageBtnNum;
                    }


                    for ($i = $start; $i <= $end; $i++) {
                        $pageHtml .= '<a class="paginate_button ' . ($this->page->isCurrent($i) ? 'current' : '') . '" href="'.$this->url->url(['page'=>$i]).'">' . $i . '</a>';
                    }
                  if ($this->page->getTotalPage() !== $this->page->getPage()) {
                      $pageHtml .= '<a class="paginate_button ' . ($this->page->isCurrent($i) ? 'current' : '') . '" href="'.$this->url->url(['page'=>$this->page->getTotalPage()]).'">... ' . $this->page->getTotalPage() . '</a>';
                  }
                    $pageHtml .= '<a class="paginate_button next disabled" href="'.$this->url->url(['page'=>$this->page->nextPage()]).'">下一页</a>'
                        . '</div>'
                        . '</div>';

         }
        echo $pageHtml;
    }

}