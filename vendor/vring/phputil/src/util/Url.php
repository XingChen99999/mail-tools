<?php
/**
 * Created by 库存管理.
 * User: Vnser
 * Date: 2020/11/27 0027
 * Time: 9:52
 */

namespace vring\util;


class Url
{
    protected static $_instance;
//    private $request;
    protected $webRoot;
    protected $entranceUrl;
    protected $isUrlEntrance = false;

    /**
     * @return self
     * */
    public static function instance()
    {
        if (!isset(static::$_instance)) {
            static::$_instance = new static;
        }
        return static::$_instance;
    }

    public function __construct()
    {
        $this->buildWebPath();
    }

    protected function buildWebPath()
    {
        $this->entranceUrl = WebPath::realToSitePath($_SERVER['SCRIPT_FILENAME']);
        $this->webRoot = WebPath::webTransform(dirname($this->entranceUrl));
        $this->webRoot = $this->webRoot === '/' ? '' : $this->webRoot;
    }


    /**
     * @param array $param
     * @param bool $marge_get_param
     * @param string $pathInfo
     * @param bool $domain
     * @return string
     */
    public function url($param = [],  $marge_get_param = true,$pathInfo = '',$domain = false)
    {
        $currentURL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        if (!empty($param) and is_string($param)) {
            parse_str($param, $param);
        }

        if (($marge_get_param)) {
            $param = array_merge($_GET, $param);
        }
//        print_r($_SERVER);
        if (!$pathInfo){
            $pathInfo = $_SERVER['PATH_INFO'];
        }
        $appUrl = $this->isUrlEntrance ? $this->entranceUrl .  $pathInfo : ($this->webRoot  . $pathInfo);
        if ($param) {
            $appUrl .= '?' . http_build_query($param);
        }
        if ($domain){
//            var_dump($domain);
            $appUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://".$_SERVER["HTTP_HOST"].$appUrl;

        }

        return $appUrl;

    }

    /**
     * @return string
     */
    public function getWebRoot()
    {
        return $this->webRoot;
    }

    /**
     *
     * @param bool $isUrlEntrance
     */
    public function setIsUrlEntrance($isUrlEntrance)
    {
        $this->isUrlEntrance = $isUrlEntrance;
    }

    /**
     * @return bool
     */
    public function isUrlEntrance()
    {
        return $this->isUrlEntrance;
    }
}