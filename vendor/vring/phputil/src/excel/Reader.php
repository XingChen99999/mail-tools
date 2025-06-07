<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022/7/15 0015
 * Time: 19:02
 */

namespace vring\excel;


use vring\util\Char;
use vring\util\Str;

abstract class Reader
{
    protected $readCount = -1;

    protected $concern = [
//        '0' => 'name'
    ];
    protected $sheetIndex = 0;
    protected $sheetName = '';
    protected $isHeadParse = false;
    protected $headParseLine = 1;
    protected $readMaxCount = 25;
    protected $headParseConcern = [
        ['单位', 'unit_name']
    ];

    protected $headData = [];

    protected $isUtf8 = false;

    public function __construct(string $filePath)
    {
        $this->openFile($filePath);
        $this->loadData();
    }

    /**
     * 抽象方法，打开文件，要求子类实现
     * @param string $filePath
     * @return void
     */
    abstract protected function openFile(string $filePath): void;

    /**
     * 表格数据
     * */
    protected $data = [];

    /**
     * @param $headParseConcern
     * @param int $headParseLine
     * @param int $readMaxCount
     */
    public function setHeadParse($headParseConcern, $headParseLine = 0, $readMaxCount = 25)
    {
        $this->isHeadParse = true;
        $this->headParseLine = $headParseLine;
        $this->headParseConcern = $headParseConcern;
        $this->readMaxCount = $readMaxCount;
    }

    /**
     * 要求子类加载数据
     * @return void
     * */
    abstract protected function loadData();

    /**
     * @param bool $onlyReadConcern
     * @return \Generator
     */
    public function read($onlyReadConcern = FALSE)
    {
        $this->readCount = -1;
        foreach ($this->data as $data) {
            $this->readCount++;
            $data = self::filterEmptyLineData($data);
            if ($data === false) {
                continue;
            }
            $data = $this->dataDecode($data);
            $data = $this->parseData($data, $onlyReadConcern);
            if ($data === false) {
                continue;
            }

            yield $data;
        }
        return $this->readCount;
    }

    /**
     * @param $data
     * @param callable $action
     * @return array
     */
    protected function eachData($data, callable $action)
    {

        $result = [];
        $count = 0;
        foreach ($data as $key => $item) {
            if ($count >= $this->readMaxCount) {
                break;
            }
            $count++;

            if (($re = $action($item, $key)) !== false) {
                $result[$key] = $re;
            } else {
                break;
            }
        }
        return $result;

    }

    /**
     * @param $data
     * @param bool $onlyReadConcern
     * @return array|bool
     */
    protected function parseData($data, $onlyReadConcern = FALSE)
    {
        if (empty($this->concern) and $this->isHeadParse) {
            $isNotHead = $this->headParseLine !== ($this->readCount + 1);
            if ($isNotHead) {
                return false;
            }
            foreach ($this->headParseConcern as $v) {
                list($search, $key_name) = $v;
                $this->eachData($data, function ($item, $key) use ($search, $key_name) {
                    if (strpos(Str::removeSpace($item), $search) !== false) {
                        $this->concern[$key] = $key_name;
                    }
                });
                /* foreach ($data as $key => $item) {
                     if (strpos($item, $search) !== false) {
                         $this->concern[$key] = $key_name;
                     }
                 }*/
            }
            if (!$isNotHead) {
                return false;
            }
        }
        $result = [];
        $param = $onlyReadConcern ? $this->concern : $data;
        foreach ($param as $k => $v) {
            $result[$this->concern[$k] ?: $k] = trim($data[$k]);
        }
        return $result;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function dataDecode(array $data)
    {
        return $this->eachData($data, function ($item) {
            return $this->isUtf8 ?$item:Char::gbkToUtf8($item);
        });
    }

    /**
     * @param $data
     * @return mixed
     */
    static protected function filterEmptyLineData($data)
    {
        if (!is_array($data)) {
            return false;
        }
        $count = count($data);
        if (count($data) <= 0) {
            return false;
        }

        $empty_num = 0;
        foreach ($data as $v) {
            if (empty($v)) {
                $empty_num++;
            }
        }
        if ($empty_num === $count) {
            return false;
        }
        return $data;
    }


    /**
     * @param bool $isHeadParse
     */
    public function setIsHeadParse(bool $isHeadParse)
    {
        $this->isHeadParse = $isHeadParse;
    }

    /**
     * @param int $sheetIndex
     */
    public function setSheetIndex(int $sheetIndex): void
    {
        $this->sheetIndex = $sheetIndex;
    }

    /**
     * @param string $sheetName
     */
    public function setSheetName(string $sheetName): void
    {
        $this->sheetName = $sheetName;
    }


}