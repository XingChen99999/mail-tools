<?php
/**
 * Created by yrspider.
 * User: Vnser
 * Date: 2021/3/9 0009
 * Time: 20:29
 */

namespace vring\util;


class Response
{

    public static function json($code = 200, $msg = '', $data = array())
    {
        header('content-type:application/json');
        exit(json_encode(array('code' => $code, 'msg' => $msg, 'data' => $data)));
    }
}