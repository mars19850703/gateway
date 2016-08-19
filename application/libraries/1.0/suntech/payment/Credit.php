<?php

class Credit extends BaseModule implements CreditPayment
{
    // module path
    protected $modulePath;
    // spgateway config
    protected $config;

    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        $this->ci->lang->load('suntech', 'zh');
        // load model
        $this->ci->load->model('auth_model');
        $this->ci->load->model('cash_config_model');
        // load get app model
        $this->ci->load->model('edc_app_mapping_model');

        // load config
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            include dirname(__FILE__) . '\..\Config.php';
        } else {
            include dirname(__FILE__) . '/../Config.php';
        }
        $this->config = $suntech;

        // set module path
        $this->modulePath = '1.0/' . $this->supplier['supplier_code'] . '/';

        // load cryptography
        $this->ci->load->library($this->modulePath . 'suntech_cryptography', null, 'SuntechCryptography');
        // load valid
        $this->ci->load->library($this->modulePath . 'payment/suntech_credit_valid', null, 'SuntechValid');
        // load log gateway library
        $this->ci->load->library($this->modulePath . 'payment/suntech_payment_log', null, 'SuntechPaymentLog');
        // 載入資料轉換 library
        $this->ci->load->library($this->modulePath . 'payment/suntech_credit_data', null, 'SuntechCreditDataTransform');
        // 載入輸出資料轉換 library
        $this->ci->load->library($this->modulePath . 'payment/suntech_output_data', null, 'SuntechPaymentOutout');

        $this->data = array(
            'success'  => false,
            'code'     => '',
            'errorMsg' => '',
            'data'     => array(),
        );
    }

    public function auth(array $postData, array $responseData)
    {
        // 檢查 service code 是否為取消
        if ($this->option['option_group'] !== 'auth') {
            return 'SYS_70005';
        }

        // 檢查 _Data 資料是否正確
        $postData['_Data'] = $this->ci->SuntechValid->{__FUNCTION__}($postData);
        if (!is_array($postData['_Data'])) {
            return $postData['_Data'];
        }

        // 判斷分期數字是否正確
        if (isset($this->option['option_code'])) {
            $inst = explode('-', $this->option['option_code']);
            if ($inst[1] !== $postData['_Data']['Inst']) {
                return 'TRA_20008';
            }
        } else {
            if ($postData['_Data']['Inst'] !== '0') {
                return 'TRA_20008';
            }
        }

        // get app by app name
        $app = $this->ci->edc_app_mapping_model->getAppDetailByEdcSetIdxAndAppName($this->terminal['edc_set_idx'], $postData['_Data']['AppName']);
        if (!$app) {
            return 'EDC_90008';
        }
        $postData['_Data']['AppVersion'] = $app['app_version'];
        $postData['_Data']['ActReq']     = $this->edcConfig[$postData['_Data']['AppName']]['ActReq'];

        // 檢查同一商店訂單編號是否重複
        $authData = $this->ci->auth_model->getAuthByMerchantIdAndOrderId($postData['MerchantID'], $postData['_Data']['OrderID']);
        if (!is_null($authData)) {
            return 'TRA_20004';
        }

        // 產出這次交易代碼
        $this->ci->load->library($this->modulePath . 'suntech_random', null, 'SuntechRandom');
        $postData['transactionNo'] = $this->ci->SuntechRandom->generateTransactionNo();

        // log gateway
        $paymentLogId = $this->ci->SuntechPaymentLog->insertPaymentLog(__FUNCTION__, $this->connectLogIdx, $postData);

        // insert auth data
        $authId = $this->ci->auth_model->insertAuth($paymentLogId, $this->merchant, $this->edc, $postData);

        // 取得此商店在金流商的相關設定
        $cashConfig = $this->ci->cash_config_model->getCashConfigByTerminalIdxAndServiceIdx($this->terminal['idx'], $this->supplier['idx'], $this->product['idx']);
        if (is_null($cashConfig)) {
            return 'SYS_70002';
        }
        $cashConfig['config_data']       = json_decode($cashConfig['config_data']);
        $postData['_Data']['MerchantID'] = $cashConfig['config_data']->MerchantID;

        // 資料轉換
        $paymentData = $this->ci->SuntechCreditDataTransform->{__FUNCTION__}($postData['_Data']);
        // 將資料 post 給金流商做交易
        $paymentData['paymentLogId'] = $paymentLogId;
        $paymentData['supplierIdx']  = $this->supplier['idx'];
        $paymentData['secret']       = $cashConfig['config_data']->Secret;
        // 金流商網址
        $gateway = $this->config['credit_auth'];
        // 信用卡資料加密
        $cardInfo = $postData['_Data']['CardNo'] . $postData['_Data']['Cvv2'] . substr($postData['_Data']['CardExpire'], 0, 2) . '/' . substr($postData['_Data']['CardExpire'], 2);
        $cardInfo = $this->ci->SuntechCryptography->encryption($cardInfo, $paymentData['secret']);

        $inputArray = array(
            'IP'        => $this->ci->input->ip_address(),
            'web'       => $paymentData['web'],
            'MN'        => $paymentData['MN'],
            'OrderInfo' => $paymentData['OrderInfo'],
            'Td'        => $paymentData['Td'],
            'CardData'  => $cardInfo,
        );

        // 記錄log
        $this->ci->load->model('log_payment_auth_model');
        $logId = $this->ci->log_payment_auth_model->insertSuntechLogPayment($paymentData, $inputArray);

        $result = $this->soapPost($gateway, $inputArray);
        if (isset($result->Add_CardPayAuthoriseResult)) {
            $returnData = $result->Add_CardPayAuthoriseResult;
        } else {
            $returnData = array();
        }

        // 狀態
        $status = $this->checkReturnData(__FUNCTION__, $paymentData, $returnData);

        // 更新log
        $updateLogData = array(
            'trade_no'    => (isset($returnData->BuySafeNo) && $returnData->BuySafeNo) ? $returnData->BuySafeNo : '',
            'status'      => $status,
            'return_data' => json_encode((array) $returnData),
            'ip'          => $_SERVER['REMOTE_ADDR'],
            'return_time' => date('Y-m-d H:i:s'),
        );
        $where = array('idx' => $logId);
        $this->ci->log_payment_auth_model->update($updateLogData, $where);

        $this->data['success']     = true;
        $this->data['code']        = $this->ci->lang->line('CREDIT_' . $status);
        $this->data['data']        = $returnData;
        $this->data['gateway']     = $this->supplier['supplier_code'];
        $this->data['gatewayName'] = $this->supplier['supplier_name'];

        // 更新授權資料
        $this->ci->auth_model->updateSuntechTransactionAuth($authId, $this->data);

        // 取得輸出 data
        $result = $this->ci->SuntechPaymentOutout->authOutput($this->action['action_code'], $postData, $this->data);

        // output log gateway
        $this->ci->SuntechPaymentLog->updatePaymentLog($paymentLogId, $result);

        return $result;
    }

    public function cancel(array $postData, array $responseData)
    {
    }

    public function request(array $postData, array $responseData)
    {
    }

    public function refund(array $postData, array $responseData)
    {
    }

    public function query(array $postData, array $responseData)
    {
    }

    /**
     * 檢查回來的結果
     * @param  [array] $data       [原始要post的資料]
     * @param  [array] $returnData [送spgateway回來的json decode資料]
     * @param  [string] $method     [那種付費方式]
     * @return [string]             [交易狀態]
     */
    protected function checkReturnData($method, $data, $returnData)
    {
        $status = 'CHECK_FAIL';

        if (!empty($method) && !empty($data) && !empty($returnData)) {
            // check return data
            if (is_object($returnData) && isset($returnData->RespCode)) {
                switch ($method) {
                    case 'auth':
                        if ($returnData->RespCode === '00' && isset($returnData->ChkValue) && isset($returnData->MN)) {
                            $checkValue = strtoupper(sha1($data['web'] . $data['secret'] . $returnData->BuySafeNo . $data['MN'] . $returnData->RespCode));

                            // MY_Controller::dumpData($data, $returnData, $returnData->ChkValue, $checkValue);

                            if ($returnData->ChkValue == $checkValue && $data['MN'] == $returnData->MN && $data['Td'] == $returnData->Td && $data['web'] == $returnData->web) {
                                $status = 'SUCCESS';
                            }
                        }
                        break;
                    default:
                        break;
                }
            }
        }
        return $status;
    }

    private function soapPost($url, $data)
    {
        // SOAP
        $options = array(
            'soap_version' => SOAP_1_2,
            'exceptions'   => true,
            'trace'        => 1,
        );

        $client = new SoapClient($url, $options);
        return $client->Add_CardPayAuthorise($data);
    }
}
