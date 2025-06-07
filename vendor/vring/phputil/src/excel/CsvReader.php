<?php
/**
 * Created by PhpStorm.
 * User: vring
 * Date: 2021/9/27
 * Time: 16:15
 */

namespace vring\excel;

class CsvReader extends Reader
{
    protected $fp;


    /**
     * 要求子类加载数据
     * @return void
     * */
    protected function loadData()
    {
        $fp = $this->getFp();
        while (!feof($fp)) {
            $this->data[] = $data = fgetcsv($fp);
        }
    }


    /**
     * @return mixed
     */
    public function getFp()
    {
        return $this->fp;
    }

    /**
     * @param mixed $fp
     */
    public function setFp($fp)
    {
        $this->fp = $fp;
    }

    protected function openFile(string $filePath):void
    {
        $fp = @fopen($filePath, 'r');
        if ($fp === false) {
            throw new \Exception("读取文件失败'{$filePath}'.");
        }
        $this->setFp($fp);
    }


}