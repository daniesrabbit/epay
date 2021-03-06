<?php

namespace app\pay\model;

class QQPayModel
{
    private $qqPayConfig;

    public function __construct(array $qqPayConfig)
    {
        $this->qqPayConfig = $qqPayConfig;
    }

    /**
     * 构建签名
     * @param array $param
     * @return string
     */
    public function signParam(array $param)
    {
        ksort($param);
        $stringA = '';
        foreach ($param as $key => $value) {
            if ($value != '' && $key != 'sign')
                $stringA .= $key . '=' . $value . '&';
        }
        //排序并组合字符串
        $stringA .= 'key=' . $this->qqPayConfig['mchkey'];
        $stringA = strtoupper(md5($stringA));
        return $stringA;
    }

    public function selectPayRecord($tradeNo)
    {
        $param = [
            'mch_id'       => $this->qqPayConfig['mchid'],
            'nonce_str'    => getRandChar(32),
            'out_trade_no' => $tradeNo
        ];
        $param['sign']      = $this->signParam($param);
        $xml                = arrayToXml($param);
        //build xml
        $result = curl('https://qpay.qq.com/cgi-bin/pay/qpay_order_query.cgi', [], 'post', $xml, 'xml');
        return xmlToArray($result);
    }

    /**
     * 获取付款二维码
     * @param array $param
     * @return array|mixed|object
     */
    public function sendPayRequest(array $param)
    {
        $param['mch_id']    = $this->qqPayConfig['mchid'];
        $param['nonce_str'] = getRandChar(32);
        $param['sign']      = $this->signParam($param);
        $xml                = arrayToXml($param);
        //build xml
        $result = curl('https://qpay.qq.com/cgi-bin/pay/qpay_unified_order.cgi', [], 'post', $xml, 'xml');
        return xmlToArray($result);
    }
}