<?php

class Edc extends MY_Controller
{
    protected $fields;

    public function __construct()
    {
        parent::__construct();
        // load payment config
        $this->config->load('param/gateway', true);
        $this->config->load('edc_setting', true);

        $this->fields = array(
            'ResType',
            'MerchantID',
            'TerminalID',
            '_Data',
            'KI',
        );
    }

    public function alive()
    {
        // $data = array(
        //     // 經度
        //     'Lon',
        //     // 緯度
        //     'Lat',
        //     'EDCID',
        //     'EDCMac',
        //     'Version',
        //     // 網路連線是 wifi, 3g, hinet
        //     'connect_mode'
        // );

        $this->data['postData'] = $this->input->post($this->fields, true);

        // 共同資料檢查
        $this->checkCommonParam(__FUNCTION__);

        // MY_Controller::dumpData($this->data);

        // 檢查終端代碼是否綁定 EDC
        $this->load->model('edc_model');
        $edc = $this->edc_model->getEdcByEdcIdAndEdcMac($this->data['postData']['_Data']['EDCID'], $this->data['postData']['_Data']['EDCMac']);
        if (!is_null($edc)) {
            // 判斷 EDCID & EDCMac 是否有傳入
            if (!isset($this->data['postData']['_Data']['EDCID']) || empty($this->data['postData']['_Data']['EDCID'])) {
                $this->gateway_output->error('EDC_90000');
            }
            if (!isset($this->data['postData']['_Data']['EDCMac']) || empty($this->data['postData']['_Data']['EDCMac'])) {
                $this->gateway_output->error('EDC_90001');
            }
            // 判斷 EDCID & EDCMac 是否正確
            if ($this->data['postData']['_Data']['EDCID'] !== $edc['edc_id']) {
                $this->gateway_output->error('EDC_90002');
            }
            if ($this->data['postData']['_Data']['EDCMac'] !== $edc['edc_mac']) {
                $this->gateway_output->error('EDC_90003');
            }
            // 判斷 EDC 狀態
            if ($edc['edc_status'] !== '1') {
                $this->gateway_output->error('EDC_90005');
            }
        } else {
            $this->gateway_output->error('EDC_90002');
        }

        // 經度
        // if (!isset($this->data['postData']['_Data']['Lon']) || empty($this->data['postData']['_Data']['Lon'])) {
        if (!isset($this->data['postData']['_Data']['Lon'])) {
            $this->gateway_output->error('EDC_90006');
        }
        // 緯度
        // if (!isset($this->data['postData']['_Data']['Lat']) || empty($this->data['postData']['_Data']['Lat'])) {
        if (!isset($this->data['postData']['_Data']['Lat'])) {
            $this->gateway_output->error('EDC_90007');
        }
        // 網路環境
        // if (!isset($this->data['postData']['_Data']['connect_mode']) || empty($this->data['postData']['_Data']['connect_mode'])) {
        if (!isset($this->data['postData']['_Data']['connect_mode'])) {
            $this->gateway_output->error('EDC_90010');
        }

        // 存入 EDC status log
        $this->load->model('edc_status_model');
        $insertData = array(
            'edc_idx'      => $edc['idx'],
            'merchant_id'  => $this->data['postData']['MerchantID'],
            // 'version'      => $this->data['postData']['_Data']['Version'],
            'connect_mode' => $this->data['postData']['_Data']['connect_mode'],
            'lon'          => $this->data['postData']['_Data']['Lon'],
            'lat'          => $this->data['postData']['_Data']['Lat'],
            'ip'           => $this->getRealIp(),
            'create_time'  => date('Y-m-d H:i:s'),
        );

        if ($this->edc_status_model->insert($insertData)) {
            $this->response['Status'] = 'REPORT_00000';

            // update soft
            $this->load->model('edc_update_model');
            $updateSet    = $this->edc_update_model->getLatestEdcSetVersion($edc['idx'], $edc['device_idx']);
            $updateConfig = $this->edc_update_model->getLatestConfig($edc['idx'], $edc['device_idx']);

            // MY_Controller::dumpData($updateSet);

            if (!is_null($updateSet)) {
                // 取得目前版本的 App
                $this->load->model('edc_set_model');
                // $oldEdcApp = $this->edc_set_model->getEdcSetAppByEdcSetIdx($edc['edc_set_idx']);
                $newEdcApp = $this->edc_set_model->getEdcSetAppByEdcSetIdx($updateSet['edc_set_idx']);
                $newSet    = $this->edc_set_model->getEdcSetBySetIdx($updateSet['edc_set_idx']);

                $this->response['UpdateSet']['url']       = $newSet['edc_set_url'];
                $this->response['UpdateSet']['check_sum'] = $newSet['edc_set_check_sum'];

                foreach ($newEdcApp as $new) {
                    $this->response['UpdateSet']['package'][$new['app_name']]['version'] = $new['app_version'];
                }

                // MY_Controller::dumpData($oldApp, $newApp, $result);
            }

            if (!isset($this->response['UpdateSet'])) {
                $this->response['UpdateSet'] = array();
            } else {
                $this->response['UpdateSet']['priorty'] = $updateSet['update_priorty'];
            }

            if (!is_null($updateConfig)) {
                $this->response['UpdateConfig'] = $updateConfig['idx'];
            } else {
                $this->response['UpdateConfig'] = '0';
            }

            // MY_Controller::dumpData($this->response);
        } else {
            $this->response['Status'] = 'REPORT_00001';
        }

        $this->gateway_output->resultOutput($this->response);
    }

    public function complete($type = null)
    {
        $this->data['postData'] = $this->input->post($this->fields, true);

        // 共同資料檢查
        $this->checkCommonParam(__FUNCTION__);

        // 檢查終端代碼是否綁定 EDC
        $this->load->model('edc_model');
        $edc = $this->edc_model->getEdcByEdcIdAndEdcMac($this->data['postData']['_Data']['EDCID'], $this->data['postData']['_Data']['EDCMac']);
        if (!is_null($edc)) {
            // 判斷 EDC 狀態
            if ($edc['edc_status'] !== '1') {
                $this->gateway_output->error('EDC_90005');
            }
        } else {
            $this->gateway_output->error('EDC_90002');
        }

        // 取得此 edc 最後更新的版本
        $update = null;
        $this->load->model('edc_update_model');
        if ($type === 'app') {
            $update = $this->edc_update_model->getLatestEdcSetVersion($edc['idx'], $edc['device_idx']);
        } else if ($type === 'config') {
            // 判斷 UpdateConfig 是否有傳入
            if (!isset($this->data["postData"]['_Data']["UpdateConfig"]) || empty($this->data["postData"]['_Data']["UpdateConfig"]) || !is_numeric($this->data["postData"]['_Data']["UpdateConfig"])) {
                $this->gateway_output->error("EDC_90011");
            }
            $update = $this->edc_update_model->getConfigByIdx($this->data["postData"]['_Data']["UpdateConfig"], $edc['idx'], $edc['device_idx']);
        }

        if (is_null($update)) {
            $this->gateway_output->error('SYS_70011');
        }

        if ($type === 'app') {
            // 更新 EDC 程式更新狀態
            $this->edc_update_model->updateEdcUpdateStatus($update['idx'], $edc['idx'], $edc['device_idx']);
            // 取得目前版本的 App
            $this->load->model('edc_set_model');
            $app = $this->edc_set_model->getEdcSetAppByEdcSetIdx($update['edc_set_idx']);

            // MY_Controller::dumpData($app);

            // 取得舊的設定檔
            $oldServerConfig = json_decode($this->data['terminal']['edc_config'], true);
            $oldClientConfig = json_decode($this->data['terminal']['edc_client_config'], true);

            // 取得可修改變數
            $modified = $this->config->item('modified', 'edc_setting');

            // MY_Controller::dumpData($app);

            foreach ($app as $a) {
                $serverConfig = json_decode($a['server_config'], true);
                $serverSystem = json_decode($a['server_system_config'], true);
                $clientConfig = json_decode($a['client_config'], true);
                $clientSystem = json_decode($a['client_system_config'], true);

                if (is_null($serverSystem)) {
                    $serverSystem = array();
                }

                if (is_null($clientSystem)) {
                    $clientSystem = array();
                }

                // MY_Controller::dumpData($serverConfig, $serverSystem, $clientConfig, $clientSystem);

                foreach ($serverConfig as $key => $value) {
                    if (isset($oldServerConfig[$a['app_name']])) {
                        if (!array_key_exists($key, $modified)) {
                            $oldServerConfig[$a['app_name']][$key] = $value;
                        }
                    } else {
                        $oldServerConfig[$a['app_name']][$key] = $value;
                    }
                }
                foreach ($serverSystem as $key => $value) {
                    if (isset($oldServerConfig['system'])) {
                        if (!array_key_exists($key, $modified)) {
                            $oldServerConfig['system'][$key] = $value;
                        }
                    } else {
                        $oldServerConfig['system'][$key] = $value;
                    }
                }

                foreach ($clientConfig as $key => $value) {
                    if (isset($oldClientConfig['Application'][$a['app_name']])) {
                        if (!array_key_exists($key, $modified)) {
                            $oldClientConfig['Application'][$a['app_name']][$key] = $value;
                        }
                    } else {
                        $oldClientConfig['Application'][$a['app_name']][$key] = $value;
                    }
                }
                foreach ($clientSystem as $key => $value) {
                    if (isset($oldClientConfig['Global']['system'])) {
                        if (!array_key_exists($key, $modified)) {
                            $oldClientConfig['Global']['system'][$key] = $value;
                        }
                    } else {
                        $oldClientConfig['Global']['system'][$key] = $value;
                    }
                }
            }

            // MY_Controller::dumpData($oldServerConfig, $oldClientConfig);

            // 更新 edc 最新 config
            $updateData = array(
                'edc_set_idx'       => $update['edc_set_idx'],
                'edc_config'        => json_encode($oldServerConfig),
                'edc_client_config' => json_encode($oldClientConfig),
            );
            $where = array(
                'idx' => intval($this->data['terminal']['idx']),
            );
            $this->terminal_model->update($updateData, $where);

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
                $factor = json_decode(file_get_contents('https://key.pre.wecanpay.com.tw/key/generate', false, $context));
            } else if (ENVIRONMENT === 'production') {
                $url    = 'https://gateway.wecanpay.com.tw/setting/get';
                $factor = json_decode(file_get_contents('https://key.wecanpay.com.tw/key/generate', false, $context));
            }

            $post = array(
                'ResType'    => 'json',
                'MerchantID' => $this->data['merchant']['merchant_id'],
                'TerminalID' => $this->data['terminal']['terminal_code'],
            );

            $data = array(
                'EDCID'  => $this->data['postData']['_Data']['EDCID'],
                'EDCMac' => $this->data['postData']['_Data']['EDCMac'],
            );

            $this->load->library('cryptography/cryptography');
            $post['_Data'] = $this->cryptography->encryption($factor->Key, $factor->Iv, $data, $factor);
            $post['KI']    = $factor->Index;

            $this->load->helper(array('common'));
            if (ENVIRONMENT === 'production' || ENVIRONMENT === 'preview') {
                $result = json_decode(curlPost($url, $post, true), true);
            } else {
                $result = json_decode(curlPost($url, $post), true);
            }

            $this->response['Data'] = $result['Data'];
        } else {
            // 更新 EDC 設定檔更新狀態
            $this->edc_update_model->updateEdcConfigUpdateStatus($update['idx'], $edc['idx'], $edc['device_idx']);
        }

        // MY_Controller::dumpData($oldClientConfig, $oldServerConfig);

        $this->response['Status'] = 'REPORT_00000';

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
        if (is_null($this->data['postData']['_Data']) && empty($this->data['postData']['_Data'])) {
            $this->gateway_output->error('SYS_60000');
        }

        // 檢查商店代號是否存在
        $this->load->model('merchant_model');
        $this->data['merchant'] = $this->merchant_model->getMerchantByMerchantId($this->data['postData']['MerchantID']);

        // 檢查 merchant 狀態
        if (is_null($this->data['merchant'])) {
            // 代號錯誤
            $this->gateway_output->error('MER_10000');
        } else if ($this->data["merchant"]["merchant_status"] == "1") {
            // 商店代號審核中
            $this->gateway_output->error("MER_10004");
        } else if ($this->data["merchant"]["merchant_status"] == "8" || $this->data["merchant"]["merchant_status"] == "9") {
            // 商店代號停用
            $this->gateway_output->error("MER_10001");
        }

        // 檢查終端代碼是否存在
        $this->load->model('terminal_model');
        $this->data['terminal'] = $this->terminal_model->getTerminalByMerchantIdxAndTerminalCode($this->data['merchant']['idx'], $this->data['postData']['TerminalID']);
        if (is_null($this->data['terminal'])) {
            $this->gateway_output->error('TERMINAL_80001');
        }

        // 檢查終端代碼是否停用
        if ($this->data["terminal"]["terminal_status"] == "0") {
            $this->gateway_output->error("TERMINAL_80002");
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
                $result = json_decode(file_get_contents($url . $this->data['postData']['KI'], false, $context));
                if ($result->Status) {
                    $factor = $result->Key;
                }
            }
        }

        // 將 Data 解密
        $this->load->library('cryptography/cryptography');
        if (is_null($factor)) {
            $this->gateway_output->error("SYS_60004");
        } else {
            $this->data['postData']['_Data'] = $this->cryptography->decryption($factor->key, $factor->iv, $this->data['postData']['_Data'], $factor);
        }
        if (!$this->data['postData']['_Data']) {
            $this->gateway_output->error('SYS_60001');
        }

        // 判斷 EDCID & EDCMac 是否有傳入
        if (!isset($this->data["postData"]['_Data']["EDCID"]) || empty($this->data["postData"]['_Data']["EDCID"])) {
            $this->gateway_output->error("EDC_90000");
        }
        if (!isset($this->data["postData"]['_Data']["EDCMac"]) || empty($this->data["postData"]['_Data']["EDCMac"])) {
            $this->gateway_output->error("EDC_90001");
        }

        // 記錄解密完資料
        $this->load->model('log_connect_decode_model');
        $this->log_connect_decode_model->insertConnectDecode($this->data['connectLogIdx'], get_class($this), $method, $this->data['postData']);
    }

    public function getRealIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            // IP分享器
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }
}
