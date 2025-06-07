<?php
/**
 * Created by PhpStorm.
 * User: vring
 * Date: 2022/11/15
 * Time: 19:02
 */

namespace vring\util;


class Sys
{
    static public function disableCacheOb()
    {
        while (@ob_end_flush()) {
        }
        ob_implicit_flush(true);
        ob_start(null, 1);
    }

}