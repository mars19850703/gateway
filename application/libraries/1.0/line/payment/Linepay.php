<?php

class Linepay extends BaseModule
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
        $this->ci->lang->load('line', 'zh');

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
        $this->config = $line;

        // set module path
        $this->modulePath = '1.0/line/payment/';
        // load valid
        $this->ci->load->library($this->modulePath . 'linepay_valid', null, 'linepayValid');
        // 載入資料轉換 library
        $this->ci->load->library($this->modulePath . 'linepay_data', null, 'linepayDataTransform');
        // load log gateway library
        $this->ci->load->library($this->modulePath . 'linepay_log', null, 'linepayLog');
        // load output
        $this->ci->load->library($this->modulePath . 'linepay_output', null, 'linepayOutput');
    }

    public function auth(array $postData, array $responseData)
    {
        // 檢查 service code 是否為退款
        if ($this->option['option_group'] !== __FUNCTION__) {
            return 'SYS_70005';
        }

        // 檢查 _Data 資料是否正確
        $postData['_Data'] = $this->ci->linepayValid->{__FUNCTION__}($postData);
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
        $this->ci->load->library($this->modulePath . 'linepay_random', null, 'linepayRandom');
        $postData['transactionNo'] = $this->ci->linepayRandom->generateTransactionNo();

        // log gateway
        $paymentLogIdx = $this->ci->linepayLog->insertPaymentLog(__FUNCTION__, $this->connectLogIdx, $postData);

        // insert auth data
        $authIdx = $this->ci->auth_model->insertLinepay($paymentLogIdx, $this->merchant, $this->edc, $postData);

        // 取得此商店在金流商的相關設定
        $this->cashConfig = $this->ci->cash_config_model->getCashConfigByTerminalIdxAndServiceIdx($this->terminal['idx'], $this->supplier['idx'], $this->product['idx']);
        if (is_null($this->cashConfig)) {
            return 'SYS_70002';
        }
        $this->cashConfig['config_data'] = json_decode($this->cashConfig['config_data']);

        // 資料轉換
        $paymentData = $this->ci->linepayDataTransform->{__FUNCTION__}($postData['_Data']);

        // 將資料 post 給金流商做交易
        $paymentData['paymentLogIdx'] = $paymentLogIdx;
        $paymentData['supplierIdx']   = $this->supplier['idx'];
        $paymentData['MerchantID']    = $this->cashConfig['config_data']->MerchantID;

        $inputData = array(
            'productName' => $paymentData['productName'],
            'amount'      => $paymentData['amount'],
            'currency'    => $paymentData['currency'],
            'orderId'     => $paymentData['orderId'],
            'oneTimeKey'  => $paymentData['oneTimeKey'],
            'capture'     => $paymentData['capture'],
        );

        // load log model
        $this->ci->load->model('log_payment_auth_model');
        // // 記錄log
        $logIdx = $this->ci->log_payment_auth_model->insertLinepayLogPayment($postData, $paymentData, $inputData);
        // 送至 LINE 付款
        $curlParam = array(
            'channelId'     => $this->cashConfig['config_data']->ID,
            'channelSecret' => $this->cashConfig['config_data']->Secret,
        );
        $this->ci->load->library($this->modulePath . 'linepay_curl', $curlParam, 'linepayCurl');
        $returnData = $this->ci->linepayCurl->request('POST', $this->config['reserveUrl'], $inputData);

        // // 付款成功測試
        // $returnData = array(
        //     'returnCode'    => '0000',
        //     'returnMessage' => 'success',
        //     'info'          => array(
        //         'transactionId'   => '2016010112345678910',
        //         'orderId'         => $paymentData['orderId'],
        //         'transactionDate' => '2016-01-01T01:01:00Z',
        //         'payInfo'         => array(
        //             array(
        //                 'method' => 'BALANCE',
        //                 'amount' => 10,
        //             ),
        //             array(
        //                 'method' => 'DISCOUNT',
        //                 'amount' => 5,
        //             ),
        //         ),
        //         'balance'         => 9900,
        //         'needCheck'       => 'N',
        //     ),
        // );
        // // 付款確認測試
        // $returnData = array(
        //     'returnCode'    => '0000',
        //     'returnMessage' => 'success',
        //     'info'          => array(
        //         'transactionId'   => '2016010112345678910',
        //         'orderId'         => $paymentData['orderId'],
        //         'needCheck'       => 'Y',
        //     ),
        // );
        // // 付款測試 TIMEOUT
        // $returnData = null;
        // MY_Controller::dumpData($postData, $paymentData, $inputData, $returnData);

        if (isset($returnData['returnCode'])) {
            $status = $this->checkReturnData(__FUNCTION__, $paymentData, $returnData);
        } else {
            $status                      = 'TIMEOUT';
            $returnData['returnCode']    = 'AUTH_TIMEOUT';
            $returnData['returnMessage'] = '授權逾時';
        }

        // 更新log
        $updateLogData = array(
            'trade_no'    => (isset($returnData['info']['transactionId']) && $returnData['info']['transactionId']) ? number_format($returnData['info']['transactionId'], 0, '.', '') : '',
            'status'      => $status,
            'return_data' => json_encode($returnData),
            'ip'          => $_SERVER['REMOTE_ADDR'],
            'return_time' => date('Y-m-d H:i:s'),
        );
        $where = array('idx' => $logIdx);
        $this->ci->log_payment_auth_model->update($updateLogData, $where);

        // 輸出
        $this->data['success'] = true;
        if (isset($returnData['info']['needCheck'])) {
            if ($returnData['info']['needCheck'] === 'Y') {
                $this->data['code'] = $this->ci->lang->line('LINEPAY_AUTHCHECK_' . $status);
            } else {
                $this->data['code'] = $this->ci->lang->line('LINEPAY_' . $status);
            }
        } else {
            $this->data['code'] = $this->ci->lang->line('LINEPAY_' . $returnData['returnCode']);
        }
        $this->data['data']              = $returnData;
        $this->data['gateway']           = $this->supplier['supplier_code'];
        $this->data['gatewayName']       = $this->supplier['supplier_name'];
        $this->data['gatewayMerchantID'] = $this->cashConfig['config_data']->MerchantID;

        // 更新授權資料
        $this->ci->auth_model->updateLinepayTransactionAuth($authIdx, $paymentData, $this->data);
        // 取得輸出 data
        $result = $this->ci->linepayOutput->authOutput($postData, $this->data);
        // output log gateway
        $this->ci->linepayLog->updatePaymentLog($paymentLogIdx, $result);

        return $result;
    }

    public function confirm(array $postData, array $responseData)
    {
        // 檢查 service code 是否為退款
        if ($this->option['option_group'] !== __FUNCTION__) {
            return 'SYS_70005';
        }

        // 檢查 _Data 資料是否正確
        $postData['_Data'] = $this->ci->linepayValid->{__FUNCTION__}($postData['_Data']);
        if (!is_array($postData['_Data'])) {
            return $postData['_Data'];
        }

        // log gateway
        $paymentLogIdx = $this->ci->linepayLog->insertPaymentLog(__FUNCTION__, $this->connectLogIdx, $postData);

        // get authData
        $authData = $this->ci->auth_model->getAuthByMerchantIdAndOrderId($postData['MerchantID'], $postData['_Data']['OrderID']);
        if (is_null($authData)) {
            return 'TRA_22301';
        }

        // 更新訂單狀態為請退款中
        $this->ci->auth_model->updateAuthStatus('lock', $authData['idx'], 0);

        // 取得此商店在金流商的相關設定
        $this->cashConfig = $this->ci->cash_config_model->getCashConfigByTerminalIdxAndServiceIdx($this->terminal['idx'], $this->supplier['idx'], $this->product['idx']);
        if (is_null($this->cashConfig)) {
            return 'SYS_70002';
        }
        $this->cashConfig['config_data'] = json_decode($this->cashConfig['config_data']);

        // load refund log model
        $this->ci->load->model('log_payment_query_model');
        $postData['paymentLogIdx'] = $paymentLogIdx;

        // CURL Line header 參數
        $curlParam = array(
            'channelId'     => $this->cashConfig['config_data']->ID,
            'channelSecret' => $this->cashConfig['config_data']->Secret,
        );
        $url = sprintf($this->config['statusCheckUrl'], urlencode($postData['_Data']['OrderID']));
        $this->ci->load->library($this->modulePath . 'linepay_curl', $curlParam, 'linepayCurl');
        // loop get LINE Pay information
        $status = 'AUTH_READY';
        $time   = 0;
        $now    = microtime(true);
        while ($time < 60 && $status === 'AUTH_READY') {
            // 記錄log
            $logIdx = $this->ci->log_payment_query_model->insertLinepayLogQuery($postData);
            // 送至 LINE 付款
            $run        = microtime(true);
            $returnData = $this->ci->linepayCurl->request('GET', $url);

            // MY_Controller::dumpData($returnData);

            // // CANCEL
            // $returnData = array(
            //     'returnCode'    => '0000',
            //     'returnMessage' => 'success',
            //     'info'          => array(
            //         'status' => 'CANCEL',
            //     ),
            // );
            // // COMPLETE
            // $returnData = array(
            //     'returnCode'    => '0000',
            //     'returnMessage' => 'success',
            //     'info'          => array(
            //         'status'          => 'COMPLETE',
            //         'transactionId'   => '2016010112345678910',
            //         'orderId'         => $authData['order_id'],
            //         'transactionDate' => '2016-01-01T01:01:00Z',
            //         'payInfo'         => array(
            //             array(
            //                 'method' => 'BALANCE',
            //                 'amount' => 10,
            //             ),
            //             array(
            //                 'method' => 'DISCOUNT',
            //                 'amount' => 5,
            //             ),
            //         ),
            //         'balance'         => 9900,
            //         'needCheck'       => 'N',
            //     ),
            // );
            // // FAIL
            // $returnData = array(
            //     'returnCode'    => '0000',
            //     'returnMessage' => 'success',
            //     'info'          => array(
            //         'status'            => 'FAIL',
            //         'failReturnCode'    => '1142',
            //         'failReturnMessage' => 'Insufficient balance remains.',
            //     ),
            // );

            if (isset($returnData['info'])) {
                $status = $returnData['info']['status'];
            }

            // 更新log
            $updateLogData = array(
                'status'      => $status,
                'return_data' => json_encode($returnData),
                'ip'          => $_SERVER['REMOTE_ADDR'],
                'return_time' => date('Y-m-d H:i:s'),
            );
            $where = array('idx' => $logIdx);
            $this->ci->log_payment_query_model->update($updateLogData, $where);

            $time = $run - $now;
            sleep(5);
        }

        switch ($status) {
            case 'CANCEL':
                // 更新訂單狀態
                $this->ci->auth_model->updateAuthStatus('cancel', $authData['idx'], 1);
                $this->ci->auth_model->updateAuthStatus('lock', $authData['idx'], 1);
                break;
            case 'COMPLETE':
                $returnData['info']['needCheck'] = 'N';
                $checkStatus = $this->checkReturnData(__FUNCTION__, $authData, $returnData);
                if ($checkStatus === '0000') {
                    // 更新訂單狀態
                    $this->ci->auth_model->updateAuthForLinepay($authData['idx'], $returnData);
                }
                $this->ci->auth_model->updateAuthStatus('lock', $authData['idx'], 1);
                break;
            case 'FAIL':
                $returnData['cancelStatus'] = $this->confirmCancel($authData);
                // 更新訂單狀態
                $this->ci->auth_model->updateAuthStatus('lock', $authData['idx'], 1);
                break;
            default:
                // 更新訂單狀態
                $this->ci->auth_model->updateAuthStatus('lock', $authData['idx'], 1);
                break;
        }

        $confirmData = $this->ci->auth_model->getAuthByIdx($authData['idx']);
        // load output data library
        $result = $this->ci->linepayOutput->confirmOutput($status, $confirmData, $returnData);
        // output log gateway
        $this->ci->linepayLog->updatePaymentLog($paymentLogIdx, $result);

        return $result;
    }

    public function refund(array $postData, array $responseData)
    {
        // 檢查 service code 是否為退款
        if ($this->option['option_group'] !== __FUNCTION__) {
            return 'SYS_70005';
        }

        // 檢查 _Data 資料是否正確
        $postData['_Data'] = $this->ci->linepayValid->{__FUNCTION__}($postData['_Data']);
        if (!is_array($postData['_Data'])) {
            return $postData['_Data'];
        }

        // 檢查是否有這筆訂單
        $authData = $this->ci->auth_model->getSuccessAuthToRefundForLinepay($postData);
        if (is_null($authData) || $authData['refund_status'] === '1') {
            return 'TRA_22200';
        }

        // 判斷是否從 tms 後台來的需求
        if (!isset($postData['_Data']['is_tms'])) {
            $postData['_Data']['is_tms'] = 0;
        } else {
            $postData['_Data']['is_tms'] = 1;
        }

        // 補齊資料
        $postData['_Data']['Amount']   = floatval($authData['amount']);
        $postData['_Data']['Currency'] = $authData['currency'];
        $postData['transactionNo']     = $authData['transaction_no'];

        // 判斷金額是否正確
        if ($postData['_Data']['Amount'] !== floatval($authData['amount'])) {
            return 'TRA_22201';
        }

        // 更新訂單狀態為請退款中
        $this->ci->auth_model->updateAuthStatus('lock', $authData['idx'], 0);
        // log gateway
        $paymentLogIdx = $this->ci->linepayLog->insertPaymentLog(__FUNCTION__, $this->connectLogIdx, $postData);
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
        $refundData = $this->ci->linepayDataTransform->{__FUNCTION__}($postData['_Data']);
        // 記錄用
        $refundData['paymentLogIdx'] = $paymentLogIdx;
        $refundData['supplierIdx']   = $this->supplier['idx'];
        $refundData['MerchantID']    = $this->cashConfig['config_data']->MerchantID;

        $inputData = array(
            'refundAmount' => $refundData['refundAmount'],
        );

        // load log model
        $this->ci->load->model('log_payment_refund_model');
        // 記錄log
        $logIdx = $this->ci->log_payment_refund_model->insertLogRefundForLinepay($postData, $refundData, $inputData);
        // 送至 LINE 付款
        $curlParam = array(
            'channelId'     => $this->cashConfig['config_data']->ID,
            'channelSecret' => $this->cashConfig['config_data']->Secret,
        );
        $this->ci->load->library($this->modulePath . 'linepay_curl', $curlParam, 'linepayCurl');
        $url        = sprintf($this->config['refundUrl'], urlencode($postData['_Data']['OrderID']));
        $returnData = $this->ci->linepayCurl->request('POST', $url, $inputData);

        // // 測試退款成功
        // $returnData = array(
        //     'returnCode'    => '0000',
        //     'returnMessage' => 'success',
        //     'info'          => array(
        //         'refundTransactionId'   => '2016010112345678910',
        //         'refundTransactionDate' => '2016-01-01T01:01:00Z',
        //     ),
        // );
        // // 測試失敗
        // $returnData = array(
        //     'returnCode'    => '0001',
        //     'returnMessage' => '取消授權失敗',
        // );
        // // 測試 timeout
        // $returnData = null;

        if (!isset($returnData['returnCode'])) {
            $returnData = array(
                'returnCode'    => 'REFUND_TIMEOUT',
                'returnMessage' => '退款逾時',
            );
        }
        // 更新log
        $updateLogData = array(
            'trade_no'    => (isset($returnData['info']['refundTransactionId']) && $returnData['info']['refundTransactionId']) ? $returnData['info']['refundTransactionId'] : '',
            'status'      => $returnData['returnCode'],
            'return_data' => json_encode($returnData),
            'ip'          => $_SERVER['REMOTE_ADDR'],
            'return_time' => date('Y-m-d H:i:s'),
        );
        $where = array('idx' => $logIdx);
        $this->ci->log_payment_refund_model->update($updateLogData, $where);

        // 輸出
        if ($returnData['returnCode'] === '0000') {
            $this->data['code'] = $this->ci->lang->line('LINEPAY_REFUND_' . $returnData['returnCode']);
        } else {
            $this->data['code'] = $this->ci->lang->line('LINEPAY_' . $returnData['returnCode']);
        }
        $this->data['data']        = $returnData;
        $this->data['gateway']     = $this->supplier['supplier_code'];
        $this->data['gatewayName'] = $this->supplier['supplier_name'];

        // 更新請款資料
        $this->ci->refund_model->updateRefundForLinepay($refundIdx, $this->data);
        if ($returnData['returnCode'] === '0000') {
            // update auth status & balance
            $this->ci->auth_model->updateAmountByTypeAndAuthIdx('refund', $authData, $authData['amount']);
        }
        // 更新訂單狀態為處理結束
        $this->ci->auth_model->updateAuthStatus('lock', $authData['idx'], 1);

        // 取得輸出 data
        $result = $this->ci->linepayOutput->refundOutput($postData, $refundData, $this->data);
        // output log gateway
        $this->ci->linepayLog->updatePaymentLog($paymentLogIdx, $result);

        return $result;
    }

    public function query(array $postData, array $responseData)
    {
        // 檢查 service code 是否為查詢
        if ($this->option['option_group'] !== __FUNCTION__) {
            return 'SYS_70005';
        }

        // 檢查 _Data 資料是否正確
        $postData['_Data'] = $this->ci->linepayValid->{__FUNCTION__}($postData['_Data']);
        if (!is_array($postData['_Data'])) {
            return $postData['_Data'];
        }

        // 檢查是否有這筆訂單
        $authData = $this->ci->auth_model->getAuthForPaymentQuery($this->supplier['idx'], $this->product['idx'], $postData);
        if (empty($authData)) {
            return 'TRA_22301';
        }

        // 更新訂單狀態為處理中
        $this->ci->auth_model->updateAuthStatus('lock', $authData[0]['idx'], 0);

        // 取得此商店在金流商的相關設定
        $this->cashConfig = $this->ci->cash_config_model->getCashConfigByTerminalIdxAndServiceIdx($this->terminal['idx'], $this->supplier['idx'], $this->product['idx']);
        if (is_null($this->cashConfig)) {
            return 'SYS_70002';
        }
        $this->cashConfig['config_data'] = json_decode($this->cashConfig['config_data']);

        // log gateway
        $paymentLogIdx = $this->ci->linepayLog->insertPaymentLog(__FUNCTION__, $this->connectLogIdx, $postData);

        // load query log model
        $this->ci->load->model('log_payment_query_model');
        // 記錄log
        $logIdx = $this->ci->log_payment_query_model->insertLogQueryForLinepay($paymentLogIdx, $authData);
        // 送至 LINE
        $curlParam = array(
            'channelId'     => $this->cashConfig['config_data']->ID,
            'channelSecret' => $this->cashConfig['config_data']->Secret,
        );
        $this->ci->load->library($this->modulePath . 'linepay_curl', $curlParam, 'linepayCurl');
        $returnData = $this->ci->linepayCurl->request('GET', $this->config['queryUrl'], array('orderId' => $authData[0]['order_id']));

        // 更新log
        $updateLogData = array(
            'status'      => $returnData['returnCode'],
            'return_data' => json_encode($returnData),
            'ip'          => $_SERVER['REMOTE_ADDR'],
            'return_time' => date('Y-m-d H:i:s'),
        );
        $where = array('idx' => $logIdx);
        $this->ci->log_payment_query_model->update($updateLogData, $where);

        foreach ($returnData['info'] as $payment) {
            switch ($payment['transactionType']) {
                case 'PAYMENT':
                    // update auth status & balance
                    $this->ci->auth_model->updateAmountByTypeAndAuthIdx('request', $authData[0], $authData[0]['amount']);
                    break;
                case 'PAYMENT_REFUND':
                    // update auth status & balance
                    $this->ci->auth_model->updateAmountByTypeAndAuthIdx('refund', $authData[0], $authData[0]['amount']);
                    break;
                default:
                    break;
            }
        }

        $queryData[] = $this->ci->auth_model->getAuthByIdx($authData[0]['idx']);

        $result = $this->ci->linepayOutput->queryOutput($this->action, $queryData, $returnData);

        // output log gateway
        $this->ci->linepayLog->updatePaymentLog($paymentLogIdx, $result);

        return $result;
    }

    private function confirmCancel($authData)
    {
        // log gateway
        $paymentLogIdx = $this->ci->linepayLog->insertPaymentLog(__FUNCTION__, $this->connectLogIdx, $authData);
        // load refund log model
        $this->ci->load->model('log_payment_cancel_model');
        $postData['paymentLogIdx'] = $paymentLogIdx;
        // 記錄log
        $logIdx = $this->ci->log_payment_cancel_model->insertLogCancelForLinepay($paymentLogIdx, $authData);
        // Line pay 取消授權網址
        $url = sprintf($this->config['voidUrl'], urlencode($authData['order_id']));
        // post to line pay
        $returnData = $this->ci->linepayCurl->request('POST', $url);

        // 取消授權測試
        $returnData = array(
            'returnCode'    => '0000',
            'returnMessage' => 'success',
        );

        if (!isset($returnData['returnCode'])) {
            $returnData['returnCode']    = 'CANCEL_TIMEOUT';
            $returnData['returnMessage'] = '取消授權逾時';
        }
        // 更新log
        $updateLogData = array(
            'trade_no'    => $authData['trade_no'],
            'status'      => $returnData['returnCode'],
            'return_data' => json_encode($returnData),
            'ip'          => $_SERVER['REMOTE_ADDR'],
            'return_time' => date('Y-m-d H:i:s'),
        );
        $where = array('idx' => $logIdx);
        $this->ci->log_payment_cancel_model->update($updateLogData, $where);

        if ($returnData['returnCode'] === '0000') {
            $this->ci->auth_model->updateAuthStatus('cancel', $authData['idx'], 1);
        }

        // output log gateway
        $this->ci->linepayLog->updatePaymentLog($paymentLogIdx, $returnData);

        // MY_Controller::dumpData($returnData);

        return $returnData;
    }

    /**
     * 檢查回來的結果
     * @param  [array] $data       [原始要post的資料]
     * @param  [array] $returnData [送pay2go回來的json decode資料]
     * @param  [string] $method     [那種付費方式]
     * @return [string]             [交易狀態]
     */
    protected function checkReturnData($method, $data, $returnData)
    {
        if (!empty($data) && !empty($returnData) && !empty($method)) {
            if ($returnData['returnCode'] === '0000') {
                if ($returnData['info']['needCheck'] === 'N') {
                    // 檢查金額是否正確
                    $amount = 0;
                    foreach ($returnData['info']['payInfo'] as $payInfo) {
                        $amount += intval($payInfo['amount']);
                    }
                    if ($data['amount'] == $amount) {
                        return $returnData['returnCode'];
                    } else {
                        return 'CHECK_FAIL';
                    }
                } else {
                    return $returnData['returnCode'];
                }
            } else {
                return 'CHECK_FAIL';
            }
        } else {
            return 'CHECK_FAIL';
        }
    }
}
