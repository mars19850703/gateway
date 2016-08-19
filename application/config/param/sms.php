<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 *    簡訊商
 */
$config["sms_company"] = array(
    "mitake" => "三竹資訊",
);

/**
 *    三竹
 */
// 帳密
// $config['mitake_username'] = '54696364';
// $config['mitake_password'] = 'd54696364w';
$config['mitake_username'] = '43864429';
$config['mitake_password'] = 'stpath!^*';

// ResponseUrl
$config['mitake_response_url'] = $_SERVER['SERVER_NAME'] . '/sms/notify/mitake/';
if (ENVIRONMENT == 'production') {
    $config['mitake_response_url'] = 'https://' . $config['mitake_response_url'];
} else {
    $config['mitake_response_url'] = 'http://' . $config['mitake_response_url'];
}

// 單封簡訊
$config['mitake_single_sms_url'] = 'http://smexpress.mitake.com.tw:9600/SmSendGet.asp';

// 多封簡訊
$config['mitake_multi_sms_url'] = 'http://smexpress.mitake.com.tw:9600/SmSendPost.asp';

// 長簡訊
$config['mitake_long_sms_url'] = 'http://smexpress.mitake.com.tw:7002/SpLmGet';

/*
 * 內文編碼選項
 * 其中Unicode即為utf-16le
 * 注意事項1：雖然三竹支援客戶指定編碼方式，但這是用於將資料轉成Big5的格式。若送來日文、韓文、簡體中文..等的unicode，保證送出去一定不是所要的結果。
 * 注意事項2：編碼的轉換是透過Window的CodePage做轉換。不保證每一個看起來像繁體中文的Unicode都能完美的對應到Big5的內碼，請測試OK後再上線，轉換結果若有缺陷，只能敬請見諒。
 */
$config['mitake_encoding'] = array('big5', 'unicode', 'utf-16le', 'utf-16be', 'utf8');

// StatusFlag
$config['mitake_status_flag'] = array(
    '0' => '預約傳送中',
    '1' => '已送達業者',
    '2' => '已送達業者',
    '3' => '已送達業者',
    '4' => '已送達手機',
    '5' => '內容有錯誤',
    '6' => '門號有錯誤',
    '7' => '簡訊已停用',
    '8' => '逾時無送達',
    '9' => '預約已取消',
);

// StatusCode
$config['mitake_status_code'] = array(
    '*' => '系統發生錯誤，請聯絡三竹資訊窗口人員',
    'a' => '簡訊發送功能暫時停止服務，請稍候再試',
    'b' => '簡訊發送功能暫時停止服務，請稍候再試',
    'c' => '請輸入帳號',
    'd' => '請輸入密碼',
    'e' => '帳號、密碼錯誤',
    'f' => '帳號已過期',
    'h' => '帳號已被停用',
    'k' => '無效的連線位址',
    'm' => '必須變更密碼，在變更密碼前，無法使用簡訊發送服務',
    'n' => '密碼已逾期，在變更密碼前，將無法使用簡訊發送服務',
    'p' => '沒有權限使用外部Http程式',
    'r' => '系統暫停服務，請稍後再試',
    's' => '帳務處理失敗，無法發送簡訊',
    't' => '簡訊已過期',
    'u' => '簡訊內容不得為空白',
    'v' => '無效的手機號碼',
    '0' => '預約傳送中',
    '1' => '已送達業者',
    '2' => '已送達業者',
    '3' => '已送達業者',
    '4' => '已送達手機',
    '5' => '內容有錯誤',
    '6' => '門號有錯誤',
    '7' => '簡訊已停用',
    '8' => '逾時無送達',
    '9' => '預約已取消',
);
