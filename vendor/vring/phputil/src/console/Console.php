<?php
/**
 * Created by PhpStorm.
 * User: vring
 * Date: 2021/10/6
 * Time: 15:39
 */

namespace vring\console;
abstract class Console
{


    static private $instance;
    static public function instance()
    {
        if (!isset(self::$instance)){
            switch (PHP_OS){
                case 'WINNT':
                    self::$instance = new Windows;
                    break;

                default :
                    self::$instance = new Linux;
            }
        }
        return self::$instance;
    }

    /**
     * @param $message
     * @return mixed
     */
    static public function input($message)
    {
        return self::instance()->prompt($message);
    }

    abstract public function prompt($content);
}