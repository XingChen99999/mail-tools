<?php
/**
 * Created by 育人爬虫.
 * User: Vnser
 * Date: 2021/1/27 0027
 * Time: 16:27
 */
namespace vring\util;
abstract class ExplorerThread
{

    public static $phpCmdPath = '../php.exe.lnk';
    public static $autoLoadPhpFile = '';
    public static $tempDir = './temp';
    private $classSerial = '';
    private $threadHash = '';

    public function __construct()
    {
        if (!file_exists(self::$tempDir)){
            mkdir(self::$tempDir,0777,true);
        }
    }

    abstract public function run();


    public function start()
    {
//        echo $this->buildThreadCode();
        $this->setUpPhp($this->buildBatCode($this->buildThreadCode()));

    }

    private function setUpPhp($batPath)
    {
        $cmd = "explorer ". Path::transform($batPath);
//        echo $cmd."\r\n";
        system($cmd);
    }

    public function buildBatCode($phpPath)
    {
        $cmdCode = Path::transform(self::$phpCmdPath) . " " . Path::transform($phpPath);
        $batFile = self::$tempDir . '/' . $this->threadHash . '.bat';
        if (file_exists($batFile)) {
            return $batFile;
        }
        file_put_contents($batFile, $cmdCode);
        return $batFile;
    }

    private function buildThreadCode()
    {
        $this->classSerial = serialize($this);
        $this->threadHash = md5($this->classSerial);
        $theadFile = self::$tempDir . '/' . $this->threadHash . '.php';
        if (file_exists($theadFile)) {
            return $theadFile;
        }
        $threadPhpCode = '<?php ';
        $threadPhpCode .= "require_once '" . self::$autoLoadPhpFile . "';//\r\n";
        $threadPhpCode .= '$thread = unserialize(\'' . $this->classSerial . '\');' . "\r\n";
        $threadPhpCode .= '$thread->run();' . "\r\n";
        file_put_contents($theadFile, $threadPhpCode);
        return $theadFile;
    }

}