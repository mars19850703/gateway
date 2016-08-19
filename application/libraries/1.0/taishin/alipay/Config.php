<?php

$alipay = array();

// URL
if (ENVIRONMENT == 'production') {
    $alipay['paymentUrl'] = 'https://tscboweb.taishinbank.com.tw/TSCBOgwAPI/gwMerchantApiPay.ashx';
    $alipay['refundUrl']  = 'https://tscboweb.taishinbank.com.tw/TSCBOgwAPI/gwMerchantApiRefund.ashx';
    $alipay['queryUrl']   = 'https://tscboweb.taishinbank.com.tw/TSCBOgwAPI/gwMerchantApiQuery.ashx';
} else {
    $alipay['paymentUrl'] = 'https://tscbweb-t.taishinbank.com.tw/TSCBOgwAPI/gwMerchantApiPay.ashx';
    $alipay['refundUrl']  = 'https://tscbweb-t.taishinbank.com.tw/TSCBOgwAPI/gwMerchantApiRefund.ashx';
    $alipay['queryUrl']   = 'https://tscbweb-t.taishinbank.com.tw/TSCBOgwAPI/gwMerchantApiQuery.ashx';
}
