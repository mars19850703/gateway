<?php

$spgateway = array();

switch (ENVIRONMENT) {
    case 'production':
        $SERVER_NAME = 'https://gateway.wecanpay.com.tw';
        break;
    case 'preivew':
        $SERVER_NAME = 'https://gateway.pre.wecanpay.com.tw';
        break;
    case 'beta':
        $SERVER_NAME = 'http://gateway.beta.wecanpay.com.tw';
        break;
    case 'development':
        $SERVER_NAME = 'http://gateway.dev.wecanpay.com.tw';
        break;
    case 'localhost':
        $SERVER_NAME = 'http://gateway.wecanpay.localhost';
        break;
    default:
        $SERVER_NAME = 'http://gateway.wecanpay.com.tw';
        break;
}

$spgateway['notify_url'] = $SERVER_NAME . '/payment/notify/pay2go/'; // 智付寶Notify小時代的URL
$spgateway['return_url'] = $SERVER_NAME . '/payment/back/pay2go/'; // 智付寶Return小時代的URL

// URL
if (ENVIRONMENT == 'production') {
    $spgateway['credit_card_url']      = 'https://core.spgateway.com/API/CreditCard'; // 幕後授權信用卡
    $spgateway['credit_cancel_url']    = 'https://core.spgateway.com/API/CreditCard/Cancel'; // 取消授權
    $spgateway['credit_request_url']   = 'https://core.spgateway.com/API/CreditCard/Close'; // 信用卡請款
    $spgateway['atm_url']              = 'https://web.pay2go.com/API/gateway/vacc'; // ATM
    $spgateway['mpg_url']              = 'https://api.pay2go.com/MPG/mpg_gateway'; // MPG
    $spgateway['query_trade_info_url'] = 'https://core.spgateway.com/API/QueryTradeInfo';
    $spgateway['notify_url']           = $spgateway['notify_url'];
    $spgateway['return_url']           = $spgateway['return_url'];
    $spgateway['invoice_issue_url']    = 'https://inv.pay2go.com/API/invoice_issue';
    $spgateway['invoice_touch_url']    = 'https://inv.pay2go.com/API/invoice_touch_issue';
    $spgateway['invoice_invaild_url']  = 'https://inv.pay2go.com/API/invoice_invalid';
} else {
    $spgateway['credit_card_url']      = 'https://ccore.spgateway.com/API/CreditCard'; // 幕後授權信用卡
    $spgateway['credit_cancel_url']    = 'https://ccore.spgateway.com/API/CreditCard/Cancel'; // 取消授權
    $spgateway['credit_request_url']   = 'https://ccore.spgateway.com/API/CreditCard/Close'; // 信用卡請款
    $spgateway['atm_url']              = 'https://cweb.pay2go.com/API/gateway/vacc'; // ATM
    $spgateway['mpg_url']              = 'https://capi.pay2go.com/MPG/mpg_gateway'; // MPG
    $spgateway['query_trade_info_url'] = 'https://ccore.spgateway.com/API/QueryTradeInfo';
    $spgateway['notify_url']           = $spgateway['notify_url'];
    $spgateway['return_url']           = $spgateway['return_url'];
    $spgateway['invoice_issue_url']    = 'https://cinv.pay2go.com/API/invoice_issue';
    $spgateway['invoice_touch_url']    = 'https://cinv.pay2go.com/API/invoice_touch_issue';
    $spgateway['invoice_invaild_url']  = 'https://cinv.pay2go.com/API/invoice_invalid';
}

// 單號類別
$spgateway['index_type'] = array(
    '1' => '商店訂單編號',
    '2' => '智付寶交易序號',
);

/**
 * 幕後授權信用卡
 */
// 收單銀行
$spgateway['auth_bank'] = array(
    'Taishin' => '台新銀行',
    'Esun'    => '玉山銀行',
    'NCCC'    => '聯合信用卡中心',
    'CTBC'    => '中國信託商業銀行',
    'KGI'     => '凱基銀行',
    'Fubon'   => '台北富邦銀行',
);
