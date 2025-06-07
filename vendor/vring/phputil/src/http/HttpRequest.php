<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022/10/6 0006
 * Time: 15:41
 */

namespace vring\http;

use vring\util\Path;

class HttpRequest
{
    #请求地址
    private $url;
    #请求数据
    private $data;
    #sock连接
    private $sock;
    #最后响应的请求头
    private $lastResponseHeader;
    #最后响应数据
    private $lastResponseData;
    #最后响应体
    private $response;
    #最后请求头
    private $lastRequestHeader;
    #请求头
    private $requestHeader = [];
    #数据编码
    private $dataEncoding = '';
    private $host;
    private $port = 80;



    public function __construct(string $url, array $data = [])
    {

        $this->url = $url;
        $this->data = $data;
        $this->parseRequest();
    }

    /**
     * @param string $host
     * @param int $port
     */
    public function setRequestServer(string $host, int $port = 80){
        $this->host = $host;
        $this->port = $port;
    }

    protected function parseRequest()
    {
        $reqInfo = parse_url($this->url);
//        print_r($reqInfo);
        $this->host = $reqInfo['host'];
        $this->port = $reqInfo['port'];
//        $this->path = $reqInfo['path'];
//        print_r($reqInfo);
    }

    public function get()
    {
        return $this->send();
    }

    public function post()
    {
        return $this->send('POST');
    }

    public function formData(array $files = [])
    {
//        $host = self::JJY_HOST;
//        $sessionId = self::$sessionId;

        $boundary = '----WebKitFormBoundary0ZDrnLXKdzTRM4Hj';
        $postDataByte = "";
        foreach ($this->data as $key => $item) {
            if ($this->dataEncoding and $this->dataEncoding !== 'utf-8'){
                $key = mb_convert_encoding($key,$this->dataEncoding,'utf-8');
                $item = mb_convert_encoding($item,$this->dataEncoding,'utf-8');
            }
            $postDataByte .= "--{$boundary}\r\nContent-Disposition: form-data; name=\"{$key}\"\r\n\r\n{$item}\r\n";
        }

        foreach ($files as $file) {
            $basename = Path::info($file[0]);
            $mimeType = mime_content_type($file[0]);
            $fileContent = file_get_contents($file[0]);
            $postDataByte .= "--{$boundary}\r\nContent-Disposition: form-data; name=\"{$file[1]}\"; filename=\"{$basename['basename']}\"\r\nContent-Type: {$mimeType}\r\n\r\n{$fileContent}\r\n";
        }

        $postDataByte .= "--{$boundary}--\r\n";


        $length = strlen($postDataByte);
        $bheader = $this->buildSetHeaders();
        $header = "POST {$this->url} HTTP/1.0
Host:{$this->host}
Accept:application/json, text/javascript, */*; q=0.01
Content-Type: multipart/form-data; boundary={$boundary}
User-Agent:Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.101 Safari/537.36
X-Requested-With:XMLHttpRequest
Connection:close
{$bheader}Content-length: {$length}

{$postDataByte}";
        $header .= "\r\n";
        return $this->send('POST', $header);
    }

    protected function send(string $method = 'GET', string $header = "")
    {
        $this->openSock();
        $requestHeader = $header;
        if (empty($header)) {
            $requestHeader = $this->buildHeader($method);
        }
        $this->lastRequestHeader = $requestHeader;
//        echo $requestHeader;
        fwrite($this->sock, $requestHeader);
        $response = "";
        while (!feof($this->sock)) {
            $response .= fread($this->sock, 1024);
        }
        if ($this->dataEncoding and $this->dataEncoding !== 'utf-8'){
            $response = mb_convert_encoding($response,'utf-8',$this->dataEncoding);
        }
        $this->response = $response;
        [$this->lastResponseHeader, $this->lastResponseData] = explode("\r\n\r\n", $response, 2);
        return $this->lastResponseData;
    }


    protected function buildHeader(string $method = 'GET'): string
    {
//        $host = self::JJY_HOST;
//        $sessionId = self::$sessionId;
        $bheader = $this->buildSetHeaders();
        $header = "{$method} {$this->url} HTTP/1.0
Host:{$this->host}
Accept:application/json, text/javascript, */*; q=0.01
Connection:close
Content-Type:application/x-www-form-urlencoded; charset=UTF-8
User-Agent:Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.101 Safari/537.36\r\n{$bheader}";
        if ('POST' === strtoupper($method)) {
            $postDataStr = http_build_query($this->data);
            if ($this->dataEncoding and $this->dataEncoding !== 'utf-8'){
                $postDataStr = mb_convert_encoding($postDataStr,$this->dataEncoding,'utf-8');
            }
            $dataLength = strlen($postDataStr);
            $header .= "Content-Length:{$dataLength}"
                . "\r\n\r\n{$postDataStr}";
        }
        $header .= "\r\n";
        return $header;
    }

    /**
     * 生成请求头
     * @return string
     * */
    protected function buildSetHeaders(): string
    {
        $header = [];
        foreach ($this->requestHeader as $k => $v) {
            $header [$k] = $k . ": " . $v;
        }
        return $header ? join("\r\n", $header) . "\r\n" : "";
    }

    protected function openSock()
    {
//        print_r($this);
        $this->sock = @fsockopen($this->host, $this->port ?: 80);
//        $this->sock = @fsockopen('127.0.0.1', self::JJY_PORT);
        if (false === $this->sock) {
            throw  new \Exception("请求异常，检查网络({$this->url})");
        }
    }


    /**
     * 添加请求头
     * @param string $name
     * @param string $value
     */
    public function addRequestHeader(string $name, string $value)
    {
        $this->requestHeader[$name] = $value;
    }

    /**
     * 析构方法
     * */
    public function __destruct()
    {
        //关闭sock通道
        $this->sock && fclose($this->sock);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @param string $name
     * @param string $item
     */
    public function appendData(string $name, string $item): void
    {
        $this->data[$name] = $item;
    }

    /**
     * @return mixed
     */
    public function getLastResponseHeader()
    {
        return $this->lastResponseHeader;
    }

    /**
     * @return mixed
     */
    public function getLastResponseData()
    {
        return $this->lastResponseData;
    }

    /**
     * @param string $dataEncoding
     */
    public function setDataEncoding(string $dataEncoding): void
    {
        $this->dataEncoding = $dataEncoding;
    }

    /**
     * @return mixed
     */
    public function getLastRequestHeader()
    {
        return $this->lastRequestHeader;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }
}