<?php
/**
 * Created by yurensf.
 * User: Vnser
 * Date: 2020/8/10 0010
 * Time: 20:47
 */
namespace vring\util;
class FileCache
{
    static private $_instance;
    static public $cacheDir = __DIR__;
//    private $store_file = 'filechace_store/file.store';
    public $data = [];

    public function __construct()
    {
//        $dir = dirname($this->store_file);
        if (!file_exists(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0777, true);
        }
//        $this->store_file = self::$cacheDir . DIRECTORY_SEPARATOR . $this->store_file;
//        $this->loadData();
    }


    public static function instance()
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    private function getNameKeyToFName($name)
    {
        return self::$cacheDir . DIRECTORY_SEPARATOR . $name . '.fcache';
    }

    public function loadData($name)
    {
        $cFile = $this->getNameKeyToFName($name);
        if (file_exists($cFile)) {
            $this->data = json_decode((@file_get_contents($cFile)), true);
        }else{
            $this->data = null;
        }

    }

    public function keys(string $pirex = '.+'){
        $keyDir = new Dir(self::$cacheDir);
        foreach ($keyDir->read(Dir::READ_FILE) as $keyFile){
            $pathInfo = Path::info($keyFile);
            if (preg_match("/({$pirex})/",$pathInfo['filename'])){
                yield $pathInfo['filename'];
            }
        }
    }

    /**
     * @param $name
     * @param $val
     * @param int $exp_time
     */
    public function set($name, $val, $exp_time = 0)
    {
        $this->data['val'] = $val;
        $this->data['exp_time'] = (0 === $exp_time ? 0 : $exp_time + time());
//        echo "<pre>";
//        print_r($this->data);
        $this->saveData($name);
//        $this->data = [];
    }

    private function checkKeyExpTime($name)
    {
        $key = $this->data;
        if (!$key['exp_time']){
            return;
        }/*
        if (0 === $key['exp_time']) {
            return;
        }*/
        if ($key['exp_time'] <= time()) {
            $this->rm($name);
        }
    }

    public function get($name)
    {
        $this->loadData($name);
        $this->checkKeyExpTime($name);
        return $this->data['val'];
    }

    public function rm($name)
    {
        @unlink($this->getNameKeyToFName($name));
    }

    public function saveData($name)
    {
        file_put_contents($this->getNameKeyToFName($name), json_encode($this->data, JSON_UNESCAPED_UNICODE));
    }
}