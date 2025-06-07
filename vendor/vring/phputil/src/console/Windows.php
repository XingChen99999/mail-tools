<?php
/**
 * Created by PhpStorm.
 * User: vring
 * Date: 2021/10/6
 * Time: 15:40
 */

namespace vring\console;
use vring\util\Char;

class Windows extends Console
{
    /**
     * @param $message
     * @return mixed
     */
    public function prompt($message)
    {
        echo $message;
        $fp = popen("set /p input= && set input", 'r');
        $result = stream_get_contents($fp, 1024);
        pclose($fp);
        return Char::compatEncoding(str_replace(['input=',"\r","\n"], '', $result),true);
    }
}