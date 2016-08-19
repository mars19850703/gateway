<?php

class Alipay extends BaseModule
{
    // module path
    protected $modulePath;
    // pay2go config
    protected $config;

    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        $this->ci->load->helper(array('common'));
        $this->ci->lang->load('alipay', 'zh');

        // load model
        $this->ci->load->model('auth_model');
        $this->ci->load->model('cash_config_model');
        $this->ci->load->model('edc_app_mapping_model');

        // load config
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            include dirname(__FILE__) . '\Config.php';
        } else {
            include dirname(__FILE__) . '/Config.php';
        }
        $this->config = $alipay;

        // set module path
        $this->modulePath = '1.0/taishin/alipay/';
        // load valid
        $this->ci->load->library($this->modulePath . 'alipay_valid', null, 'alipayValid');
        // 載入資料轉換 library
        $this->ci->load->library($this->modulePath . 'alipay_data', null, 'alipayDataTransform');
        // load log gateway library
        $this->ci->load->library($this->modulePath . 'alipay_log', null, 'alipayLog');
        // load sign
        $this->ci->load->library($this->modulePath . 'alipay_sign', null, 'alipaySign');
        // load output
        $this->ci->load->library($this->modulePath . 'alipay_output', null, 'alipayOutput');
    }

    public function auth(array $postData, array $responseData)
    {
        // 檢查 service code 是否為退款
        if ($this->option['option_group'] !== 'auth') {
            return 'SYS_70005';
        }

        // 檢查 _Data 資料是否正確
        $postData['_Data'] = $this->ci->alipayValid->{__FUNCTION__}($postData);
        if (!is_array($postData['_Data'])) {
            return $postData['_Data'];
        }

        // get app by app name
        $app = $this->ci->edc_app_mapping_model->getAppDetailByEdcSetIdxAndAppName($this->terminal['edc_set_idx'], $postData['_Data']['AppName']);
        if (!$app) {
            return 'EDC_90008';
        }
        $postData['_Data']['AppVersion'] = $app['app_version'];

        // 檢查同一商店訂單編號是否重複
        $authData = $this->ci->auth_model->getAuthByMerchantIdAndOrderId($postData['MerchantID'], $postData['_Data']['OrderID']);
        if (!is_null($authData)) {
            return 'TRA_20004';
        }

        // 產出這次交易代碼
        $this->ci->load->library($this->modulePath . 'alipay_random', null, 'alipayRandom');
        $postData['transactionNo'] = $this->ci->alipayRandom->generateTransactionNo();

        // log gateway
        $paymentLogIdx = $this->ci->alipayLog->insertPaymentLog(__FUNCTION__, $this->connectLogIdx, $postData);

        // insert auth data
        $authIdx = $this->ci->auth_model->insertAlipay($paymentLogIdx, $this->merchant, $this->edc, $postData);

        // 取得此商店在金流商的相關設定
        $this->cashConfig = $this->ci->cash_config_model->getCashConfigByTerminalIdxAndServiceIdx($this->terminal['idx'], $this->supplier['idx'], $this->product['idx']);
        if (is_null($this->cashConfig)) {
            return 'SYS_70002';
        }
        $this->cashConfig['config_data'] = json_decode($this->cashConfig['config_data']);

        // 資料轉換
        $paymentData = $this->ci->alipayDataTransform->{__FUNCTION__}($postData['_Data']);

        // 將資料 post 給金流商做交易
        $paymentData['paymentLogIdx'] = $paymentLogIdx;
        $paymentData['supplierIdx']   = $this->supplier['idx'];
        $paymentData['MerchantID']    = $this->cashConfig['config_data']->MerchantID;

        // load log model
        $this->ci->load->model('log_payment_auth_model');

        $inputData = array(
            'amount'     => $paymentData['amount'],
            'barcode'    => $paymentData['barcode'],
            'gw'         => $paymentData['gw'],
            'merchantid' => $paymentData['MerchantID'],
            'orderid'    => $paymentData['orderid'],
            'ordername'  => $paymentData['ordername'],
            'ordermemo'  => $paymentData['ordermemo'],
            'storeid'    => $this->cashConfig['config_data']->ReverseStore,
            'terminalid' => $postData['TerminalID'],
            'timestamp'  => $paymentData['timestamp'],
        );

        // 取得簽章
        $inputData['sign'] = $this->ci->alipaySign->getSign($this->cashConfig['config_data'], $inputData);
        // 記錄log
        $logIdx = $this->ci->log_payment_auth_model->insertAlipayLogPayment($postData, $paymentData, $inputData);
        // 送至台新付款
        $url = $this->config['paymentUrl'] . '?' . $this->buildQueryString($inputData);

        // 付款結果
        $returnStr  = urldecode(file_get_contents($url));
        $returnData = @simplexml_load_string($returnStr);

        if (is_object($returnData)) {
            $returnData                 = json_decode(json_encode($returnData));
            $returnData->orderid        = is_object($returnData->orderid) ? '' : $returnData->orderid;
            $returnData->return_message = is_object($returnData->return_message) ? '' : $returnData->return_message;
            // 狀態
            if (isset($returnData->sign)) {
                $status = $this->checkReturnData($paymentData, $returnData, __FUNCTION__);
            } else {
                $status = $returnData->return_code;
            }
        } else {
            return 'ALIPAY_10004';
        }

        // 更新log
        $updateLogData = array(
            'trade_no'    => (isset($returnData->orderid) && $returnData->orderid) ? $returnData->orderid : '',
            'status'      => $status,
            'return_data' => $returnStr,
            'ip'          => $_SERVER['REMOTE_ADDR'],
            'return_time' => date('Y-m-d H:i:s'),
        );
        $where = array('idx' => $logIdx);
        $this->ci->log_payment_auth_model->update($updateLogData, $where);

        // 輸出
        $this->data['success']     = true;
        $this->data['code']        = $this->ci->lang->line('ALIPAY_' . $status);
        $this->data['data']        = $returnData;
        $this->data['gateway']     = $this->supplier['supplier_code'];
        $this->data['gatewayName'] = $this->supplier['supplier_name'];

        // 更新授權資料
        $this->ci->auth_model->updateAlipayTransactionAuth($authIdx, $this->data);
        // 取得輸出 data
        $result = $this->ci->alipayOutput->authOutput($postData, $this->data);
        // output log gateway
        $this->ci->alipayLog->updatePaymentLog($paymentLogIdx, $result);

        return $result;
    }

    public function refund(array $postData, array $responseData)
    {
        // 檢查 service code 是否為退款
        if ($this->option['option_group'] !== 'refund') {
            return 'SYS_70005';
        }

        // 檢查 _Data 資料是否正確
        $postData['_Data'] = $this->ci->alipayValid->{__FUNCTION__}($postData);
        if (!is_array($postData['_Data'])) {
            return $postData['_Data'];
        }

        // 檢查是否有這筆訂單
        $authData = $this->ci->auth_model->getSuccessAuthToRefund($postData);
        if (is_null($authData) || $authData['refund_status'] === '1') {
            return 'TRA_22200';
        }

        // 判斷是否從 tms 後台來的需求
        if (!isset($postData['_Data']['is_tms'])) {
            $postData['_Data']['is_tms'] = 0;
        } else {
            $postData['_Data']['is_tms'] = 1;
        }

        // 輸出用
        $authData['supplier']        = $this->supplier;
        $postData['_Data']['Amount'] = intval($authData['amount']);
        $postData['transactionNo']   = $authData['transaction_no'];

        // 判斷金額是否正確
        if (floatval($postData['_Data']['Amount']) !== floatval($authData['amount'])) {
            return 'TRA_22201';
        }

        // 更新訂單狀態為請退款中
        $this->ci->auth_model->updateAuthStatus('lock', $authData['idx'], 0);
        // log gateway
        $paymentLogIdx = $this->ci->alipayLog->insertPaymentLog(__FUNCTION__, $this->connectLogIdx, $postData);
        // insert request data
        $this->ci->load->model('refund_model');
        $refundIdx = $this->ci->refund_model->insertRefund($authData['idx'], $postData);
        // 取得此商店在金流商的相關設定
        $this->cashConfig = $this->ci->cash_config_model->getCashConfigByTerminalIdxAndServiceIdx($this->terminal['idx'], $this->supplier['idx'], $this->product['idx']);
        if (is_null($this->cashConfig)) {
            return 'SYS_70002';
        }
        $this->cashConfig['config_data'] = json_decode($this->cashConfig['config_data']);

        // 資料轉換
        $refundData = $this->ci->alipayDataTransform->{__FUNCTION__}($postData['_Data']);

        // 將資料 post 給金流商做交易
        $refundData['paymentLogIdx'] = $paymentLogIdx;
        $refundData['supplierIdx']   = $this->supplier['idx'];
        $refundData['MerchantID']    = $this->cashConfig['config_data']->MerchantID;

        // load refund log model
        $this->ci->load->model('log_payment_refund_model');

        $inputData = array(
            'amount'       => $refundData['amount'],
            'gw'           => $refundData['gw'],
            'merchantid'   => $refundData['MerchantID'],
            'orderid'      => $refundData['orderid'],
            'refundid'     => $refundData['refundid'],
            'refundreason' => $refundData['refundreason'],
            'storeid'      => $this->cashConfig['config_data']->ReverseStore,
            'terminalid'   => $postData['TerminalID'],
            'timestamp'    => $refundData['timestamp'],
        );
        // 取得簽章
        $inputData['sign'] = $this->ci->alipaySign->getSign($this->cashConfig['config_data'], $inputData);

        // 記錄log
        $logIdx = $this->ci->log_payment_refund_model->insertAlipayLogRefund($postData, $refundData, $inputData);
        // 送至台新付款
        $url = $this->config['refundUrl'] . '?' . $this->buildQueryString($inputData);

        // 退款結果
        $returnStr  = urldecode(file_get_contents($url));
        $returnData = @simplexml_load_string($returnStr);

        if (is_object($returnData)) {
            $returnData                 = json_decode(json_encode($returnData));
            $returnData->orderid        = is_object($returnData->orderid) ? '' : $returnData->orderid;
            $returnData->return_message = is_object($returnData->return_message) ? '' : $returnData->return_message;
            // 狀態
            if (isset($returnData->sign)) {
                $status = $this->checkReturnData($refundData, $returnData, __FUNCTION__);
            } else {
                $status = $returnData->return_code;
            }
        } else {
            return 'ALIPAY_10004';
        }

        // 狀態
        $status = $this->checkReturnData($refundData, $returnData, __FUNCTION__);
        // 更新log
        $updateLogData = array(
            'trade_no'    => (isset($returnData->orderid) && $returnData->orderid) ? $returnData->orderid : '',
            'status'      => $status,
            'return_data' => $returnStr,
            'ip'          => $_SERVER['REMOTE_ADDR'],
            'return_time' => date('Y-m-d H:i:s'),
        );
        $where = array('idx' => $logIdx);
        $this->ci->log_payment_refund_model->update($updateLogData, $where);

        // 輸出
        $this->data['success']     = true;
        $this->data['code']        = $this->ci->lang->line('ALIPAY_' . $status);
        $this->data['data']        = $returnData;
        $this->data['gateway']     = $this->supplier['supplier_code'];
        $this->data['gatewayName'] = $this->supplier['supplier_name'];

        // 更新請款資料
        $this->ci->refund_model->updateAlipayRefundForResult($refundIdx, $this->data);
        if ($status === '000') {
            // update auth status & balance
            $this->ci->auth_model->updateAmountByTypeAndAuthIdx('refund', $authData, $this->data['data']->totalrefundamount);
        }
        // 更新訂單狀態為處理結束
        $this->ci->auth_model->updateAuthStatus('lock', $authData['idx'], 1);

        // 取得輸出 data
        $result = $this->ci->alipayOutput->refundOutput($postData, $authData, $this->data);
        // output log gateway
        $this->ci->alipayLog->updatePaymentLog($paymentLogIdx, $result);

        return $result;
    }

    public function query(array $postData, array $responseData)
    {
        // 檢查 service code 是否為退款
        if ($this->option['option_group'] !== 'query') {
            return 'SYS_70005';
        }

        // 檢查 _Data 資料是否正確
        $postData['_Data'] = $this->ci->alipayValid->{__FUNCTION__}($postData);
        if (!is_array($postData['_Data'])) {
            return $postData['_Data'];
        }

        // 檢查是否有這筆訂單
        $authData = $this->ci->auth_model->getAuthForPaymentQuery($this->supplier['idx'], $this->product['idx'], $postData);
        if (empty($authData)) {
            return 'TRA_22301';
        }

        // 更新訂單狀態為請退款中
        $this->ci->auth_model->updateAuthStatus('lock', $authData[0]['idx'], 0);

        // 取得此商店在金流商的相關設定
        $this->cashConfig = $this->ci->cash_config_model->getCashConfigByTerminalIdxAndServiceIdx($this->terminal['idx'], $this->supplier['idx'], $this->product['idx']);
        if (is_null($this->cashConfig)) {
            return 'SYS_70002';
        }
        $this->cashConfig['config_data'] = json_decode($this->cashConfig['config_data']);

        // 資料轉換
        $data = $this->ci->alipayDataTransform->{__FUNCTION__}($postData['_Data']);

        // check data 需要使用金額
        $data['amount']  = intval($authData[0]['amount']);
        $data['orderid'] = $authData[0]['order_id'];

        // log gateway
        $paymentLogIdx = $this->ci->alipayLog->insertPaymentLog(__FUNCTION__, $this->connectLogIdx, $postData);

        // 將資料 post 給金流商做交易
        $data['paymentLogIdx'] = $paymentLogIdx;
        $data['supplierIdx']   = $this->supplier['idx'];
        $data['MerchantID']    = $this->cashConfig['config_data']->MerchantID;

        // load query log model
        $this->ci->load->model('log_payment_query_model');

        $type = array(
            'P',
            'R',
        );
        foreach ($type as $t) {
            $inputData = array(
                'gw'         => $data['gw'],
                'merchantid' => $data['MerchantID'],
                'id'         => $data['id'],
                'storeid'    => $this->cashConfig['config_data']->ReverseStore,
                'terminalid' => $postData['TerminalID'],
                'timestamp'  => $data['timestamp'],
                // 'type'       => $data['type'],
                'type'       => $t,
            );
            // 取得簽章
            $inputData['sign'] = $this->ci->alipaySign->getSign($this->cashConfig['config_data'], $inputData);

            // 記錄log
            $logIdx = $this->ci->log_payment_query_model->insertAlipayLogQuery($postData, $data, $inputData);
            // 送至台新查詢
            $url = $this->config['queryUrl'] . '?' . $this->buildQueryString($inputData);

            // MY_Controller::dumpData($url);

            // 查詢結果
            $returnStr  = urldecode(file_get_contents($url));
            $returnData = @simplexml_load_string($returnStr);
            if (is_object($returnData)) {
                $returnData                 = json_decode(json_encode($returnData));
                $returnData->orderid        = is_object($returnData->orderid) ? '' : $returnData->orderid;
                $returnData->return_message = is_object($returnData->return_message) ? '' : $returnData->return_message;
                // 狀態
                if (isset($returnData->sign)) {
                    $status = $this->checkReturnData($data, $returnData, __FUNCTION__);
                } else {
                    $status = $returnData->return_code;
                }
            } else {
                return 'ALIPAY_10004';
            }

            // 更新log
            $updateLogData = array(
                'status'      => $status,
                'return_data' => $returnStr,
                'ip'          => $_SERVER['REMOTE_ADDR'],
                'return_time' => date('Y-m-d H:i:s'),
            );
            $where = array('idx' => $logIdx);
            $this->ci->log_payment_query_model->update($updateLogData, $where);

            if (!empty($returnData->orderid)) {
                // 更新訂單狀態
                if ($this->ci->auth_model->updateAlipayOrderStatus($t, $authData[0]['idx'], $returnData)) {
                    $this->ci->auth_model->updateAuthStatus('lock', $authData[0]['idx'], 1);
                }
            }
        }

        $queryData = $this->ci->auth_model->getAuthForPaymentQuery($this->supplier['idx'], $this->product['idx'], $postData);

        // load output data library
        $result = $this->ci->alipayOutput->queryOutput($this->action, $queryData);
        // output log gateway
        $this->ci->alipayLog->updatePaymentLog($paymentLogIdx, $result);

        return $result;
    }

    protected function buildQueryString($data)
    {
        $str    = '';
        $encode = array(
            'ordername',
            'ordermemo',
        );
        foreach ($data as $key => $value) {
            if (in_array($key, $encode)) {
                $str .= $key . '=' . urlencode($value) . '&';
            } else {
                $str .= $key . '=' . $value . '&';
            }
        }

        return substr($str, 0, -1);
    }

    /**
     * 檢查回來的結果
     * @param  [array] $data       [原始要post的資料]
     * @param  [array] $returnData [送pay2go回來的json decode資料]
     * @param  [string] $method     [那種付費方式]
     * @return [string]             [交易狀態]
     */
    protected function checkReturnData($data, $returnData, $method)
    {
        // MY_Controller::dumpData(json_decode(json_encode($returnData), true));

        $returnData = json_decode(json_encode($returnData), true);
        if (!empty($data) && !empty($returnData) && !empty($method)) {
            switch ($method) {
                case 'auth':
                    $checkData = array(
                        'gw'             => (empty($returnData['gw']) ? '' : $returnData['gw']),
                        'merchantid'     => (empty($returnData['merchantid']) ? '' : $returnData['merchantid']),
                        'storeid'        => (empty($returnData['storeid']) ? '' : $returnData['storeid']),
                        'terminalid'     => (empty($returnData['terminalid']) ? '' : $returnData['terminalid']),
                        'orderid'        => (empty($returnData['orderid']) ? '' : $returnData['orderid']),
                        'amount'         => (empty($returnData['amount']) ? '0' : $returnData['amount']),
                        'timestamp'      => (empty($returnData['timestamp']) ? '' : $returnData['timestamp']),
                        'return_code'    => (empty($returnData['return_code']) ? '' : $returnData['return_code']),
                        'return_message' => (empty($returnData['return_message']) ? '' : $returnData['return_message']),
                    );
                    break;
                case 'refund':
                    $checkData = array(
                        'gw'                => (empty($returnData['gw']) ? '' : $returnData['gw']),
                        'merchantid'        => (empty($returnData['merchantid']) ? '' : $returnData['merchantid']),
                        'storeid'           => (empty($returnData['storeid']) ? '' : $returnData['storeid']),
                        'terminalid'        => (empty($returnData['terminalid']) ? '' : $returnData['terminalid']),
                        'orderid'           => (empty($returnData['orderid']) ? '' : $returnData['orderid']),
                        'refundid'          => (empty($returnData['refundid']) ? '' : $returnData['refundid']),
                        'payamount'         => (empty($returnData['payamount']) ? '0' : $returnData['payamount']),
                        'refundamount'      => (empty($returnData['refundamount']) ? '0' : $returnData['refundamount']),
                        'totalpayamount'    => (empty($returnData['totalpayamount']) ? '0' : $returnData['totalpayamount']),
                        'totalrefundamount' => (empty($returnData['totalrefundamount']) ? '0' : $returnData['totalrefundamount']),
                        'timestamp'         => (empty($returnData['timestamp']) ? '' : $returnData['timestamp']),
                        'return_code'       => (empty($returnData['return_code']) ? '' : $returnData['return_code']),
                        'return_message'    => (empty($returnData['return_message']) ? '' : $returnData['return_message']),
                    );
                    $returnData['amount'] = $returnData['payamount'];
                    break;
                case 'query':
                    $checkData = array(
                        'gw'                => (empty($returnData['gw']) ? '' : $returnData['gw']),
                        'merchantid'        => (empty($returnData['merchantid']) ? '' : $returnData['merchantid']),
                        'storeid'           => (empty($returnData['storeid']) ? '' : $returnData['storeid']),
                        'terminalid'        => (empty($returnData['terminalid']) ? '' : $returnData['terminalid']),
                        'paytype'           => (empty($returnData['paytype']) ? '' : $returnData['paytype']),
                        'orderid'           => (empty($returnData['orderid']) ? '' : $returnData['orderid']),
                        'refundid'          => (empty($returnData['refundid']) ? '' : $returnData['refundid']),
                        'amount'            => (empty($returnData['amount']) ? '0' : $returnData['amount']),
                        'buyeremail'        => (empty($returnData['buyeremail']) ? '' : $returnData['buyeremail']),
                        'status'            => (empty($returnData['status']) ? '' : $returnData['status']),
                        'transtime'         => (empty($returnData['transtime']) ? '' : $returnData['transtime']),
                        'appropriationtime' => (empty($returnData['appropriationtime']) ? '' : $returnData['appropriationtime']),
                        'timestamp'         => (empty($returnData['timestamp']) ? '' : $returnData['timestamp']),
                        'return_code'       => (empty($returnData['return_code']) ? '' : $returnData['return_code']),
                        'return_message'    => (empty($returnData['return_message']) ? '' : $returnData['return_message']),
                    );
                    break;
                default:
                    $checkData = array();
                    break;
            }
            ksort($checkData);
            // 取得簽章
            $sign = $this->ci->alipaySign->getSign($this->cashConfig['config_data'], $checkData);

            // MY_Controller::dumpData($returnData, $checkData, $returnData['sign'], $sign, $returnData['amount'], $data['amount'], $returnData['orderid'], $data['orderid'], $returnData['merchantid'], $data['MerchantID']);

            if ($returnData['sign'] == $sign && $returnData['amount'] == $data['amount'] && $returnData['orderid'] == $data['orderid'] && $returnData['merchantid'] == $data['MerchantID']) {
                return $returnData['return_code'];
            } else {
                return 'CHECK_FAIL';
            }
        } else {
            return 'CHECK_FAIL';
        }
    }
}
