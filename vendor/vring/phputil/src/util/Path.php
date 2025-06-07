<?php
/**
 * Created by 育人爬虫.
 * User: Vnser
 * Date: 2021/2/14 0014
 * Time: 15:46
 */

namespace vring\util;


class Path
{
    /**
     * 目录替换分割符
     * @param string $path
     * @return string
     */
    static public function transform($path)
    {
        $search = (PHP_OS === 'WINNT') ? '/':'\\';

        return str_replace($search, DIRECTORY_SEPARATOR, $path);
    }


    /**
     * 解析数组替换{root}
     * @param array $arr
     * @return array
     */
    static public function replaces(array $arr)
    {
        foreach ($arr as $k =>$v){
            $arr[$k] =  preg_replace_callback('/\{(\w+?)\}/',function ($mat) use ($arr){
                return $arr[$mat[1]];
            },$v);
        }
        return $arr;
    }

    /**
     * 重写pathinfo
     * @param $path
     * @return [
     *   'dirname'=>$pathmat['path'],
    'basename'=>$pathmat['basename'],
    'extension'=>$pathmat['ext'],
    'filename'=>$pathmat['filename'],]
     */
    static public function info($path){
        if (preg_match('/^(?:(?<path>.+)[\\\\\\/])?(?<basename>(?<filename>[^\\\\\\/]+?)(?:\.(?<ext>\w+))?)$/',$path,$pathmat)){
            return [
                'dirname'=>$pathmat['path'],
                'basename'=>$pathmat['basename'],
                'extension'=>$pathmat['ext'],
                'filename'=>$pathmat['filename'],
            ];
        }

    }
}