<?php
/**
 * Created by PhpStorm.
 * User: vring
 * Date: 2021/11/23
 * Time: 10:25
 */

namespace vring\excel;


use vring\util\FileResponse;
use vring\util\FileStore;


class CsvBuild
{
    /**
     * @var array
     */
    private $data;
    /**
     * @var array
     */
    private $map;

    /**
     * CsvBuild constructor.
     * @param array $data
     * @param array $map
     */
    public function __construct(array $data, array $map)
    {

        $this->data = $data;
        $this->map = $map;
    }

    private function build()
    {
        $res = [];
        $header = [];
        foreach ($this->data as $k => $v) {
            $csv = [];
            foreach ($this->map as $mk => $mv) {
                foreach ($v as $key => $item) {
                    if (preg_match("/{$mk}/", $key, $match)) {
                        if (empty($header[$k])) {
//                        print_r($key);
                            if (is_callable($mv[0])) {
                                $header[$key] = $mv[0]($item, $match);
                            } else {
                                $header[$key] = $mv[0];
                            }

                        }
                        $call = $mv[1];
                        if (is_callable($call)) {
//                                $csv[] = $call($item);
                            $csv[] = $call($item, $match);
                        } else {
                            $csv[] = $item;
                        }
                    }

                }

            }

            $res[$k] = '"' . join('","', $csv) . '"';
        }
//        print_r($header);
        array_unshift($res, '"' . join('","', $header) . '"');
        return join("\r\n", $res);
    }

    public function render()
    {
        echo $this->build();
    }

    public function rep($filename)
    {
        $tmpfile = FileStore::tempFile();
        $this->save($tmpfile);
        $response = new FileResponse($tmpfile, $filename);
        $response->responseFileBinary();
    }

    public function save($filePath)
    {
        return (bool)@file_put_contents($filePath, $this->build());
    }
}