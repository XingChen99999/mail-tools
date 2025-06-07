<?php
/**
 * Created by 育人爬虫.
 * User: Vnser
 * Date: 2021/1/7 0007
 * Time: 9:49
 */
namespace vring\util;
class Http
{
    public static function getHeader($link)
    {
        $file_header = @get_headers($link);
        if (!$file_header) {
            return false;
        }
        $header = [];
        foreach ($file_header as $v) {
            list($key, $val) = explode(':', $v, 2);
            $header[$key] = trim($val);
        }
        return $header;
    }

    static public function parseCookie($str)
    {
        $exp = explode(';', $str);
        $cookie = [];
        foreach ($exp as $v) {
            $kv = explode('=', $v, 2);
            $cookie[trim($kv[0])] = trim($kv[1]);
        }
        return $cookie;
    }
}