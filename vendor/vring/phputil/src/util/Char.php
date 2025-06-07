<?php
/**
 * Created by PhpStorm.
 * User: vring
 * Date: 2021/9/27
 * Time: 16:16
 */

namespace vring\util;


class Char
{

    /**
     * gbk转为utf8
     * @param string $content
     * @return string
     */
    static public function gbkToUtf8(string $content)
    {
        if (self::isUtf8($content)) {
            return $content;
        }
        return mb_convert_encoding($content, 'UTF-8', 'CP936');
    }

    /**
     * UTF8编码转为Gbk
     * @param string $content
     * @return string
     */
    static public function utf8ToGbk(string $content)
    {
        if (self::isGbk($content)) {
            return $content;
        }
        return mb_convert_encoding($content, 'CP936', 'UTF-8');
    }


    /**
     * 判断编码是否为Utf8,因为UTF-8是万维编码，所以检测不一定正确，检测范围：utf-8、gbk、ibm862之间的字符。
     * @param string $string
     * @return bool
     */
    static public function isUtf8(string $string): bool
    {

        $first = ('UTF-8' === self::findEncoding($string));
        if (TRUE === $first and (iconv('UTF-8', 'CP936', $string) OR iconv('UTF-8', 'IBM862', $string))) {
            #二次检测，此处采用黑科技
            return true;
        }
        return false;
    }

    /**
     * 检测编码是否是Gbk、utf-8
     * @param string $string
     * @return bool
     */
    static public function isGbk(string $string): bool
    {

        $first = ('CP936' === self::findEncoding($string));
        if (false === $first and ($cp936 = iconv('CP936', 'gb2312', $string)) and !iconv('utf-8','cp936',$string)) {
            #二次检测，此处采用黑科技
            return true;
        }

        return $first;
    }

    /**
     * 检测编码是否为编码
     * @param string $string
     * @param array $encodings
     * @return string
     */
    static public function findEncoding(string $string,array $encodings = ['UTF-8','CP936']): string
    {
        return mb_detect_encoding($string, $encodings, true);
    }

    /**
     * 兼容windows系统编码 @vring 该函数仅限于window系统与php之间编码相互转化，该函数缺乏一定兼容性
     * @param $string
     * @param bool $isUtf8
     * @return string
     */
    static public function compatEncoding($string, $isUtf8 = false)
    {
        if (version_compare(PHP_VERSION, '7.1.0', "<") AND PHP_OS == 'WINNT') {
            if ($isUtf8) {
                return self::utf8ToGbk($string);
            }
            return self::gbkToUtf8($string);
        }
        return $string;
    }
}