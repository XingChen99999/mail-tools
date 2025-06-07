<?php
/**
 * Created by PhpStorm.
 * User: vring
 * Date: 2022/4/9
 * Time: 15:31
 */

namespace vring\util;


class EventSource
{

    public function __construct()
    {
        ini_set('zlib.output_compression',false);
        $this->responseEventHeader();
    }

    private function responseEventHeader()
    {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
    }

    public function responseEvent($data, $event = null, $id = null)
    {
        $map = [
            'id' => [$id,'',true],
            'event' => [$event,'',true],
            'data' => [$data,'data:',false]
        ];
        while (@ob_end_flush());//关闭所有缓冲区
        (ob_start(null,1));
        foreach ($map as $key => $item){
            $message = $item[0];
            if ($message){
                $message = $this->stripLine($message,$item[2]);
                $message = $this->formatData($message,$key);
                echo "{$message}\n";
            }
        }
        echo "\n";
        (ob_flush());
        flush();
    }

    private function stripLine($str,$isDel = false)
    {

        return preg_replace(($isDel?'/(?:\r\n|[\r\n])/':'/(?:\r\n|[\r\n]){2,}/'), ($isDel ? "":"\n"), trim(trim($str,"\r\n"),"\r\n"));
    }

    private function formatData($str,$prefix = 'data')
    {
        return preg_replace('/([^\r\n]+(?:\r\n|[\r\n])?)/', $prefix.":$1", $str);
    }
}