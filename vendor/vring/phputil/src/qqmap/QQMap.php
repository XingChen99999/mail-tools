<?php

namespace vring\qqmap;



class QQMap
{
    static public $key = '';

    /**
     * 腾讯地图接口,逆地址查询,根据中文地址解析解析出
     * @return void
     * @throws \Exception
     */
    static public function qeocoderByAddress($address)
    {
        return self::qeocoder(['address'=>$address]);
    }


    /**
     * 根据坐标查询地址
     * @param string $lat
     * @param string $lng
     * @return mixed
     * @throws \Exception
     */
    static public function qeocoderByLocation($lat,$lng)
    {
        return self::qeocoder(['location'=>"{$lat},{$lng}"]);
    }


    /**
     * 腾讯地图接口,逆地址查询,根据中文地址解析解析出
     * @param $param
     * @return mixed
     * @throws \Exception
     */
    static private function qeocoder($param)
    {
        $key = self::$key;
//        $location = $this->request->request('location');
        $paramStr = http_build_query(array_merge($param,['key'=>$key]));
        $geocoder = file_get_contents("https://apis.map.qq.com/ws/geocoder/v1/?{$paramStr}");
        $res = json_decode($geocoder,true);
        if ($res['status'] != 0){
            throw new \Exception($res['message']);
        }
        return $res;
    }

}