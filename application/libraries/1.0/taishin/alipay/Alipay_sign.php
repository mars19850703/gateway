<?php

class Alipay_sign
{
    public function __construct()
    {

    }

    public function getSign($cashConfig, $inputData)
    {
        ksort($inputData);
        $str  = urldecode(http_build_query($inputData)) . $cashConfig->Token;
        $sign = hash('sha256', $str);

        return $sign;
    }
}
