<?php
/**
 * Author: vring
 * Date: 2024/4/19
 * Email: <971626354@qq.com>
 */

namespace vring\validate;
use vring\http\Curl;

class AliBankCard
{
    const VERIFY_HOST = 'https://ccdcapi.alipay.com/validateAndCacheCardInfo.json?_input_charset=utf-8&cardNo=%d&cardBinCheck=true';

    static protected $bankMap = [];
    public function __construct()
    {
        if (!self::$bankMap){
            self::$bankMap = json_decode(file_get_contents(__DIR__.'/alibacktype.json'),true);
        }
    }

    /**
     * 获取指定银行卡号信息
     * @param $cardBankNo
     * @return array|mixed 银卡卡号
     * @return array
     */
    public function getBinkType($cardBankNo)
    {
        $data = Curl::main(sprintf(self::VERIFY_HOST,$cardBankNo))->get();
        $data = json_decode($data,true);
        if ($data['validated']){
            $data['bank_text'] = self::$bankMap[$data['bank']];
            return $data;
        }
        return [];
    }

    /**
     * 验证指定卡号的正确性
     * @param $cardBankNo
     * @return bool
     */
    public function verify($cardBankNo)
    {
        return (bool)$this->getBinkType($cardBankNo);
    }
}