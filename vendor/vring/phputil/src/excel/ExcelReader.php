<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022/7/15 0015
 * Time: 19:21
 */

namespace vring\excel;
require_once LIBRARY_PATH . '/phpexcel/PHPExcel.php';
require_once LIBRARY_PATH . '/phpexcel/PHPExcel/Reader/Excel5.php';
require_once LIBRARY_PATH . '/phpexcel/PHPExcel/Reader/Excel2007.php';
require_once LIBRARY_PATH . '/phpexcel/PHPExcel/Writer/Excel5.php';
require_once LIBRARY_PATH . '/phpexcel/PHPExcel/Writer/Excel2007.php';
require_once LIBRARY_PATH . '/phpexcel/PHPExcel/Writer/Excel5.php';

use PHPExcel_Reader_Excel2007;
use PHPExcel_Reader_Excel5;

class ExcelReader extends Reader
{

    protected $isUtf8 = true;

    /**
     * @var \PHPExcel
     * */
    protected $excel;

    /**
     * @return \PHPExcel
     */
    public function getExcel(): \PHPExcel
    {
        return $this->excel;
    }

    /**
     * @param \PHPExcel $excel
     */
    public function setExcel(\PHPExcel $excel): void
    {
        $this->excel = $excel;
    }

    /**
     * 抽象方法，打开文件，要求子类实现
     * @param string $filePath
     * @return void
     * @throws \Exception
     */
    protected function openFile(string $filePath): void
    {
        $ext = strtolower(pathinfo($filePath,PATHINFO_EXTENSION));
        if (!file_exists($filePath)){
            throw new \Exception('数据文件不存在.');
        }
        $phpExcelReader = null;
        switch ($ext){
            case 'xls':
                $phpExcelReader = new PHPExcel_Reader_Excel5();
                break;
            case 'xlsx':
                $phpExcelReader = new PHPExcel_Reader_Excel2007();
                break;
            default:
                throw new \Exception("不支持的数据类型.");
        }

        $this->excel = $phpExcelReader->load($filePath);

    }

    /**
     * 要求子类加载数据
     * @return void
     * @throws \Exception
     */
    protected function loadData()
    {
        if ($this->sheetName){
            $this->data = $this->excel->getSheetByName($this->sheetName)->toArray();
        }else{
            $this->data =  $this->excel->getSheet()->toArray();
        }
    }


}