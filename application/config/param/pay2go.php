<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['notify_url'] = $_SERVER['SERVER_NAME'] . '/payment/notify/pay2go/'; // 智付寶Notify小時代的URL
$config['return_url'] = $_SERVER['SERVER_NAME'] . '/payment/back/pay2go/'; // 智付寶Return小時代的URL

// URL
if (ENVIRONMENT == 'production' || ENVIRONMENT == 'preview') {
    $config['credit_card_url']      = 'https://web.pay2go.com/API/CreditCard'; // 幕後授權信用卡
    $config['credit_cancel_url']    = 'https://web.pay2go.com/API/CreditCard/Cancel'; // 取消授權
    $config['credit_request_url']   = 'https://web.pay2go.com/API/CreditCard/Close'; // 信用卡請款
    $config['atm_url']              = 'https://web.pay2go.com/API/gateway/vacc'; // ATM
    $config['mpg_url']              = 'https://api.pay2go.com/MPG/mpg_gateway'; // MPG
    $config['query_trade_info_url'] = 'https://api.pay2go.com/API/QueryTradeInfo';
    $config['notify_url']           = 'https://' . $config['notify_url'];
    $config['return_url']           = 'https://' . $config['return_url'];
} else {
    $config['credit_card_url']      = 'https://cweb.pay2go.com/API/CreditCard'; // 幕後授權信用卡
    $config['credit_cancel_url']    = 'https://cweb.pay2go.com/API/CreditCard/Cancel'; // 取消授權
    $config['credit_request_url']   = 'https://cweb.pay2go.com/API/CreditCard/Close'; // 信用卡請款
    $config['atm_url']              = 'https://cweb.pay2go.com/API/gateway/vacc'; // ATM
    $config['mpg_url']              = 'https://capi.pay2go.com/MPG/mpg_gateway'; // MPG
    $config['query_trade_info_url'] = 'https://capi.pay2go.com/API/QueryTradeInfo';
    $config['notify_url']           = 'http://' . $config['notify_url'];
    $config['return_url']           = 'http://' . $config['return_url'];
}

// 單號類別
$config['index_type'] = array(
    '1' => '商店訂單編號',
    '2' => '智付寶交易序號',
);

/**
 * 幕後授權信用卡
 */
// 收單銀行
$config['auth_bank'] = array(
    'NCCC'    => '聯信',
    'CTBC'    => '中國信託',
    'KGI'     => '凱基',
    'Taishin' => '台新',
    'Fubon'   => '富邦',
    'ESUN'    => '玉山',
);

/**
 * ATM
 */
// ATM銀行
$config['atm_bank_type'] = array(
    'Mega'     => '兆豐銀行',
    'BOT'      => '台灣銀行',
    'Esun'     => '玉山銀行',
    'LandBank' => '土地銀行',
    'HNCB'     => '華南銀行',
    'Taishin'  => '台新銀行',
    'CHB'      => '彰化銀行',
);

/**
 * MPG
 */
// MPG允許的付款方式
$config['mpg_allow_payment_type'] = array(
    'CREDIT',
    'UNIONPAY',
    'WEBATM',
    'VACC',
    'CVS',
    'BARCODE',
);
