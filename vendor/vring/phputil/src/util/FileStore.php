<?php
/**
 * Created by 育人爬虫.
 * User: vring
 * Date: 2021/2/14 0014
 * Time: 15:44
 */

namespace vring\util;

class FileStore
{

    /**
     * 文件大小转换 b => m
     *
     * @param $to_unit_str
     * @param string $from_unit 45MB
     * @return bool|float|int
     */
    static public function sizeUnitTran($to_unit_str,$from_unit = 'B'){
//    abs()
//    echo pow(2,2);

        preg_match('/(\d+(?:\.\d+)*)(\w{1})/',$to_unit_str,$mat);
        if(!$mat)
            return false;
        $size = $mat[1];
        $unit = strtoupper($mat[2] ? : 'B');
        $unit_arr = array('B','K','M','G');
        $unit_key = array_search($unit,$unit_arr);
        $from_unit_key = array_search(strtoupper($from_unit),$unit_arr);
        $pow = pow(1024,abs($unit_key-$from_unit_key));
        if ($unit_key === $from_unit_key){
            return $size;
        }
        if($unit_key > $from_unit_key){
            $res = $size * $pow;
        }else{
            $res = $size / $pow;
        }
        return $res;
    }

    static public function toUnit($byte, $str_format = '%s%s')
    {
        $byte_unit = [
            'B', 'KB', 'MB', 'GB'
        ];
        $res = $byte;
        $unit = $byte_unit[0];
        $count = count($byte_unit);
        foreach ($byte_unit as $k => $v) {

            if ($res < 1024) {
                break;
            }
            if (($k + 1) == $count) {
                break;
            }
            $res = $res / 1024;
            $unit = $byte_unit[$k + 1];
        }
        $res = round($res, 2);
        return sprintf($str_format, (string)$res, $unit);
    }




    /**
     * 取得文件存储大小
     * @param string $path
     * @return string
     *
     *   // Recover all file sizes larger than > 4GB.
    // Works on php 32bits and 64bits and supports linux
    // Used the com_dotnet extension

     */
    static public function fileSize($path)
    {
        if (!file_exists($path))
            return false;

        $size = filesize($path);

        if (!($file = fopen($path, 'rb')))
            return false;

        if ($size >= 0)
        {//Check if it really is a small file (< 2 GB)
            if (fseek($file, 0, SEEK_END) === 0)
            {//It really is a small file
                fclose($file);
                return $size;
            }
        }

        //Quickly jump the first 2 GB with fseek. After that fseek is not working on 32 bit php (it uses int internally)
        $size = PHP_INT_MAX - 1;
        if (fseek($file, PHP_INT_MAX - 1) !== 0)
        {
            fclose($file);
            return false;
        }

        $length = 1024 * 1024;
        while (!feof($file))
        {//Read the file until end
            $read = fread($file, $length);
            $size = bcadd($size, $length);
        }
        $size = bcsub($size, $length);
        $size = bcadd($size, strlen($read));

        fclose($file);
        return $size;
    }


    /**
     * 随机生成临时文件
     * @param string $prefix
     * @return bool|string
     */
    static public function tempFile($prefix = ''){
        $file_name = tempnam(sys_get_temp_dir(),$prefix);
        register_shutdown_function(function () use($file_name){
            @unlink($file_name);
        });
        return $file_name;
    }
}