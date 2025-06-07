<?php
/**
 * Created by yrspider.
 * User: Vnser
 * Date: 2021/5/1 0001
 * Time: 15:42
 */

namespace vring\util;


class Str
{
    /**
     * 省略多余字符,追加占位支付
     * @param \string $str
     * @param int $maxLength
     * @param \string $placeholder
     * @return \string
     */
    static public function omit($str, $maxLength = 10, $placeholder = '...')
    {
        $resStr = $str;
        if (mb_strlen($str, 'utf-8') > $maxLength) {

            $resStr = mb_substr($str, 0, $maxLength, 'utf-8');
            $resStr .= $placeholder;
        }
        return $resStr;
    }

    /**
     * 删除字符串中的所有空格
     * @param $content
     * @return mixed
     */
    static public function removeSpace($content)
    {
        return str_replace([' ',"\t","\r","\n"], '', $content);
    }


    /**
     * 字符串对齐
     * @param $str
     * @param int $width
     * @param string $glue
     * @return string
     */
    static public function align($str, $width = 30, $glue = ' ')
    {
        $length = mb_strlen($str);
        $diff = $width - $length;
        $rep = '';
        if ($diff > 0) {
            $rep = str_repeat($glue, $diff);
        }
        return "{$str}{$rep}";
    }
}