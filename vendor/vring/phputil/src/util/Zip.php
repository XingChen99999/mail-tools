<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022/7/25 0025
 * Time: 10:16
 */

namespace vring\util;


use ZipArchive;

class Zip
{
    /**
     * @var ZipArchive
     * */
    private $zip;

    public function __construct(string $zipFile)
    {
        $this->openZip($zipFile);
    }

    protected function openZip(string $zipFile)
    {
        $this->zip = new ZipArchive;
        $this->zip->open($zipFile);
    }

    public function extractTo(string $dirName)
    {

        $zip = $this->zip;
        !file_exists($dirName) and @mkdir($dirName);

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $info = $zip->statIndex($i);
//            print_r($info);
//            $this->debug = $info;
            $info['name'] = self::repairChar($info['name']);
            $disFile = $dirName . DIRECTORY_SEPARATOR . $info['name'];
            if ($info['size'] === 0 and $info['crc'] === 0) {
                @mkdir($disFile, 0777, true);
            } else {
                $content = $zip->getFromIndex($i);
                file_put_contents($disFile, $content);
            }
            yield $info['name'];
        }

    }

    /**
     * 纠正错误zip扩展错误中文转码
     * @param string $messyCode
     * @return string
     */
    static public function repairChar(string $messyCode): string
    {
        $orgStr = $messyCode;

        if (Char::isGbk($messyCode)) {
            $con = iconv('GBK','utf-8',$orgStr);
            return $con;
        }
        $messyCode = iconv('UTF-8', 'IBM862', $messyCode);
        if (!$messyCode){
            return $orgStr;
        }
        $messyCode = iconv('GBK', 'UTF-8', $messyCode);
        return $messyCode;
    }
}