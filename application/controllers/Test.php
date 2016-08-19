<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Test extends MY_Controller
{
    protected $edcId;
    protected $edcMac;
    protected $terminalCode;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('cryptography/cryptography');
        if (ENVIRONMENT === 'production' || ENVIRONMENT === 'preview') {
            $this->edcId        = 'Q80414400035';
            $this->edcMac       = '00:0C:D6:40:72:51';
            $this->terminalCode = '0001';
        } else {
            $this->edcId        = 'V90413400522';
            $this->edcMac       = '00:19:94:30:10:C5';
            $this->terminalCode = '0002';
        }
    }

    public function index()
    {
        // if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        //     echo 'This is a server using Windows!';
        // } else {
        //     echo 'This is a server not using Windows!';
        // }
        // echo str_pad('1', 3, '0', STR_PAD_RIGHT);
        // echo strtoupper(sha1('S1607149075' . 'wecanpay4429' . 'T001001608010000487' . '100' . '00'));
        // phpinfo();

        // $x = 0;
        // $status = 'AUTH';
        // while ($x <= 10 && $status === 'AUTH') {
        //     echo $x . '<br/>';
        //     if ($x === 6) {
        //         $status = 'COMPLETE';
        //     }
        //     echo $status . '<br/>';
        //     $x++;
        // }

        die;
    }

    public function cardType()
    {
        $cardNo = '';
        $length = strlen($cardNo);
        $first  = substr($cardNo, 0, 1);

        if ($length === 15) {
            switch ($first) {
                case '1':
                    $card4No = substr($cardNo, 0, 4);
                    if ($card4No === '1800') {
                        $type = 'JCB CARD';
                    } else {
                        $type = 'UNDEFINED';
                    }
                    break;
                case '2':
                    $card4No = substr($cardNo, 0, 4);
                    if ($card4No === '2131') {
                        $type = 'JCB CARD';
                    } else {
                        $type = 'UNDEFINED';
                    }
                    break;
                case '3':
                    $card3No = intval(substr($cardNo, 0, 3));
                    if ($card3No >= 340 && $card3No <= 379) {
                        $type = 'AMEX CARD';
                    } else {
                        $type = 'UNDEFINED';
                    }
                    break;
                default:
                    $type = 'UNDEFINED';
                    break;
            }
        } else if ($length === 16) {
            switch ($first) {
                case '3':
                    $card3No = intval(substr($cardNo, 0, 3));
                    if ($card3No >= 300 && $card3No <= 399) {
                        $type = 'JCB CARD';
                    } else {
                        $type = 'UNDEFINED';
                    }
                    break;
                case '4':
                    $type = 'VISA CARD';
                    break;
                case '5':
                    $second = intval(substr($cardNo, 1, 1));
                    if ($second > 0 && $second < 6) {
                        $type = 'MASTER CARD';
                    } else {
                        $type = 'UNDEFINED';
                    }
                    break;
                default:
                    $type = 'UNDEFINED';
                    break;
            }
        }

        echo $type;
    }

    /**
     *  gateway 授權交易測試
     */
    public function group()
    {
        $this->gateway_output->setConstantsFile('payment');
        $factor = null;

        $context = stream_context_create(array('http' => array('header' => 'Connection: close\r\n')));
        if (ENVIRONMENT === 'localhost') {
            $url    = 'http://gateway.wecanpay.localhost/payment/group';
            $factor = json_decode(file_get_contents('http://key.wecanpay.localhost/key/generate', false, $context));
        } else if (ENVIRONMENT === 'development') {
            $url    = 'http://gateway.dev.wecanpay.com.tw/payment/group';
            $factor = json_decode(file_get_contents('http://key.dev.wecanpay.com.tw/key/generate', false, $context));
        } else if (ENVIRONMENT === 'beta') {
            $url    = 'http://gateway.beta.wecanpay.com.tw/payment/group';
            $factor = json_decode(file_get_contents('http://key.beta.wecanpay.com.tw/key/generate', false, $context));
        } else if (ENVIRONMENT === 'preview') {
            $url    = 'http://gateway.pre.wecanpay.com.tw/payment/group';
            $factor = json_decode(file_get_contents('http://key.pre.wecanpay.com.tw/key/generate', false, $context));
        } else {
            $url    = 'http://gateway.wecanpay.com.tw/payment/group';
            $factor = json_decode(file_get_contents('http://key.wecanpay.com.tw/key/generate', false, $context));
        }

        $OrderID = time();
        $post    = array(
            'ResType'    => 'json',
            // 'MerchantID' => '0012348',
            'MerchantID' => '5966706',
            'TerminalID' => $this->terminalCode,
            // 'TerminalID' => '0002',
            'Gateway'    => '1.0',
        );

        $data = array(
            // 'auth'      => array(
            //     // 'OrderID'     => '',
            //     'Currency'    => 'NTD',
            //     'Amount'      => '1',
            //     'CardNo'      => '4000221111111111',
            //     'CardExpire'  => '1909',
            //     'Cvv2'        => 'aaa',
            //     'ProDesc'     => '',
            //     'ServiceCode' => '001001001005',
            //     'EDCID'       => $this->edcId,
            //     'EDCMac'      => $this->edcMac,
            //     // 'EDCID'  => 'Q80414400034',
            //     // 'EDCMac' => '00:0C:D6:40:72:50',
            //     'AppName'     => 'WeCanPay_Payment',
            // ),
            // 'cancel' => array(
            //     'OrderID'     => '021469778034',
            //     'ServiceCode' => '001001001012',
            //     'EDCID'       => $this->edcId,
            //     'EDCMac'      => $this->edcMac,
            //     'AppName'     => 'WeCanPay_Payment',
            // ),
            // 'refund' => array(
            //     'OrderID'     => '021465957243',
            //     'Currency'    => 'NTD',
            //     'Amount'      => '10',
            //     'ServiceCode' => '001001001013',
            //     'EDCID'       => $this->edcId,
            //     'EDCMac'      => $this->edcMac,
            //     'AppName'     => 'WeCanPay_Payment',
            // ),
            // 'query'  => array(
            //     'OrderID'     => '021469778034',
            //     'ServiceCode' => '001001001014',
            //     'EDCID'       => $this->edcId,
            //     'EDCMac'      => $this->edcMac,
            //     'AppName'     => 'WeCanPay_Payment',
            // ),
            // 'issue'     => array(
            //     // 'OrderID'     => '021465970054',
            //     'BuyerName'   => 'Mars',
            //     // 'BuyerUBN'    => '43864429',
            //     'BuyerEmail'  => 'mars.lin.0703@gmail.com',
            //     'TaxType'     => '1',
            //     'TaxRate'     => '5',
            //     'Currency'    => 'NTD',
            //     'Amount'      => '100',
            //     'TaxAmount'   => '5',
            //     'TotalAmount' => '105',
            //     'ItemName'    => array(
            //         '商品一',
            //         '商品二',
            //     ),
            //     'ItemCount'   => array(
            //         '1',
            //         '1',
            //     ),
            //     'ItemUnit'    => array(
            //         '個',
            //         '張',
            //     ),
            //     'ItemPrice'   => array(
            //         '50',
            //         '50',
            //     ),
            //     'ItemAmount'  => array(
            //         '50',
            //         '50',
            //     ),
            //     'ServiceCode' => '003004004017',
            //     'EDCID'       => $this->edcId,
            //     'EDCMac'      => $this->edcMac,
            //     'AppName'     => 'WeCanPay_Payment',
            // ),
            // 'touch' => array(
            //     // 'OrderID'     => '021465970059',
            //     'ServiceCode' => '003004004018',
            //     'EDCID'       => $this->edcId,
            //     'EDCMac'      => $this->edcMac,
            //     'AppName'     => 'WeCanPay_Payment',
            // ),
            // 'invalid' => array(
            //     'InvoiceNumber' => 'CC00000031',
            //     'InvalidReason' => '測試測試',
            //     'ServiceCode'   => '003004004019',
            //     'EDCID'         => $this->edcId,
            //     'EDCMac'        => $this->edcMac,
            //     'AppName'       => 'WeCanPay_Payment',
            // ),
            // 'allowance' => array(
            //     'InvoiceNumber' => 'CC00000029',
            //     'ItemName'    => array(
            //         '商品一',
            //     ),
            //     'ItemCount'   => array(
            //         '1',
            //     ),
            //     'ItemUnit'    => array(
            //         '個',
            //     ),
            //     'ItemPrice'   => array(
            //         '50',
            //     ),
            //     'ItemAmount'  => array(
            //         '50',
            //     ),
            //     'ItemTaxAmount' =>array(
            //         '50'
            //     ),
            //     'TotalAmount' => '50',
            //     'ServiceCode'   => '003004004020',
            //     'EDCID'         => $this->edcId,
            //     'EDCMac'        => $this->edcMac,
            //     'AppName'       => 'WeCanPay_Payment',
            // ),
            // 'search' => array(
            //     'InvoiceNumber' => 'CC00000037',
            //     'ServiceCode'   => '003004004021',
            //     'EDCID'         => $this->edcId,
            //     'EDCMac'        => $this->edcMac,
            //     'AppName'       => 'WeCanPay_Payment',
            // ),
            // 'alipay_auth' => array(
            //     'Amount'      => '1',
            //     'BarCode'     => '289387266716459297',
            //     // 'OrderID'     => '55555',
            //     // 'OrderName'   => '商品Ａ',
            //     // 'OrderMemo'   => '測試測試',
            //     'ServiceCode' => '002002002001',
            //     'EDCID'       => $this->edcId,
            //     'EDCMac'      => $this->edcMac,
            //     'AppName'     => 'WeCanPay_Payment',
            // ),
            // 'alipay_refund' => array(
            //     'OrderID'     => '021470884437',
            //     'ServiceCode' => '002002002002',
            //     'EDCID'       => $this->edcId,
            //     'EDCMac'      => $this->edcMac,
            //     'AppName'     => 'WeCanPay_Payment',
            // ),
            // 'alipay_query' => array(
            //     // 'Type'        => '1',
            //     'OrderID'     => '021470710436',
            //     // 'ServiceCode' => '002002002003',
            //     'ServiceCode' => '002004005019',
            //     // 'EDCID'       => $this->edcId,
            //     // 'EDCMac'      => $this->edcMac,
            //     "EDCID"       => "V90413400522",
            //     "EDCMac"      => "00:19:94:30:10:C5",
            //     'AppName'     => 'WeCanPay_Payment',
            // ),
            // 'suntech_auth'      => array(
            //     'Currency'    => 'NTD',
            //     'Amount'      => '100',
            //     'CardNo'      => '4000221111111111',
            //     'CardExpire'  => '0919',
            //     'Cvv2'        => '000',
            //     'ProDesc'     => '',
            //     'ServiceCode' => '004005005022',
            //     'EDCID'       => $this->edcId,
            //     'EDCMac'      => $this->edcMac,
            //     'AppName'     => 'WeCanPay_Payment',
            // ),
            // 'line_auth' => array(
            //     'Currency'    => 'NTD',
            //     'Amount'      => '15',
            //     'BarCode'     => '711497307035',
            //     'ServiceCode' => '005006006023',
            //     'EDCID'       => $this->edcId,
            //     'EDCMac'      => $this->edcMac,
            //     'AppName'     => 'WeCanPay_Payment',
            // ),
            // 'line_confirm' => array(
            //     'OrderID'     => '021470903544',
            //     'ServiceCode' => '005006006024',
            //     'EDCID'       => $this->edcId,
            //     'EDCMac'      => $this->edcMac,
            //     'AppName'     => 'WeCanPay_Payment',
            // ),
            // 'line_refund' => array(
            //     'OrderID'     => '021470903544',
            //     'ServiceCode' => '005006006025',
            //     'EDCID'       => $this->edcId,
            //     'EDCMac'      => $this->edcMac,
            //     'AppName'     => 'WeCanPay_Payment',
            // ),
            'line_query' => array(
                'OrderID'     => '021471233612',
                // 'ServiceCode' => '005006006026',
                'ServiceCode' => '003005006022',
                'EDCID'       => $this->edcId,
                'EDCMac'      => $this->edcMac,
                'AppName'     => 'WeCanPay_Payment',
            ),
        );

        if (!is_null($factor)) {
            $post['_Data'] = $this->cryptography->encryption($factor->Key, $factor->Iv, $data, $factor);
            $post['KI']    = $factor->Index;
        } else {
            $post['_Data'] = $this->cryptography->encryption($key, $iv, $data);
        }

        $this->load->helper(array('common'));
        // $result = json_decode(curlPost($url, $post));
        $result = curlPost($url, $post);

        MY_Controller::dumpData($factor, $url, $data, $post, $result);
    }

    /**
     *  gateway 加解密測試
     */
    public function key()
    {
        $origin = '';
        $str    = '3a372f22bbe43331poiuj8966566ecee88885fcbpoiuj8968cc9f725ab1d17fapoiuj89640889d21eaf7bed9poiuj896215e58f7a848248dpoiuj896b5d7b6fc4fe109f8poiuj89609da6ace8fc2905dpoiuj896450f000d5467fd44poiuj896509da2c2437f0de1poiuj89681df98ebcb836062poiuj896ba715bcc39f75229poiuj8964666f25d890fb9e4poiuj89659d931f829227d74poiuj896e38bf7b979915ddfpoiuj896a0a137bd51e37fbdpoiuj89644d503196a08610dpoiuj8961726cff962a69773poiuj89630fc9b00c3e89cb1poiuj896a55316fd1cd15df6poiuj896da3c70d2291e4cb2poiuj896a3b8db9c4f8addebpoiuj896eb31e262b33aaf46poiuj896c9f5bafa15890739poiuj896046cf3ef9b52158epoiuj8962a9a99a80f89d7b1poiuj896736065b498e497e8poiuj896639612ce05a6d618poiuj8962f05d1165312e56epoiuj896d642202a9a59ee10poiuj89610f571d0ab845711poiuj8963ce372f4ef632a23poiuj987ae695b21b9941bb8';

        $factor = (object) array(
            'idx'  => '3275',
            'key'  => 'f3NNgauepWwG11RjTFf1ySdJ8qGFHZUW',
            'iv'   => '2HJiSexhb3Xc5PvZ',
            'Time' => date('Y-m-d H:i:s'),
        );

        $str_123 = $this->cryptography->decryption($factor->key, $factor->iv, $str, $factor);
        // $str_456 = $this->cryptography->decryption_old($factor->key, $factor->iv, $str, $factor);

        MY_Controller::dumpData($origin, $str, $str_123);
    }

    /**
     *  gateway 交班測試
     */
    public function handovers()
    {
        $this->gateway_output->setConstantsFile('payment');
        $factor = null;

        $context = stream_context_create(array('http' => array('header' => 'Connection: close\r\n')));
        if (ENVIRONMENT === 'localhost') {
            $url    = 'http://gateway.wecanpay.localhost/payment/handovers';
            $factor = json_decode(file_get_contents('http://key.wecanpay.localhost/key/generate', false, $context));
        } else if (ENVIRONMENT === 'development') {
            $url    = 'http://gateway.dev.wecanpay.com.tw/payment/handovers';
            $factor = json_decode(file_get_contents('http://key.dev.wecanpay.com.tw/key/generate', false, $context));
        } else if (ENVIRONMENT === 'beta') {
            $url    = 'http://gateway.beta.wecanpay.com.tw/payment/handovers';
            $factor = json_decode(file_get_contents('http://key.beta.wecanpay.com.tw/key/generate', false, $context));
        } else if (ENVIRONMENT === 'preview') {
            $url    = 'http://gateway.pre.wecanpay.com.tw/payment/handovers';
            $factor = json_decode(file_get_contents('http://key.pre.wecanpay.com.tw/key/generate', false, $context));
        } else if (ENVIRONMENT === 'production') {
            $url    = 'http://gateway.wecanpay.com.tw/payment/handovers';
            $factor = json_decode(file_get_contents('http://key.wecanpay.com.tw/key/generate', false, $context));
        }
        $key     = 'tZT07t9z5PxvMVN1YBJRtqzhaaZJo1pS';
        $iv      = '1jT8N2HSSfoFacH8';
        $OrderID = time();
        $post    = array(
            'ResType'    => 'json',
            'MerchantID' => '0012348',
            'TerminalID' => $this->terminalCode,
            'Gateway'    => '1.0',
        );

        $data = array(
            'ServiceCode' => '001001001015',
            'EDCID'       => $this->edcId,
            'EDCMac'      => $this->edcMac,
            'AppName'     => 'WeCanPay_Payment',
        );

        if (!is_null($factor)) {
            $post['_Data'] = $this->cryptography->encryption($factor->Key, $factor->Iv, $data, $factor);
            $post['KI']    = $factor->Index;
        } else {
            $post['_Data'] = $this->cryptography->encryption($key, $iv, $data);
        }

        $this->load->helper(array('common'));
        // $result = json_decode(curlPost($url, $post));
        $result = curlPost($url, $post);

        MY_Controller::dumpData($post, $result);
    }

    /**
     *  gateway EDC 存活測試
     */
    public function alive()
    {
        $this->gateway_output->setConstantsFile('payment');
        $factor = null;

        $context = stream_context_create(array('http' => array('header' => 'Connection: close\r\n')));
        if (ENVIRONMENT === 'localhost') {
            $url    = 'http://gateway.wecanpay.localhost/edc/alive';
            $factor = json_decode(file_get_contents('http://key.wecanpay.localhost/key/generate', false, $context));
        } else if (ENVIRONMENT === 'development') {
            $url    = 'http://gateway.dev.wecanpay.com.tw/edc/alive';
            $factor = json_decode(file_get_contents('http://key.dev.wecanpay.com.tw/key/generate', false, $context));
        } else if (ENVIRONMENT === 'beta') {
            $url    = 'http://gateway.beta.wecanpay.com.tw/edc/alive';
            $factor = json_decode(file_get_contents('http://key.beta.wecanpay.com.tw/key/generate', false, $context));
        } else if (ENVIRONMENT === 'preview') {
            $url    = 'http://gateway.pre.wecanpay.com.tw/edc/alive';
            $factor = json_decode(file_get_contents('http://key.pre.wecanpay.com.tw/key/generate', false, $context));
        } else if (ENVIRONMENT === 'production') {
            $url    = 'http://gateway.wecanpay.com.tw/edc/alive';
            $factor = json_decode(file_get_contents('http://key.wecanpay.com.tw/key/generate', false, $context));
        }
        $key     = 'tZT07t9z5PxvMVN1YBJRtqzhaaZJo1pS';
        $iv      = '1jT8N2HSSfoFacH8';
        $OrderID = time();
        $post    = array(
            'ResType'    => 'json',
            // 'MerchantID' => '0012348',
            'MerchantID' => '1849035',
            // 'TerminalID' => $this->terminalCode,
            'TerminalID' => '0004',
        );

        $data = array(
            'EDCID'        => 'Q80414400034',
            'EDCMac'       => '00:0C:D6:40:72:50',
            // 'EDCID'        => $this->edcId,
            // 'EDCMac'       => $this->edcMac,
            'Lon'          => '',
            'Lat'          => '',
            'connect_mode' => '',
        );

        if (!is_null($factor)) {
            $post['_Data'] = $this->cryptography->encryption($factor->Key, $factor->Iv, $data, $factor);
            $post['KI']    = $factor->Index;
        } else {
            $post['_Data'] = $this->cryptography->encryption($key, $iv, $data);
        }

        $this->load->helper(array('common'));
        // $result = json_decode(curlPost($url, $post));
        $result = curlPost($url, $post);

        MY_Controller::dumpData($post, $result);
    }

    /**
     *  gateway 設定檔測試
     */
    public function config()
    {
        $this->gateway_output->setConstantsFile('payment');
        $factor = null;

        $context = stream_context_create(array('http' => array('header' => 'Connection: close\r\n')));
        if (ENVIRONMENT === 'localhost') {
            $url    = 'http://gateway.wecanpay.localhost/setting/get';
            $factor = json_decode(file_get_contents('http://key.wecanpay.localhost/key/generate', false, $context));
        } else if (ENVIRONMENT === 'development') {
            $url    = 'http://gateway.dev.wecanpay.com.tw/setting/get';
            $factor = json_decode(file_get_contents('http://key.dev.wecanpay.com.tw/key/generate', false, $context));
        } else if (ENVIRONMENT === 'beta') {
            $url    = 'http://gateway.beta.wecanpay.com.tw/setting/get';
            $factor = json_decode(file_get_contents('http://key.beta.wecanpay.com.tw/key/generate', false, $context));
        } else if (ENVIRONMENT === 'preview') {
            $url    = 'https://gateway.pre.wecanpay.com.tw/setting/get';
            $factor = json_decode(file_get_contents('http://key.pre.wecanpay.com.tw/key/generate', false, $context));
        } else if (ENVIRONMENT === 'production') {
            $url    = 'https://gateway.wecanpay.com.tw/setting/get';
            $factor = json_decode(file_get_contents('http://key.wecanpay.com.tw/key/generate', false, $context));
        }

        $key = 'tZT07t9z5PxvMVN1YBJRtqzhaaZJo1pS';
        $iv  = '1jT8N2HSSfoFacH8';

        $post = array(
            'ResType'    => 'json',
            'MerchantID' => '0012348',
            'TerminalID' => $this->terminalCode,
            // 'TerminalID' => '0001',
        );

        $data = array(
            // 'EDCID'  => $this->edcId,
            // 'EDCMac' => $this->edcMac,
            'EDCID'  => 'Q80414400035',
            'EDCMac' => '00:0C:D6:40:72:51',
        );

        if (!is_null($factor)) {
            $post['_Data'] = $this->cryptography->encryption($factor->Key, $factor->Iv, $data, $factor);
            $post['KI']    = $factor->Index;
        } else {
            $post['_Data'] = $this->cryptography->encryption($key, $iv, $data);
        }

        $this->load->helper(array('common'));
        // $result = json_decode(curlPost($url, $post));
        $result = curlPost($url, $post);

        MY_Controller::dumpData($url, $factor, $post, $result);
    }

    /**
     *  gateway 預設設定檔
     */
    public function restore()
    {
        $this->gateway_output->setConstantsFile('payment');
        $factor = null;

        $context = stream_context_create(array('http' => array('header' => 'Connection: close\r\n')));
        if (ENVIRONMENT === 'localhost') {
            $url = 'http://gateway.wecanpay.localhost/setting/restore';
            // $factor = json_decode(file_get_contents('http://key.wecanpay.localhost/key/generate', false, $context));
        } else if (ENVIRONMENT === 'development') {
            $url = 'http://gateway.dev.wecanpay.com.tw/setting/restore';
            // $factor = json_decode(file_get_contents('http://key.dev.wecanpay.com.tw/key/generate', false, $context));
        } else if (ENVIRONMENT === 'beta') {
            $url = 'http://gateway.beta.wecanpay.com.tw/setting/restore';
            // $factor = json_decode(file_get_contents('http://key.beta.wecanpay.com.tw/key/generate', false, $context));
        } else if (ENVIRONMENT === 'preview') {
            $url = 'https://gateway.pre.wecanpay.com.tw/setting/restore';
            // $factor = json_decode(file_get_contents('http://key.pre.wecanpay.com.tw/key/generate', false, $context));
        } else if (ENVIRONMENT === 'production') {
            $url = 'https://gateway.wecanpay.com.tw/setting/restore';
            // $factor = json_decode(file_get_contents('http://key.wecanpay.com.tw/key/generate', false, $context));
        }

        $data = array(
            'EDCID'  => $this->edcId,
            'EDCMac' => $this->edcMac,
        );

        $this->load->helper(array('common'));
        // $result = json_decode(curlPost($url, $post));
        $result = curlPost($url, $data);

        MY_Controller::dumpData($url, $data, $result);
    }

    /**
     *  gateway 設定檔測試
     */
    public function initial()
    {
        $this->gateway_output->setConstantsFile('payment');
        $factor = null;

        $context = stream_context_create(array('http' => array('header' => 'Connection: close\r\n')));
        if (ENVIRONMENT === 'localhost') {
            $url    = 'http://gateway.wecanpay.localhost/setting/initial';
            $factor = json_decode(file_get_contents('http://key.wecanpay.localhost/key/generate', false, $context));
        } else if (ENVIRONMENT === 'development') {
            $url    = 'http://gateway.dev.wecanpay.com.tw/setting/initial';
            $factor = json_decode(file_get_contents('http://key.dev.wecanpay.com.tw/key/generate', false, $context));
        } else if (ENVIRONMENT === 'beta') {
            $url    = 'http://gateway.beta.wecanpay.com.tw/setting/initial';
            $factor = json_decode(file_get_contents('http://key.beta.wecanpay.com.tw/key/generate', false, $context));
        } else if (ENVIRONMENT === 'preview') {
            $url    = 'http://gateway.pre.wecanpay.com.tw/setting/initial';
            $factor = json_decode(file_get_contents('http://key.pre.wecanpay.com.tw/key/generate', false, $context));
        } else if (ENVIRONMENT === 'production') {
            $url    = 'https://gateway.wecanpay.com.tw/setting/initial';
            $factor = json_decode(file_get_contents('https://key.wecanpay.com.tw/key/generate', false, $context));
        }

        $post = array(
            'ResType' => 'json',
        );

        $data = array(
            'EDCID'  => $this->edcId,
            'EDCMac' => $this->edcMac,
        );

        if (!is_null($factor)) {
            $post['_Data'] = $this->cryptography->encryption($factor->Key, $factor->Iv, $data, $factor);
            $post['KI']    = $factor->Index;
        } else {
            $post['_Data'] = $this->cryptography->encryption($key, $iv, $data);
        }

        $this->load->helper(array('common'));
        // $result = json_decode(curlPost($url, $post));
        $result = curlPost($url, $post);

        MY_Controller::dumpData($data, $result);
    }

    /**
     *  回報更新完成
     */
    public function complete($type)
    {
        $this->gateway_output->setConstantsFile('payment');
        $factor = null;

        $context = stream_context_create(array('http' => array('header' => 'Connection: close\r\n')));
        if (ENVIRONMENT === 'localhost') {
            $url    = 'http://gateway.wecanpay.localhost/edc/complete/' . $type;
            $factor = json_decode(file_get_contents('http://key.wecanpay.localhost/key/generate', false, $context));
        } else if (ENVIRONMENT === 'development') {
            $url    = 'http://gateway.dev.wecanpay.com.tw/edc/complete/' . $type;
            $factor = json_decode(file_get_contents('http://key.dev.wecanpay.com.tw/key/generate', false, $context));
        } else if (ENVIRONMENT === 'beta') {
            $url    = 'http://gateway.beta.wecanpay.com.tw/edc/complete/' . $type;
            $factor = json_decode(file_get_contents('http://key.beta.wecanpay.com.tw/key/generate', false, $context));
        } else if (ENVIRONMENT === 'preview') {
            $url    = 'http://gateway.pre.wecanpay.com.tw/edc/complete/' . $type;
            $factor = json_decode(file_get_contents('http://key.pre.wecanpay.com.tw/key/generate', false, $context));
        } else if (ENVIRONMENT === 'production') {
            $url    = 'http://gateway.wecanpay.com.tw/edc/complete/' . $type;
            $factor = json_decode(file_get_contents('http://key.wecanpay.com.tw/key/generate', false, $context));
        }

        $post = array(
            'ResType'    => 'json',
            'MerchantID' => '0012348',
            'TerminalID' => $this->terminalCode,
        );

        $data = array(
            'EDCID'  => $this->edcId,
            'EDCMac' => $this->edcMac,
        );

        if ($type === 'config') {
            $data['UpdateConfig'] = 9;
        }

        if (!is_null($factor)) {
            $post['_Data'] = $this->cryptography->encryption($factor->Key, $factor->Iv, $data, $factor);
            $post['KI']    = $factor->Index;
        } else {
            $post['_Data'] = $this->cryptography->encryption($key, $iv, $data);
        }

        $this->load->helper(array('common'));
        // $result = json_decode(curlPost($url, $post));
        $result = curlPost($url, $post);

        MY_Controller::dumpData($post, $result);
    }

    /**
     *  gateway 重印測試
     */
    public function reprint($type = 'auth', $orderId = '')
    {
        $this->gateway_output->setConstantsFile('payment');
        $factor = null;

        $context = stream_context_create(array('http' => array('header' => 'Connection: close\r\n')));
        if (ENVIRONMENT === 'localhost') {
            $url    = 'http://gateway.wecanpay.localhost/payment/reprint';
            $factor = json_decode(file_get_contents('http://key.wecanpay.localhost/key/generate', false, $context));
        } else if (ENVIRONMENT === 'development') {
            $url    = 'http://gateway.dev.wecanpay.com.tw/payment/reprint';
            $factor = json_decode(file_get_contents('http://key.dev.wecanpay.com.tw/key/generate', false, $context));
        } else if (ENVIRONMENT === 'beta') {
            $url    = 'http://gateway.beta.wecanpay.com.tw/payment/reprint';
            $factor = json_decode(file_get_contents('http://key.beta.wecanpay.com.tw/key/generate', false, $context));
        } else if (ENVIRONMENT === 'preview') {
            $url    = 'http://gateway.pre.wecanpay.com.tw/payment/reprint';
            $factor = json_decode(file_get_contents('http://key.pre.wecanpay.com.tw/key/generate', false, $context));
        } else if (ENVIRONMENT === 'production') {
            $url    = 'http://gateway.wecanpay.com.tw/payment/reprint';
            $factor = json_decode(file_get_contents('http://key.wecanpay.com.tw/key/generate', false, $context));
        }

        $post = array(
            'ResType'    => 'json',
            'MerchantID' => '0012348',
            'TerminalID' => $this->terminalCode,
            'Gateway'    => '1.0',
        );

        $data = array(
            'Type'        => $type,
            'OrderID'     => '',
            'ServiceCode' => '001001001016',
            'EDCID'       => $this->edcId,
            'EDCMac'      => $this->edcMac,
            'AppName'     => 'WeCanPay_Payment',
        );

        if (!is_null($factor)) {
            $post['_Data'] = $this->cryptography->encryption($factor->Key, $factor->Iv, $data, $factor);
            $post['KI']    = $factor->Index;
        } else {
            $post['_Data'] = $this->cryptography->encryption($key, $iv, $data);
        }

        $this->load->helper(array('common'));
        // $result = json_decode(curlPost($url, $post));
        $result = curlPost($url, $post);

        MY_Controller::dumpData($post, $result);
    }

    /**
     *  gateway 開通商店功能測試
     */
    public function merchantNotify($type = 'spgateway')
    {
        $this->gateway_output->setConstantsFile('payment');
        $factor = null;

        $context = stream_context_create(array('http' => array('header' => 'Connection: close\r\n')));
        if (ENVIRONMENT === 'localhost') {
            $url = 'http://gateway.wecanpay.localhost/merchant/notify/' . $type;
            // $factor = json_decode(file_get_contents('http://key.wecanpay.localhost/key/generate', false, $context));
        } else if (ENVIRONMENT === 'development') {
            $url = 'http://gateway.dev.wecanpay.com.tw/merchant/notify/' . $type;
            // $factor = json_decode(file_get_contents('http://key.dev.wecanpay.com.tw/key/generate', false, $context));
        } else if (ENVIRONMENT === 'beta') {
            $url = 'http://gateway.beta.wecanpay.com.tw/merchant/notify/' . $type;
            // $factor = json_decode(file_get_contents('http://key.beta.wecanpay.com.tw/key/generate', false, $context));
        } else if (ENVIRONMENT === 'preview') {
            $url = 'https://gateway.pre.wecanpay.com.tw/merchant/notify/' . $type;
            // $factor = json_decode(file_get_contents('http://key.pre.wecanpay.com.tw/key/generate', false, $context));
        } else if (ENVIRONMENT === 'production') {
            $url = 'https://gateway.wecanpay.com.tw/merchant/notify/' . $type;
            // $factor = json_decode(file_get_contents('https://key.wecanpay.com.tw/key/generate', false, $context));
        }

        // $url = 'https://gateway.wecanpay.com.tw/merchant/notify/' . $type;

        $post = array(
            'CheckCode'  => 'B8BC57A1CE1C43FA01231837446F5D9DD5CCAA642F16D02B5C78078886C67EAD',
            'CreditInst' => 'OFF',
            'CreditRed'  => 'OFF',
            'Date'       => '2016-07-26 14:54:11',
            'MerchantID' => 'WCP60133540001',
            'UseInfo'    => 'ON',
        );

        $this->load->helper(array('common'));
        // $result = json_decode(curlPost($url, $post));
        $result = curlPost($url, $post);

        MY_Controller::dumpData($url, $post, $result);
    }
}
