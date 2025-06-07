<?php
/**
 * Created by PhpStorm.
 * User: vring
 * Date: 2021/6/1
 * Time: 20:11
 */
namespace vring\util;
class FileResponse
{
    private $fp;
    private $fileName;
    private $fileSize;
    private $readSize = 1048576; //1M

    public function __construct($path, $file, $fSize = null)
    {
        set_time_limit(0);
        ob_implicit_flush(true);
        ignore_user_abort();
        $this->fp = fopen($path, 'r');
        $this->fileName = $file;
        $this->fileSize = (float)$fSize ?: filesize($path);
    }

    public function responseFileBinary()
    {

        $conLength = $this->fileSize;
        list($range_byte, $range_byte_end) = $this->getRequestRange();

        if ($range_byte > 0 or $range_byte_end < $this->fileSize - 1) {
            header('HTTP/1.1 206 Partial Content');
            header("Content-Range: bytes {$range_byte}-" . $range_byte_end . "/{$this->fileSize}");
            fseek($this->fp, $range_byte);//偏移文件
            $conLength = $range_byte_end - $range_byte + 1;
        }
        /*响应header部分*/
        header('Content-Transfer-Encoding: binary');
        header('Content-Disposition:attachment; filename="' . iconv('utf-8', 'gbk', $this->fileName) . '"');
        header('Content-Type: application/octet-stream');
        header('Accept-Ranges:bytes');
        header('Content-Length: ' . $conLength);
        while (ob_end_flush()){};
        $readLength = $this->readSize;
        $end_tell = $range_byte_end + 1;
        while (true) {
            $tell = ftell($this->fp);
            if ($tell == $end_tell) {
                break;
            }
            $tell_length = $end_tell - $tell;
            if ($tell_length < $this->readSize) {
                $readLength = $tell_length;
            }
            ob_clean();
            echo fread($this->fp, $readLength);
            ob_flush();
//            flush();
        }
        fclose($this->fp);

    }

    protected function getRequestRange()
    {
        $range = $_SERVER['HTTP_RANGE'];
        $max_range = $this->fileSize - 1;
        $range_byte = 0;
        $range_byte_end = $max_range;

        if ($range) {
            preg_match('/bytes\=(\d+)\-(\d*)/', $range, $mat);
            $range_byte = (float)$mat[1];
            $range_byte_end = (float)$mat[2] ?: $max_range;
            $range_byte_end = $range_byte_end >= $max_range ? $max_range : $range_byte_end;
        }

        return [$range_byte, $range_byte_end];
    }
}