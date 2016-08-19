<?php

class Setting extends MY_Controller
{
    protected $fields;

    public function __construct()
    {
        parent::__construct();
        // load payment config
        $this->config->load('param/gateway', true);
        // $this->config->load('edc_setting', true);

        $this->fields = array(
            'ResType',
            'MerchantID',
            'TerminalID',
            '_Data',
            'KI',
        );
    }

    public function get()
    {
        $this->data['postData'] = $this->input->post($this->fields, true);

        // 共同資料檢查
        $this->checkCommonParam(__FUNCTION__);

        // 檢查終端代碼是否綁定 EDC
        $this->load->model('edc_model');
        $this->data['edc'] = $this->edc_model->getEdcByEdcIdAndEdcMac($this->data['postData']['_Data']['EDCID'], $this->data['postData']['_Data']['EDCMac']);
        if (!is_null($this->data['edc'])) {
            // 判斷 EDC 狀態
            if ($this->data['edc']['edc_status'] !== '1') {
                $this->gateway_output->error('EDC_90005');
            }
        } else {
            $this->gateway_output->error('EDC_90002');
        }

        $this->load->library('profile', $this->data);
        $config = $this->profile->getConfigByEdc();

        if ($config) {
            $this->response['Status'] = 'SETTING_00000';
            $this->response['Data']   = $config;
        } else {
            $this->response['Status'] = 'SETTING_00001';
        }

        $this->gateway_output->resultOutput($this->response);
    }

    public function restore()
    {
        $this->data['postData'] = $this->input->post(array('EDCID', 'EDCMac'), true);

        // 檢查終端代碼是否綁定 EDC
        $this->load->model('edc_model');
        $this->data['edc'] = $this->edc_model->getEdcByEdcIdAndEdcMac($this->data['postData']['EDCID'], $this->data['postData']['EDCMac']);
        if (!is_null($this->data['edc'])) {
            // 判斷 EDC 狀態
            if ($this->data['edc']['edc_status'] !== '1') {
                $this->gateway_output->error('EDC_90005');
            }
        } else {
            $this->gateway_output->error('EDC_90002');
        }

        // get terminal
        $this->load->model('terminal_model');
        $this->data['terminal'] = $this->terminal_model->getTerminalByEdcIdx($this->data['edc']['idx']);

        $this->load->library('profile', $this->data);
        $config = $this->profile->getConfigByEdc();

        if ($config) {
            $this->response['Status'] = 'SETTING_00000';
            $this->response['Data']   = $config;
        } else {
            $this->response['Status'] = 'SETTING_00001';
        }

        $this->gateway_output->resultOutput($this->response);
    }

    public function initial()
    {
        $this->data['postData'] = $this->input->post(array('ResType', 'KI', '_Data'), true);

        // 檢查 ResType 參數是否正確
        $responseType = $this->config->item('response_type', 'param/gateway');
        if (!in_array(strtolower($this->data['postData']['ResType']), $responseType)) {
            $this->gateway_output->error('SYS_70001');
        }

        // 判斷有沒有 KI
        $factor = null;
        if (isset($this->data['postData']['KI'])) {
            if (!is_null($this->data['postData']['KI']) && !empty($this->data['postData']['KI'])) {
                switch (ENVIRONMENT) {
                    case 'development':
                        $url = 'http://key.dev.wecanpay.com.tw/key/get/';
                        break;
                    case 'beta':
                        $url = 'http://key.beta.wecanpay.com.tw/key/get/';
                        break;
                    case 'preview':
                        $url = 'https://key.pre.wecanpay.com.tw/key/get/';
                        break;
                    case 'production':
                        $url = 'https://key.wecanpay.com.tw/key/get/';
                        break;
                    default:
                        $url = 'http://key.wecanpay.localhost/key/get/';
                        break;
                }

                $context = stream_context_create(array('http' => array('header' => 'Connection: close\r\n')));
                $result  = json_decode(file_get_contents($url . $this->data['postData']['KI'], false, $context));

                if ($result->Status) {
                    $factor = $result->Key;
                }
            }
        }

        // 將 Data 解密
        $this->load->library('cryptography/cryptography');
        if (is_null($factor)) {
            $this->gateway_output->error('SYS_60004');
        } else {
            $this->data['postData']['_Data'] = $this->cryptography->decryption($factor->key, $factor->iv, $this->data['postData']['_Data'], $factor);
        }
        if (!$this->data['postData']['_Data']) {
            $this->gateway_output->error('SYS_60001');
        }

        // 記錄解密完資料
        $this->load->model('log_connect_decode_model');
        $this->log_connect_decode_model->insertConnectDecode($this->data['connectLogIdx'], get_class($this), __FUNCTION__, $this->data['postData']);

        // MY_Controller::dumpData($this->data);

        // 檢查終端代碼是否綁定 EDC
        $this->load->model('edc_model');
        $this->data['edc'] = $this->edc_model->getEdcByEdcIdAndEdcMac($this->data['postData']['_Data']['EDCID'], $this->data['postData']['_Data']['EDCMac']);
        if (!is_null($this->data['edc'])) {
            // 判斷 EDC 狀態
            if ($this->data['edc']['edc_status'] !== '1') {
                $this->gateway_output->error('EDC_90005');
            }
        } else {
            $this->gateway_output->error('EDC_90002');
        }

        // get terminal
        $this->load->model('terminal_model');
        $this->data['terminal'] = $this->terminal_model->getTerminalByEdcIdx($this->data['edc']['idx']);

        $this->load->library('profile', $this->data);
        $config = $this->profile->getConfigByEdc();

        if ($config) {
            $this->response['Status'] = 'SETTING_00000';
            $this->response['Data']   = $config;
        } else {
            $this->response['Status'] = 'SETTING_00001';
        }

        $this->gateway_output->resultOutput($this->response);
    }

    protected function checkCommonParam($method = '')
    {
        // 檢查 ResType 參數是否正確
        $responseType = $this->config->item('response_type', 'param/gateway');
        if (!in_array(strtolower($this->data['postData']['ResType']), $responseType)) {
            $this->gateway_output->error('SYS_70001');
        }

        // set output type
        $this->gateway_output->setOutputType(strtolower($this->data['postData']['ResType']));

        // 檢查 MerchantID 是否為空
        if (is_null($this->data['postData']['MerchantID']) || empty($this->data['postData']['MerchantID'])) {
            $this->gateway_output->error('MER_10002');
        }

        // 檢查 TerminalID 是否為空 & 是否為四碼數字
        if (is_null($this->data['postData']['TerminalID']) || empty($this->data['postData']['TerminalID'])) {
            $this->gateway_output->error('TERMINAL_80000');
        } else if (strlen($this->data['postData']['TerminalID']) !== 4 || !is_numeric($this->data['postData']['TerminalID'])) {
            $this->gateway_output->error('TERMINAL_80001');
        }

        // 檢查 _Data 是否為空
        if (is_null($this->data['postData']['TerminalID']) || empty($this->data['postData']['TerminalID'])) {
            $this->gateway_output->error('SYS_60000');
        }

        // 檢查商店代號是否存在
        $this->load->model('merchant_model');
        $this->data['merchant'] = $this->merchant_model->getMerchantByMerchantId($this->data['postData']['MerchantID']);

        // 檢查 merchant 狀態
        if (is_null($this->data['merchant'])) {
            // 代號錯誤
            $this->gateway_output->error('MER_10000');
        } else if ($this->data['merchant']['merchant_status'] == '1') {
            // 商店代號審核中
            $this->gateway_output->error('MER_10004');
        } else if ($this->data['merchant']['merchant_status'] == '8' || $this->data['merchant']['merchant_status'] == '9') {
            // 商店代號停用
            $this->gateway_output->error('MER_10001');
        }

        // 檢查終端代碼是否存在
        $this->load->model('terminal_model');
        $this->data['terminal'] = $this->terminal_model->getTerminalByMerchantIdxAndTerminalCode($this->data['merchant']['idx'], $this->data['postData']['TerminalID']);
        if (is_null($this->data['terminal'])) {
            $this->gateway_output->error('TERMINAL_80001');
        }

        // 檢查終端代碼是否停用
        if ($this->data['terminal']['terminal_status'] == '0') {
            $this->gateway_output->error('TERMINAL_80002');
        }

        // 檢查終端代碼使用期限是否過期
        if (intval($this->data['terminal']['use_end_time']) < time()) {
            $this->gateway_output->error('TERMINAL_80003');
        }

        // 判斷有沒有 KI
        $factor = null;
        if (isset($this->data['postData']['KI'])) {
            if (!is_null($this->data['postData']['KI']) && !empty($this->data['postData']['KI'])) {
                switch (ENVIRONMENT) {
                    case 'development':
                        $url = 'http://key.dev.wecanpay.com.tw/key/get/';
                        break;
                    case 'beta':
                        $url = 'http://key.beta.wecanpay.com.tw/key/get/';
                        break;
                    case 'preview':
                        $url = 'https://key.pre.wecanpay.com.tw/key/get/';
                        break;
                    case 'production':
                        $url = 'https://key.wecanpay.com.tw/key/get/';
                        break;
                    default:
                        $url = 'http://key.wecanpay.localhost/key/get/';
                        break;
                }

                $context = stream_context_create(array('http' => array('header' => 'Connection: close\r\n')));
                $result  = json_decode(file_get_contents($url . $this->data['postData']['KI'], false, $context));

                if ($result->Status) {
                    $factor = $result->Key;
                }
            }
        }

        // 將 Data 解密
        $this->load->library('cryptography/cryptography');
        if (is_null($factor)) {
            $this->gateway_output->error('SYS_60004');
        } else {
            $this->data['postData']['_Data'] = $this->cryptography->decryption($factor->key, $factor->iv, $this->data['postData']['_Data'], $factor);
        }
        if (!$this->data['postData']['_Data']) {
            $this->gateway_output->error('SYS_60001');
        }

        // 記錄解密完資料
        $this->load->model('log_connect_decode_model');
        $this->log_connect_decode_model->insertConnectDecode($this->data['connectLogIdx'], get_class($this), $method, $this->data['postData']);
    }
}
