<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('curlPost')) {
    function curlPost($url, $PostData, $ssl = false)
    {
        // 建立CURL連線
        $ch = curl_init();

        // 設定擷取的URL網址
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);

        if ($ssl) {
            //turning off the server and peer verification(TrustManager Concept).
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
            // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        }

        //將curl_exec()獲取的訊息以文件流的形式返回，而不是直接輸出。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //設定CURLOPT_POST 為 1或true，表示要用POST方式傳遞
        curl_setopt($ch, CURLOPT_POST, 1);
        //CURLOPT_POSTFIELDS 後面則是要傳接的POST資料。
        curl_setopt($ch, CURLOPT_POSTFIELDS, $PostData);
        // set time out
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1); // CURLOPT_NOSIGNAL 設為 1
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 75000); // 設定最長執行 900 毫秒

        // 執行
        $temp = curl_exec($ch);

        // 關閉CURL連線
        curl_close($ch);

        return $temp;
    }
}

if (!function_exists('_curl')) {
    function _curl($method = 'POST', $post_url = NULL, $nvpStr = NULL, $header = NULL) {
        $ch = curl_init();

        if($header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }


        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        //turning off the server and peer verification(TrustManager Concept).
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpStr);
                break;
            case 'GET':
                $post_url .= "?{$nvpStr}";
                break;
        }


        curl_setopt($ch, CURLOPT_URL, $post_url);

        //getting response from server
        $http_info = array();
        $http_info['body'] = curl_exec($ch);
        $http_info['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $http_info['err_no'] = curl_errno ($ch);
        $http_info['err_desc'] = curl_error($ch);
        $http_info['detail'] = curl_getinfo($ch);
        curl_close($ch);

        // if ($this->http_info['err_no'] || $this->http_info['http_code'] != '200') {
        //     return $http_info;
        // } else {
        //     return TRUE;
        // }
        return $http_info;
    }
}

if (!function_exists('_param2array')) {
    function _param2array($str = NULL) {
        $return = array();
        if (trim($str) != '') {
            $ary = explode("\n", trim($str));
            foreach($ary as $p) {
                if (preg_match('/(.*)=(.*)$/', $p)) {
                    list($k, $v) = explode('=', $p);
                    $return[$k] = trim($v);
                }
            }
        }

        return $return;
    }
}
