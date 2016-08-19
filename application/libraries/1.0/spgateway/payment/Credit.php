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
        $this->ci->load->helper(array('common'));
        $this->ci->lang->load('pay2go', 'zh');

        // load model
        $this->ci->load->model('auth_model');
        $this->ci->load->model('cash_config_model');

        // load config
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            include dirname(__FILE__) . '\..\Config.php';
        } else {
            include dirname(__FILE__) . '/../Config.php';
        }
        $this->config = $spgateway;

        // set module path
        $this->modulePath = '1.0/' . $this->supplier['supplier_code'] . '/';

        // load cryptography
        $this->ci->load->library($this->modulePath . 'spgateway_cryptography', null, 'SpgatewayCryptography');
        // load valid
        $this->ci->load->library($this->modulePath . 'check_data/payment/credit_valid', null, 'SpgatewayValid');
        // load log gateway library
        $this->ci->load->library($this->modulePath . 'log/payment_log', null, 'SpgatewayPaymentLog');
        // 載入資料轉換 library
        $this->ci->load->library($this->modulePath . 'data_transform/spgateway_data', $this->config, 'spgatewayDataTransform');
        // 載入輸出資料轉換 library
        $this->ci->load->library($this->modulePath . 'data_transform/spgateway_output_data', null, 'SpgatewayPaymentOutout');
        // load get app model
        $this->ci->load->model('edc_app_mapping_model');

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
        $postData['_Data'] = $this->ci->SpgatewayValid->auth($postData);
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
        $this->ci->load->library($this->modulePath . 'generate/random', null, 'SpgatewayRandom');
        $postData['transactionNo'] = $this->ci->SpgatewayRandom->generateTransactionNo();

        // log gateway
        $paymentLogId = $this->ci->SpgatewayPaymentLog->insertPaymentLog(__FUNCTION__, $this->connectLogIdx, $postData);

        // insert auth data
        $authId = $this->ci->auth_model->insertAuth($paymentLogId, $this->merchant, $this->edc, $postData);

        // 取得此商店在金流商的相關設定
        $cashConfig = $this->ci->cash_config_model->getCashConfigByTerminalIdxAndServiceIdx($this->terminal['idx'], $this->supplier['idx'], $this->product['idx']);
        if (is_null($cashConfig)) {
            return 'SYS_70002';
        }
        $cashConfig['config_data']       = json_decode($cashConfig['config_data']);
        $postData['_Data']['MerchantID'] = $cashConfig['config_data']->MerchantID;
        $postData['_Data']['Key']        = $cashConfig['config_data']->Key;
        $postData['_Data']['Iv']         = $cashConfig['config_data']->Iv;

        // 資料轉換
        $paymentData = $this->ci->spgatewayDataTransform->auth($postData['_Data']);

        // 將資料 post 給金流商做交易
        $paymentData['paymentLogId'] = $paymentLogId;
        $paymentData['supplierIdx']  = $this->supplier['idx'];

        // load log model
        $this->ci->load->model('log_payment_auth_model');

        //選擇版本
        // $merchantId = $paymentData['MerchantID']; // 商店代號
        // $key        = $paymentData['HashKey']; //商店代號的 Key 值
        // $iv         = $paymentData['HashIV']; // 商店代號的 iv 值
        $gateway = $this->config['credit_card_url']; //授權閘道位址

        $pos = (isset($paymentData['POS_'])) ? $paymentData['POS_'] : 'JSON'; // 此範例可為 JSON 或 String

        $PayerEmail = $paymentData['PayerEmail'];
        $inputArray = array(
            'TimeStamp'       => $paymentData['TimeStamp'], //時間戳記
            'Version'         => $paymentData['Version'], //串接程式版本
            'MerchantOrderNo' => $paymentData['MerchantOrderNo'], //商店訂單編號，同一店鋪中此編號不可重覆
            'Amt'             => intval($paymentData['Amt']), //訂單金額
            'ProdDesc'        => $paymentData['ProdDesc'], //商品描述
            'PayerEmail'      => $paymentData['PayerEmail'], //付款人 Email
            'Inst'            => $paymentData['Inst'], //信用卡 分期付款啟用
            'CardNo'          => $paymentData['CardNo'],
            'Exp'             => $paymentData['Exp'],
            'CVC'             => $paymentData['CVC'],
        );

        // 加密函式
        $spgatewayPostData = $this->ci->SpgatewayCryptography->encryption($cashConfig['config_data']->Key, $cashConfig['config_data']->Iv, $inputArray);

        // curl post
        $curlData = array(
            'MerchantID_' => $cashConfig['config_data']->MerchantID,
            'PostData_'   => $spgatewayPostData,
            'Pos_'        => $pos,
        );

        // 記錄log
        $logId = $this->ci->log_payment_auth_model->insertLogPayment($paymentData, $curlData);

        $json       = curlPost($gateway, $curlData);
        $returnData = json_decode($json);

        // 狀態
        $status = $this->checkReturnData($paymentData, $returnData, __FUNCTION__);

        // 更新log
        $updateLogData = array(
            'trade_no'    => (isset($returnData->Result->TradeNo) && $returnData->Result->TradeNo) ? $returnData->Result->TradeNo : '',
            'status'      => $status,
            'return_data' => $json,
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
        $this->ci->auth_model->updateTransactionAuth($authId, $this->data);

        // 取得輸出 data
        $result = $this->ci->SpgatewayPaymentOutout->authOutput($this->action['action_code'], $postData, $this->data);

        // output log gateway
        $this->ci->SpgatewayPaymentLog->updatePaymentLog($paymentLogId, $result);

        return $result;
    }

    public function cancel(array $postData, array $responseData)
    {
        // 檢查 service code 是否為取消
        if ($this->option['option_group'] !== 'cancel') {
            return 'SYS_70005';
        }

        // 檢查 _Data 資料是否正確
        $postData['_Data'] = $this->ci->SpgatewayValid->cancel($postData['_Data']);
        if (!is_array($postData['_Data'])) {
            return $postData['_Data'];
        }

        // 檢查是否有這筆訂單
        $authData = $this->ci->auth_model->getSuccessAuthToCancel($postData);

        // MY_Controller::dumpData($authData, $this->ci->db->last_query());

        if (is_null($authData)) {
            return 'TRA_22000';
        }

        // 判斷是否已退過款
        if ($authData['cancel_status'] === '1') {
            return 'TRA_22002';
        }

        // 判斷是否從 tms 後台來的需求
        if (!isset($postData['_Data']['is_tms'])) {
            $postData['_Data']['is_tms'] = 0;
        } else {
            $postData['_Data']['is_tms'] = 1;
        }

        // 輸出用
        $authData['supplier'] = $this->supplier;

        // // 判斷金額是否正確
        // if (floatval($postData['_Data']['Amount']) !== floatval($authData['amount'])) {
        //     return '22001';
        // }

        // 訂單金額
        $postData['_Data']['Currency'] = $authData['currency'];
        $postData['_Data']['Amount']   = floatval($authData['amount']);

        // 更新訂單狀態為請退款中
        $this->ci->auth_model->updateAuthStatus('lock', $authData['idx'], 0);

        // 輸出用
        $postData['transactionNo'] = $authData['transaction_no'];

        // log gateway
        $paymentLogId = $this->ci->SpgatewayPaymentLog->insertPaymentLog(__FUNCTION__, $this->connectLogIdx, $postData);

        // insert request data
        $this->ci->load->model('cancel_model');
        $cancelIdx = $this->ci->cancel_model->insertCancel($authData['idx'], $postData);

        // 取得此商店在金流商的相關設定
        $cashConfig = $this->ci->cash_config_model->getCashConfigByTerminalIdxAndServiceIdx($this->terminal['idx'], $this->supplier['idx'], $this->product['idx']);
        if (is_null($cashConfig)) {
            return 'SYS_70002';
        }
        $cashConfig['config_data']       = json_decode($cashConfig['config_data']);
        $postData['_Data']['MerchantID'] = $cashConfig['config_data']->MerchantID;
        $postData['_Data']['Key']        = $cashConfig['config_data']->Key;
        $postData['_Data']['Iv']         = $cashConfig['config_data']->Iv;

        // 請款資料轉換
        $cancelData = $this->ci->spgatewayDataTransform->cancel($postData['_Data']);

        // 送去金流商做請款
        $cancelData['paymentLogId'] = $paymentLogId;
        $cancelData['supplierIdx']  = $this->supplier['idx'];

        // load request log model
        $this->ci->load->model('log_payment_cancel_model');

        //選擇版本
        // $merchantId = $data['MerchantID']; // 商店代號
        // $key        = $data['HashKey']; //商店代號的 Key 值
        // $iv         = $data['HashIV']; // 商店代號的 iv 值
        $gateway = $this->config['credit_cancel_url']; //取消授權閘道位址

        $inputArray = array(
            'RespondType'     => $cancelData['RespondType'],
            'TimeStamp'       => $cancelData['TimeStamp'], //時間戳記
            'Version'         => $cancelData['Version'], //串接程式版本
            'MerchantOrderNo' => (isset($cancelData['MerchantOrderNo'])) ? $cancelData['MerchantOrderNo'] : '', //商店訂單編號，同一店鋪中此編號不可重覆
            'TradeNo'         => (isset($cancelData['TradeNo'])) ? $cancelData['TradeNo'] : '',
            'IndexType'       => $cancelData['IndexType'],
            'Amt'             => intval($cancelData['Amt']), //訂單金額
            'NotifyURL'       => (!empty($cancelData['NotifyURL'])) ? $cancelData['NotifyURL'] : '',
        );
        // 加密函式
        $spgatewayPostData = $this->ci->SpgatewayCryptography->encryption($cashConfig['config_data']->Key, $cashConfig['config_data']->Iv, $inputArray);

        // curl post
        $curlData = array(
            'MerchantID_' => $cashConfig['config_data']->MerchantID,
            'PostData_'   => $spgatewayPostData,
        );

        // 記錄log
        $logId = $this->ci->log_payment_cancel_model->insertLogCancel($cancelData, $curlData);

        $json       = curlPost($gateway, $curlData);
        $returnData = json_decode($json);

        // 狀態
        $status = $this->checkReturnData($cancelData, $returnData, __FUNCTION__);

        // 更新log
        $updateLogData = array(
            'trade_no'    => (isset($returnData->Result->TradeNo) && $returnData->Result->TradeNo) ? $returnData->Result->TradeNo : '',
            'status'      => $status,
            'return_data' => $json,
            'ip'          => $_SERVER['REMOTE_ADDR'],
            'return_time' => date('Y-m-d H:i:s'),
        );
        $where = array('idx' => $logId);
        $this->ci->log_payment_cancel_model->update($updateLogData, $where);

        $this->data['success'] = true;
        $this->data['code']    = $this->ci->lang->line($status);
        $this->data['data']    = $returnData;

        // 更新請款資料
        $this->ci->cancel_model->updateCancelForResult($cancelIdx, $this->data);

        if ($this->data['data']->Status === 'SUCCESS') {
            // 更新訂單狀態為取消授權
            $this->ci->auth_model->updateAuthStatus('cancel', $authData['idx'], 1);
            // 更新訂單狀態為不去請款
            // $this->ci->auth_model->updateAuthStatus('toRequest', $authData['idx'], 0);
        }
        $this->ci->auth_model->updateAuthStatus('lock', $authData['idx'], 1);

        // 取得輸出 data
        $result = $this->ci->SpgatewayPaymentOutout->requestOutput($postData, $authData, $this->data);

        // output log gateway
        $paymentLogId = $this->ci->SpgatewayPaymentLog->updatePaymentLog($paymentLogId, $result);

        return $result;
    }

    public function request(array $postData, array $responseData)
    {
        // 判斷 postData 有無 OrderID，沒有需要從 responseData 中取得
        if ((!isset($postData['_Data']['OrderID']) || empty($postData['_Data']['OrderID'])) && isset($responseData['auth']['Result']['OrderID'])) {
            $postData['_Data']['OrderID'] = $responseData['auth']['Result']['OrderID'];
        }

        // 檢查 _Data 資料是否正確
        $postData['_Data'] = $this->ci->SpgatewayValid->request($postData['_Data']);
        if (!is_array($postData['_Data'])) {
            return $postData['_Data'];
        }

        // 檢查是否有這筆訂單
        $authData = $this->ci->auth_model->getSuccessAuthToRequest($postData);
        if (is_null($authData)) {
            return 'TRA_22100';
        } else if (floatval($authData['request_amount']) > 0) {
            return 'TRA_22102';
        }

        // 判斷是否從 tms 後台來的需求
        if (!isset($postData['_Data']['is_tms'])) {
            $postData['_Data']['is_tms'] = 0;
        } else {
            $postData['_Data']['is_tms'] = 1;
        }

        // 輸出用
        $authData['supplier'] = $this->supplier;

        // 更新訂單狀態為請退款中
        $this->ci->auth_model->updateAuthStatus('lock', $authData['idx'], 0);

        // 輸出用
        $postData['transactionNo'] = $authData['transaction_no'];

        // 檢查金額是否正確
        $requestAmount = floatval($authData['amount']) - floatval($postData['_Data']['Amount']);
        if ($requestAmount < 0) {
            return 'TRA_22101';
        }

        // log gateway
        $paymentLogId = $this->ci->SpgatewayPaymentLog->insertPaymentLog(__FUNCTION__, $this->connectLogIdx, $postData);

        // insert request data
        $this->ci->load->model('request_model');
        $requestIdx = $this->ci->request_model->insertRequest($authData['idx'], $postData);

        // 取得此商店在金流商的相關設定
        $cashConfig = $this->ci->cash_config_model->getCashConfigByTerminalIdxAndServiceIdx($this->terminal['idx'], $this->supplier['idx'], $this->product['idx']);
        if (is_null($cashConfig)) {
            return 'SYS_70002';
        }
        $cashConfig['config_data']       = json_decode($cashConfig['config_data']);
        $postData['_Data']['MerchantID'] = $cashConfig['config_data']->MerchantID;
        $postData['_Data']['Key']        = $cashConfig['config_data']->Key;
        $postData['_Data']['Iv']         = $cashConfig['config_data']->Iv;

        // 請款資料轉換
        $requestData = $this->ci->spgatewayDataTransform->request($postData['_Data']);

        // 送去金流商做請款
        $requestData['paymentLogId'] = $paymentLogId;
        $requestData['supplierIdx']  = $this->supplier['idx'];

        // load request log model
        $this->ci->load->model('log_payment_request_model');

        //選擇版本
        // $merchantId = $requestData['MerchantID']; // 商店代號
        // $key        = $requestData['HashKey']; //商店代號的 Key 值
        // $iv         = $requestData['HashIV']; // 商店代號的 iv 值
        $gateway = $this->config['credit_request_url']; //取消授權閘道位址

        $inputArray = array(
            'RespondType'     => $requestData['RespondType'],
            'TimeStamp'       => $requestData['TimeStamp'], //時間戳記
            'Version'         => $requestData['Version'], //串接程式版本
            'MerchantOrderNo' => (isset($requestData['MerchantOrderNo'])) ? $requestData['MerchantOrderNo'] : '', //商店訂單編號，同一店鋪中此編號不可重覆
            'TradeNo'         => (isset($requestData['TradeNo'])) ? $requestData['TradeNo'] : '',
            'IndexType'       => $requestData['IndexType'],
            'Amt'             => intval($requestData['Amt']), //訂單金額
            'NotifyURL'       => (!empty($requestData['NotifyURL'])) ? $requestData['NotifyURL'] : '',
            'CloseType'       => $requestData['CloseType'],
        );

        // 加密函式
        $spgatewayPostData = $this->ci->SpgatewayCryptography->encryption($cashConfig['config_data']->Key, $cashConfig['config_data']->Iv, $inputArray);

        // curl post
        $curlData = array(
            'MerchantID_' => $cashConfig['config_data']->MerchantID,
            'PostData_'   => $spgatewayPostData,
        );

        // 記錄log
        $logId = $this->ci->log_payment_request_model->insertLogRequest($requestData, $curlData);

        $json       = curlPost($gateway, $curlData);
        $returnData = json_decode($json);

        // 狀態
        $status = $this->checkReturnData($requestData, $returnData, __FUNCTION__);

        // 更新log
        $updateLogData = array(
            'trade_no'    => (isset($returnData->Result->TradeNo) && $returnData->Result->TradeNo) ? $returnData->Result->TradeNo : '',
            'status'      => $status,
            'return_data' => $json,
            'ip'          => $this->ci->input->ip_address(),
            'return_time' => date('Y-m-d H:i:s'),
        );
        $where = array('idx' => $logId);
        $this->ci->log_payment_request_model->update($updateLogData, $where);

        $this->data['success'] = true;
        $this->data['code']    = $this->ci->lang->line($status);
        $this->data['data']    = $returnData;

        // 更新請款資料
        $this->ci->request_model->updateRequsetForResult($requestIdx, $this->data);

        if ($this->data['data']->Status === 'SUCCESS') {
            // update auth status & balance
            $this->ci->auth_model->updateAmountByTypeAndAuthIdx('request', $authData, $this->data['data']->Result->Amt);
            $this->ci->auth_model->updateAuthStatus('toRequest', $authData['idx'], 0);
        }
        $this->ci->auth_model->updateAuthStatus('lock', $authData['idx'], 1);

        // 取得輸出 data
        $result = $this->ci->SpgatewayPaymentOutout->requestOutput($postData, $authData, $this->data);

        // output log gateway
        $paymentLogId = $this->ci->SpgatewayPaymentLog->updatePaymentLog($paymentLogId, $result);

        return $result;
    }

    public function requestToSign(array $postData, array $responseData)
    {
        // 判斷 postData 有無 OrderID，沒有需要從 responseData 中取得
        if ((!isset($postData['_Data']['OrderID']) || empty($postData['_Data']['OrderID'])) && isset($responseData['auth']['Result']['OrderID'])) {
            $postData['_Data']['OrderID'] = $responseData['auth']['Result']['OrderID'];
        }

        // 檢查 _Data 資料是否正確
        $postData['_Data'] = $this->ci->SpgatewayValid->request($postData['_Data']);
        if (!is_array($postData['_Data'])) {
            return $postData['_Data'];
        }

        // 檢查是否有這筆訂單
        $authData = $this->ci->auth_model->getSuccessAuthToRequest($postData);
        if (is_null($authData)) {
            return 'TRA_22100';
        } else if (floatval($authData['request_amount']) > 0) {
            return 'TRA_22102';
        }

        // 更新訂單狀態為請款中
        $this->ci->auth_model->updateAuthStatus('toRequest', $authData['idx'], 1);

        // 取得輸出 data
        $result = array(
            'Status' => 'PAYMENT_00000',
        );

        return $result;
    }

    public function refund(array $postData, array $responseData)
    {
        // 檢查 service code 是否為退款
        if ($this->option['option_group'] !== 'refund') {
            return 'SYS_70005';
        }

        // 檢查 _Data 資料是否正確
        $postData['_Data'] = $this->ci->SpgatewayValid->refund($postData['_Data']);
        if (!is_array($postData['_Data'])) {
            return $postData['_Data'];
        }

        // 檢查是否有這筆訂單
        $authData = $this->ci->auth_model->getSuccessAuthToRefund($postData);
        if (is_null($authData)) {
            return 'TRA_22200';
        }

        // 判斷是否從 tms 後台來的需求
        if (!isset($postData['_Data']['is_tms'])) {
            $postData['_Data']['is_tms'] = 0;
        } else {
            $postData['_Data']['is_tms'] = 1;
        }

        // 輸出用
        $authData['supplier'] = $this->supplier;

        // 判斷此筆訂單為一次付清 or 分期
        if ($authData['inst'] !== '0') {
            // 檢查金額是否正確
            if ($authData['refund_amount'] !== '0' || $postData['_Data']['Amount'] !== $authData['amount']) {
                return 'TRA_22101';
            }
        } else {
            // 檢查金額是否正確
            $refundAmount = floatval($authData['refund_amount']) + floatval($postData['_Data']['Amount']);
            $canRefund    = floatval($authData['request_amount']) - $refundAmount;
        }

        // MY_Controller::dumpData($refundAmount, $canRefund);

        if ($canRefund === 0) {
            return 'TRA_22202';
        } else if ($canRefund < 0) {
            return 'TRA_22201';
        }

        // 更新訂單狀態為請退款中
        $this->ci->auth_model->updateAuthStatus('lock', $authData['idx'], 0);

        // 輸出用
        $postData['transactionNo'] = $authData['transaction_no'];

        // log gateway
        $paymentLogId = $this->ci->SpgatewayPaymentLog->insertPaymentLog(__FUNCTION__, $this->connectLogIdx, $postData);

        // insert request data
        $this->ci->load->model('refund_model');
        $refundIdx = $this->ci->refund_model->insertRefund($authData['idx'], $postData);

        // 取得此商店在金流商的相關設定
        $cashConfig = $this->ci->cash_config_model->getCashConfigByTerminalIdxAndServiceIdx($this->terminal['idx'], $this->supplier['idx'], $this->product['idx']);
        if (is_null($cashConfig)) {
            return 'SYS_70002';
        }
        $cashConfig['config_data']       = json_decode($cashConfig['config_data']);
        $postData['_Data']['MerchantID'] = $cashConfig['config_data']->MerchantID;
        $postData['_Data']['Key']        = $cashConfig['config_data']->Key;
        $postData['_Data']['Iv']         = $cashConfig['config_data']->Iv;

        // 請款資料轉換
        $refundData = $this->ci->spgatewayDataTransform->refund($postData['_Data']);

        // MY_Controller::dumpData($this->data);

        // 送去金流商做請款
        $refundData['paymentLogId'] = $paymentLogId;
        $refundData['supplierIdx']  = $this->supplier['idx'];

        // load refund log model
        $this->ci->load->model('log_payment_refund_model');

        //選擇版本
        // $merchantId = $data['MerchantID']; // 商店代號
        // $key        = $data['HashKey']; //商店代號的 Key 值
        // $iv         = $data['HashIV']; // 商店代號的 iv 值
        $gateway = $this->config['credit_request_url']; //取消授權閘道位址

        $inputArray = array(
            'RespondType'     => $refundData['RespondType'],
            'TimeStamp'       => $refundData['TimeStamp'], //時間戳記
            'Version'         => $refundData['Version'], //串接程式版本
            'MerchantOrderNo' => (isset($refundData['MerchantOrderNo'])) ? $refundData['MerchantOrderNo'] : '', //商店訂單編號，同一店鋪中此編號不可重覆
            'TradeNo'         => (isset($refundData['TradeNo'])) ? $refundData['TradeNo'] : '',
            'IndexType'       => $refundData['IndexType'],
            'Amt'             => intval($refundData['Amt']), //訂單金額
            'NotifyURL'       => (!empty($refundData['NotifyURL'])) ? $refundData['NotifyURL'] : '',
            'CloseType'       => $refundData['CloseType'],
        );
        // 加密函式
        $spgatewayPostData = $this->ci->SpgatewayCryptography->encryption($cashConfig['config_data']->Key, $cashConfig['config_data']->Iv, $inputArray);

        // curl post
        $curlData = array(
            'MerchantID_' => $cashConfig['config_data']->MerchantID,
            'PostData_'   => $spgatewayPostData,
        );

        // 記錄log
        $logId = $this->ci->log_payment_refund_model->insertLogRefund($refundData, $curlData);

        $json       = curlPost($gateway, $curlData);
        $returnData = json_decode($json);

        // 狀態
        $status = $this->checkReturnData($refundData, $returnData, __FUNCTION__);

        // 更新log
        $updateLogData = array(
            'trade_no'    => (isset($returnData->Result->TradeNo) && $returnData->Result->TradeNo) ? $returnData->Result->TradeNo : '',
            'status'      => $status,
            'return_data' => $json,
            'ip'          => $_SERVER['REMOTE_ADDR'],
            'return_time' => date('Y-m-d H:i:s'),
        );
        $where = array('idx' => $logId);
        $this->ci->log_payment_refund_model->update($updateLogData, $where);

        $this->data['success']     = true;
        $this->data['code']        = $this->ci->lang->line($status);
        $this->data['data']        = $returnData;
        $this->data['gateway']     = $this->supplier['supplier_code'];
        $this->data['gatewayName'] = $this->supplier['supplier_name'];

        // 更新請款資料
        $this->ci->refund_model->updateRefundForResult($refundIdx, $this->data);

        if ($this->data['data']->Status === 'SUCCESS') {
            // update auth status & balance
            $this->ci->auth_model->updateAmountByTypeAndAuthIdx('refund', $authData, $this->data['data']->Result->Amt);
        }
        $this->ci->auth_model->updateAuthStatus('lock', $authData['idx'], 1);

        $result = $this->ci->SpgatewayPaymentOutout->requestOutput($postData, $authData, $this->data);

        // output log gateway
        $paymentLogId = $this->ci->SpgatewayPaymentLog->updatePaymentLog($paymentLogId, $result);

        return $result;
    }

    public function query(array $postData, array $responseData)
    {
        // 檢查 service code 是否為查詢
        if ($this->option['option_group'] !== 'query') {
            return 'SYS_70005';
        }

        // 檢查 _Data 資料是否正確
        $postData['_Data'] = $this->ci->SpgatewayValid->query($postData['_Data']);
        if (!is_array($postData['_Data'])) {
            return $postData['_Data'];
        }

        // log gateway
        $this->data['paymentLogId'] = $this->ci->SpgatewayPaymentLog->insertPaymentLog(__FUNCTION__, $this->connectLogIdx, $postData);

        // query transaction record
        $queryData = $this->ci->auth_model->getAuthForPaymentQuery($this->supplier['idx'], $this->product['idx'], $postData);

        // MY_Controller::dumpData($queryData);

        if (count($queryData) !== 0) {
            // load output data library
            $result = $this->ci->SpgatewayPaymentOutout->queryOutput($this->action, $queryData);
        } else {
            return 'TRA_22301';
        }

        // MY_Controller::dumpData($result);

        // output log gateway
        $this->ci->SpgatewayPaymentLog->updatePaymentLog($this->data['paymentLogId'], $result);

        return $result;
    }

    public function queryToUpdate(array $postData, array $responseDat)
    {
        // 檢查 _Data 資料是否正確
        $postData['_Data'] = $this->ci->SpgatewayValid->queryToUpdate($postData);
        if (!is_array($postData['_Data'])) {
            return $postData['_Data'];
        }

        // get app by app name
        $app = $this->ci->edc_app_mapping_model->getAppDetailByEdcSetIdxAndAppName($this->terminal['edc_set_idx'], $postData['_Data']['AppName']);
        if (!$app) {
            return 'EDC_90008';
        }
        $postData['_Data']['AppVersion'] = $app['app_version'];

        // 檢查是否有這筆訂單
        $authData = $this->ci->auth_model->getSuccessAuthToRequest($postData);
        if (is_null($authData)) {
            return 'TRA_22100';
        } else if (floatval($authData['request_amount']) > 0) {
            return 'TRA_22102';
        }

        // 判斷是否從 tms 後台來的需求
        if (!isset($postData['_Data']['is_tms'])) {
            $postData['_Data']['is_tms'] = 0;
        } else {
            $postData['_Data']['is_tms'] = 1;
        }

        // 輸出用
        $authData['supplier'] = $this->supplier;

        // log gateway
        $paymentLogIdx = $this->ci->SpgatewayPaymentLog->insertPaymentLog(__FUNCTION__, $this->connectLogIdx, $postData);

        // 更新訂單狀態為請退款中
        $this->ci->auth_model->updateAuthStatus('lock', $authData['idx'], 0);

        // 輸出用
        $postData['transactionNo'] = $authData['transaction_no'];

        // insert query data
        $this->ci->load->model('query_model');
        $queryIdx = $this->ci->query_model->insertQuery($authData['idx'], $postData);

        // 取得此商店在金流商的相關設定
        $cashConfig = $this->ci->cash_config_model->getCashConfigByTerminalIdxAndServiceIdx($this->terminal['idx'], $this->supplier['idx'], $this->product['idx']);
        if (is_null($cashConfig)) {
            return 'SYS_70002';
        }
        $cashConfig['config_data']       = json_decode($cashConfig['config_data']);
        $postData['_Data']['MerchantID'] = $cashConfig['config_data']->MerchantID;
        $postData['_Data']['Key']        = $cashConfig['config_data']->Key;
        $postData['_Data']['Iv']         = $cashConfig['config_data']->Iv;

        // 資料轉換
        $queryData = $this->ci->spgatewayDataTransform->queryToUpdate($postData['_Data']);

        // 將資料 post 給金流商做交易
        $queryData['paymentLogId'] = $paymentLogIdx;
        $queryData['supplierIdx']  = $this->supplier['idx'];

        // load log model
        $this->ci->load->model('log_payment_query_model');

        //選擇版本
        $gateway = $this->config['query_trade_info_url']; //授權閘道位址

        $inputArray = array(
            'RespondType'     => 'JSON',
            'MerchantID'      => $postData['_Data']['MerchantID'],
            'Version'         => $queryData['Version'],
            'TimeStamp'       => $queryData['TimeStamp'],
            'MerchantOrderNo' => $queryData['MerchantOrderNo'],
            'Amt'             => intval($queryData['Amt']),
        );

        $checkArr = array(
            'Amt'             => intval($queryData['Amt']),
            'MerchantID'      => $postData['_Data']['MerchantID'],
            'MerchantOrderNo' => $queryData['MerchantOrderNo'],
        );
        // check value
        ksort($checkArr);
        $checkStr                 = 'IV=' . $postData['_Data']['Iv'] . '&' . http_build_query($checkArr) . '&Key=' . $postData['_Data']['Key'];
        $inputArray['CheckValue'] = strtoupper(hash("sha256", $checkStr));

        // MY_Controller::dumpData($inputArray, $checkStr);

        // 記錄log
        $logId      = $this->ci->log_payment_query_model->insertLogQuery($queryData, $inputArray);
        $json       = curlPost($gateway, $inputArray);
        $returnData = json_decode($json);

        // 狀態
        $status = $this->checkReturnData($queryData, $returnData, __FUNCTION__);

        // 更新log
        $updateLogData = array(
            'trade_no'    => (isset($returnData->Result->TradeNo) && $returnData->Result->TradeNo) ? $returnData->Result->TradeNo : '',
            'status'      => $status,
            'return_data' => $json,
            'ip'          => $this->ci->input->ip_address(),
            'return_time' => date('Y-m-d H:i:s'),
        );
        $where = array('idx' => $logId);
        $this->ci->log_payment_query_model->update($updateLogData, $where);

        $this->data['success']     = true;
        $this->data['code']        = $this->ci->lang->line('QUERY_' . $status);
        $this->data['data']        = $returnData;
        $this->data['gateway']     = $this->supplier['supplier_code'];
        $this->data['gatewayName'] = $this->supplier['supplier_name'];

        if ($this->data['data']->Result->CloseStatus === '3') {
            // update auth status & balance
            $this->ci->auth_model->updateAmountByTypeAndAuthIdx('request', $authData, $this->data['data']->Result->CloseAmt);
            $this->ci->auth_model->updateAuthStatus('toRequest', $authData['idx'], 0);
            $this->ci->auth_model->updateAuthStatus('request', $authData['idx'], 1);
        }
        $this->ci->auth_model->updateAuthStatus('lock', $authData['idx'], 1);

        // 更新查詢資料
        $this->ci->query_model->updateQuery($queryIdx, $this->data);

        // 取得輸出 data
        $result = $this->ci->SpgatewayPaymentOutout->requestOutput($postData, $authData, $this->data);

        // output log gateway
        $this->ci->SpgatewayPaymentLog->updatePaymentLog($paymentLogIdx, $result);

        return $result;
    }

    /**
     * 檢查回來的結果
     * @param  [array] $data       [原始要post的資料]
     * @param  [array] $returnData [送spgateway回來的json decode資料]
     * @param  [string] $method     [那種付費方式]
     * @return [string]             [交易狀態]
     */
    protected function checkReturnData($data, $returnData, $method)
    {
        $status = (isset($returnData->Status) && $returnData->Status) ? $returnData->Status : 'CHECK_FAIL';

        if (!empty($data) && !empty($returnData) && !empty($method)) {
            // check return data
            if (is_object($returnData) && isset($returnData->Status)) {
                switch ($method) {
                    case 'value':
                        break;
                    default:
                        if ($returnData->Status === 'SUCCESS' && isset($returnData->Result->CheckCode) && isset($returnData->Result->Amt)) {
                            //組CheckCode
                            $checkData = array(
                                'Amt'             => (int) $data['Amt'],
                                'MerchantID'      => $data['MerchantID'],
                                'MerchantOrderNo' => $data['MerchantOrderNo'],
                                'TradeNo'         => $returnData->Result->TradeNo,
                            );
                            ksort($checkData);
                            $checkStr     = http_build_query($checkData);
                            $checkCodeStr = 'HashIV=' . $data['HashIV'] . '&' . $checkStr . '&HashKey=' . $data['HashKey'];
                            $checkCode    = strtoupper(hash('sha256', $checkCodeStr));

                            // 驗證CheckCode、金額、訂單編號
                            if ($returnData->Result->CheckCode != $checkCode || $data['Amt'] != $returnData->Result->Amt || $data['MerchantOrderNo'] != $returnData->Result->MerchantOrderNo || $data['MerchantID'] != $returnData->Result->MerchantID) {
                                //比對有問題
                                $status = 'CHECK_FAIL';
                            } else {
                                $status = $returnData->Status;
                            }
                        }
                        break;
                }
            }
        }
        return $status;
    }
}
