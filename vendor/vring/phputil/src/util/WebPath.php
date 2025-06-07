<?php
/**
 * Created by PhpStorm.
 * User: vring
 * Date: 2021/10/6
 * Time: 10:10
 */

namespace vring\util;


class WebPath
{
    /**
     * 网站目录替换分割符 始终 /
     * @param string $path
     * @return string
     */
    static public function webTransform($path)
    {
        return str_replace('\\', '/', $path);
    }

    /**
     * 磁盘路径转网站访问路径，需文件在网站根
     * @param string $real_path
     * @return mixed
     */
    static public function realToSitePath($real_path)
    {
        $web_root = self::webTransform($_SERVER['DOCUMENT_ROOT']);
//    var_dump($web_root);
        return str_replace($web_root, '', self::webTransform($real_path));
    }


    /**
     * 网站路径转物理路径
     * @param string $url 资源URL
     * @param null|string $currentDomain 域名包含https://
     * @return false|string
     */
    static public function webPathToPhysical($url,$currentDomain = null) {


        // 获取网站根目录
        $documentRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/');

        if (!$currentDomain){
            // 获取当前域名
            $currentDomain = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}";
        }
        // 去掉 URL 中的域名部分
        if (strpos($url, $currentDomain) === 0) {
            $relativePath = substr($url, strlen($currentDomain));
        } else {
            $relativePath = $url; // 如果 URL 不属于当前站点，返回 false
        }
        // 拼接服务器物理路径
        $physicalPath = $documentRoot . $relativePath;
        return $physicalPath;
    }

    /**
     * 取得网站根路径
     * @return string
     * */
    static public function getSiteRoot()
    {
//        $root = Path::transform(dirname(str_replace($_SERVER['PATH_INFO'],'',$_SERVER['PHP_SELF'])));
        $root = dirname(WebPath::realToSitePath($_SERVER['SCRIPT_FILENAME']));
//        var_dump($root);
        return $root == '/' ? '' : $root;
    }
}