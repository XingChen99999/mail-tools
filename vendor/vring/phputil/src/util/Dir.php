<?php
/**
 * Created by PhpStorm.
 * User: vring
 * Date: 2021/10/6
 * Time: 9:01
 */

namespace vring\util;


class Dir
{
    const READ_ALL = 0;
    const READ_FILE = 1;
    const READ_DIR = 2;

    private $dirPath = '';

    public function __construct(string $dirPath)
    {

        $this->dirPath = rtrim($dirPath,'/\\');

    }

    /**
     * 读取所有文件 返回数组
     * @param int $filtrate
     * @param bool $recursion
     * @return array
     * @throws \Exception
     */
    public function readAll($filtrate = self::READ_ALL, $recursion = false)
    {
        $result = [];
        foreach ($this->read($filtrate, $recursion) as $item) {
            $result[] = $item;
        }
        return $result;
    }

    /**
     * @param int $filtrate
     * @param bool $recursion
     * @return \Generator
     * @throws \Exception
     */
    public function read($filtrate = self::READ_ALL, $recursion = false)
    {
        return $this->_read($this->dirPath, $filtrate, $recursion);
    }

    /**
     * @param $dir
     * @param int $filtrate
     * @param bool $recursion
     * @return \Generator
     * @throws \Exception
     */
    private function _read($dir, $filtrate = self::READ_ALL, $recursion = false)
    {
        $openDir = @opendir($dir);
        if (!$openDir){
            throw new \Exception("“{$dir}”目录不存在!");
        }
        while ($file = readdir($openDir)) {
            $filePath = Path::transform($dir .DIRECTORY_SEPARATOR. $file);

            if ($file === '.' or $file === '..') {
                continue;
            }
//            var_dump($dir .DIRECTORY_SEPARATOR. $file);
            if ($recursion and is_dir($filePath)) {
                foreach ($this->_read($filePath , $filtrate, $recursion) as $item) {
                    yield $item;
                }
//                continue;
            }
            switch ($filtrate) {
                case self::READ_DIR:
                    if (is_dir($filePath)) {
                        yield $filePath;
                    }
                    break;
                case self::READ_FILE:
                    if (is_file($filePath)) {
                        yield $filePath;
                    }
                    break;
                default:
                    yield $filePath;
            }


        }
        closedir($openDir);
    }

    /**
     * @param string $dir
     * @throws \Exception
     */
    static public function remove(string $dir){
        $self = new self($dir);
        foreach ($self->read(self::READ_ALL,true) as $file){
            is_dir($file)? rmdir($file):unlink($file);
        }
        rmdir($dir);
    }

    /**
     * @param string $prefix
     * @return string
     */
    static public function getTempDir(string $prefix = ''){
        $tempDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid($prefix);
        @mkdir($tempDir);
        register_shutdown_function(function () use ($tempDir){
            self::remove($tempDir);
        });
        return $tempDir;
    }
}