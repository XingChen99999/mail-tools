<?php
/**
 * Created by PhpStorm.
 * User: vring
 * Date: 2021/10/16
 * Time: 14:50
 */

namespace vring\util;


class Arr
{
    static public function assocToIndex($array)
    {
        $result = [];
        foreach ($array as $k => $v) {
            foreach ($v as $key => $item) {
                $result[$key][$k] = $item;
            }
        }
        return $result;
    }
}