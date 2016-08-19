<?php

class Invoice extends BaseModule
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
        $this->ci->lang->load('pay2go', 'zh');

        // load model
        $this->ci->load->model('invoice_model');
        $this->ci->load->model('cash_config_model');

        // load config
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            include dirname(__FILE__) . '\..\Config.php';
        } else {
            include dirname(__FILE__) . '/../Config.php';
        }
        $this->config = $pay2go;

        // set module path
        $this->modulePath = '1.0/' . $this->supplier['supplier_code'] . '/';
        // load cryptography
        $this->ci->load->library($this->modulePath . 'pay2go_cryptography', null, 'Pay2goCryptography');
        // load valid
        $this->ci->load->library($this->modulePath . 'check_data/service/invoice_valid', null, 'Pay2goValid');
        // load log gateway library
        $this->ci->load->library($this->modulePath . 'log/payment_log', null, 'Pay2goPaymentLog');
        // 載入資料轉換 library
        $this->ci->load->library($this->modulePath . 'data_transform/pay2go_data', $this->config, 'pay2goDataTransform');
        // 載入輸出資料轉換 library
        $this->ci->load->library($this->modulePath . 'data_transform/pay2go_output_data', null, 'Pay2goPaymentOutout');

        $this->data = array(
            'success'  => false,
            'code'     => '',
            'errorMsg' => '',
            'data'     => array(),
        );
    }

    /**
     * 開立發票
     * @param  array  $postData     [description]
     * @param  array  $responseData [description]
     * @return [type]               [description]
     */
    public function issue(array $postData, array $responseData)
    {
        // 檢查 service code 是否為開立發票
        if ($this->option['option_group'] !== __FUNCTION__) {
            return 'SYS_70005';
        }

        // 判斷 postData 有無 OrderID，沒有需要從 responseData 中取得
        if ((!isset($postData['_Data']['OrderID']) || empty($postData['_Data']['OrderID'])) && isset($responseData['auth']['Result']['OrderID'])) {
            $postData['_Data']['OrderID'] = $responseData['auth']['Result']['OrderID'];
        }

        // 預設即時開立發票
        $postData['_Data']['Status'] = '1';
        // 預設索取紙本發票
        $postData['_Data']['PrintFlag'] = 'Y';

        // 檢查 _Data 資料是否正確
        $postData['_Data'] = $this->ci->Pay2goValid->{__FUNCTION__}($postData['_Data']);
        if (!is_array($postData['_Data'])) {
            return $postData['_Data'];
        }

        // log gateway
        $paymentLogIdx = $this->ci->Pay2goPaymentLog->insertPaymentLog(__FUNCTION__, $this->connectLogIdx, $postData);

        // insert invoice data
        $invoiceIdx = $this->ci->invoice_model->insertInvoice(__FUNCTION__, $paymentLogIdx, $this->merchant, $postData);

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
        $invoiceData = $this->ci->pay2goDataTransform->issue($postData['_Data']);

        $invoiceData['paymentLogIdx'] = $paymentLogIdx;
        $invoiceData['supplierIdx']   = $this->supplier['idx'];
        $gateway                      = $this->config['invoice_issue_url'];

        // load log model
        $this->ci->load->model('log_invoice_issue_model');

        $inputArray = array(
            'RespondType'     => $invoiceData['RespondType'],
            'Version'         => $invoiceData['Version'], //串接程式版本
            'TimeStamp'       => $invoiceData['TimeStamp'], //時間戳記
            'MerchantOrderNo' => $invoiceData['MerchantOrderNo'], //商店訂單編號，同一店鋪中此編號不可重覆
            'Status'          => $invoiceData['Status'],
            // 'CreateStatusTime' => $invoiceData['CreateStatusTime'],
            'Category'        => $invoiceData['Category'],
            'BuyerName'       => $invoiceData['BuyerName'],
            'BuyerUBN'        => $invoiceData['BuyerUBN'],
            'BuyerEmail'      => $invoiceData['BuyerEmail'],
            'PrintFlag'       => $invoiceData['PrintFlag'],
            'TaxType'         => $invoiceData['TaxType'],
            'TaxRate'         => $invoiceData['TaxRate'],
            'Amt'             => floatval($invoiceData['Amt']), //訂單金額
            'TaxAmt'          => floatval($invoiceData['TaxAmt']),
            'TotalAmt'        => floatval($invoiceData['TotalAmt']),
            'ItemName'        => $invoiceData['ItemName'],
            'ItemCount'       => $invoiceData['ItemCount'],
            'ItemUnit'        => $invoiceData['ItemUnit'],
            'ItemPrice'       => $invoiceData['ItemPrice'],
            'ItemAmt'         => $invoiceData['ItemAmt'],
        );

        // 加密函式
        $pay2goPostData = $this->ci->Pay2goCryptography->encryption($cashConfig['config_data']->Key, $cashConfig['config_data']->Iv, $inputArray);

        // curl post
        $curlData = array(
            'MerchantID_' => $cashConfig['config_data']->MerchantID,
            'PostData_'   => $pay2goPostData,
        );

        // 記錄log
        $logIdx = $this->ci->log_invoice_issue_model->insertLogInvoice($invoiceData, $curlData);

        $json               = curlPost($gateway, $curlData);
        $returnData         = json_decode($json);
        $returnData->Result = json_decode($returnData->Result);

        // 狀態
        $status = $this->checkReturnData($invoiceData, $returnData, __FUNCTION__);

        // 更新log
        $updateLogData = array(
            'invoice_trade_no' => (isset($returnData->Result->InvoiceTransNo) && $returnData->Result->InvoiceTransNo) ? $returnData->Result->InvoiceTransNo : '',
            'status'           => $status,
            'return_data'      => $json,
            'ip'               => $_SERVER['REMOTE_ADDR'],
            'return_time'      => date('Y-m-d H:i:s'),
        );
        $where = array('idx' => intval($logIdx));
        $this->ci->log_invoice_issue_model->update($updateLogData, $where);

        $this->data['success'] = true;
        if ($status === 'SUCCESS') {
            $this->data['code'] = $this->ci->lang->line('INVOICE_ISSUE_' . $status);
        } else {
            $this->data['code'] = $this->ci->lang->line('INVOICE_' . $status);
        }
        $this->data['data'] = $returnData;

        // 更新授權資料
        if ($postData['_Data']['Status'] === '0') {
            $this->ci->invoice_model->updateInvoice($invoiceIdx, $this->data, 2);
        } else if ($postData['_Data']['Status'] === '1') {
            $this->ci->invoice_model->updateInvoice($invoiceIdx, $this->data, 1);
        }

        // 取得輸出 data
        $result = $this->ci->Pay2goPaymentOutout->invoiceIssueOutput($this->action['action_code'], $postData, $this->data);

        // MY_Controller::dumpData($status);

        // output log gateway
        $paymentLogIdx = $this->ci->Pay2goPaymentLog->updatePaymentLog($paymentLogIdx, $result);

        return $result;
    }

    /**
     * 觸發開立發票
     * @param  array  $postData     [description]
     * @param  array  $responseData [description]
     * @return [type]               [description]
     */
    public function touch(array $postData, array $responseData)
    {
        // 檢查 service code 是否為觸發開立發票
        if ($this->option['option_group'] !== __FUNCTION__) {
            return 'SYS_70005';
        }

        // 判斷 postData 有無 OrderID，沒有需要從 responseData 中取得
        if ((!isset($postData['_Data']['OrderID']) || empty($postData['_Data']['OrderID'])) && isset($responseData['auth']['Result']['OrderID'])) {
            $postData['_Data']['OrderID'] = $responseData['auth']['Result']['OrderID'];
        }

        // 檢查 _Data 資料是否正確
        $postData['_Data'] = $this->ci->Pay2goValid->touch($postData['_Data']);
        if (!is_array($postData['_Data'])) {
            return $postData['_Data'];
        }

        // 取得要觸發開立發票的資料
        $invoiceData = $this->ci->invoice_model->getInvoiceToTouch($postData);
        if (is_null($invoiceData)) {
            return 'INVOICE_60033';
        }

        $postData['_Data']['TotalAmt']       = floatval($invoiceData['total_amount']);
        $postData['_Data']['InvoiceTransNo'] = $invoiceData['invoice_trade_no'];

        // 更新發票狀態為處理中
        $this->ci->invoice_model->updateInvoiceStatus('lock', $invoiceData['idx'], 0);

        // log gateway
        $paymentLogIdx = $this->ci->Pay2goPaymentLog->insertPaymentLog(__FUNCTION__, $this->connectLogIdx, $postData);

        // insert invoice touch data
        $this->ci->load->model('invoice_touch_model');
        $touchIdx = $this->ci->invoice_touch_model->insertInvoiceTouch($invoiceData['idx'], $postData);

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
        $touchData = $this->ci->pay2goDataTransform->{__FUNCTION__}($postData['_Data']);

        // 送去金流商做請款
        $touchData['paymentLogIdx'] = $paymentLogIdx;
        $touchData['supplierIdx']   = $this->supplier['idx'];

        // load request log model
        $this->ci->load->model('log_invoice_touch_model');

        $gateway = $this->config['invoice_touch_url'];

        $inputArray = array(
            'RespondType'     => $touchData['RespondType'],
            'Version'         => $touchData['Version'], //串接程式版本
            'TimeStamp'       => $touchData['TimeStamp'], //時間戳記
            'MerchantOrderNo' => $touchData['MerchantOrderNo'], //商店訂單編號，同一店鋪中此編號不可重覆
            'InvoiceTransNo'  => $touchData['InvoiceTransNo'],
            'TotalAmt'        => $touchData['TotalAmt'],
        );

        // 加密函式
        $pay2goPostData = $this->ci->Pay2goCryptography->encryption($cashConfig['config_data']->Key, $cashConfig['config_data']->Iv, $inputArray);

        // curl post
        $curlData = array(
            'MerchantID_' => $cashConfig['config_data']->MerchantID,
            'PostData_'   => $pay2goPostData,
        );

        // 記錄log
        $logIdx = $this->ci->log_invoice_touch_model->insertLogInvoiceTouch($touchData, $curlData);

        $json               = curlPost($gateway, $curlData);
        $returnData         = json_decode($json);
        $returnData->Result = json_decode($returnData->Result);

        // 狀態
        $status = $this->checkReturnData($touchData, $returnData, __FUNCTION__);

        // 更新log
        $updateLogData = array(
            'invoice_trade_no' => (isset($returnData->Result->InvoiceTransNo) && $returnData->Result->InvoiceTransNo) ? $returnData->Result->InvoiceTransNo : '',
            'status'           => $status,
            'return_data'      => $json,
            'ip'               => $_SERVER['REMOTE_ADDR'],
            'return_time'      => date('Y-m-d H:i:s'),
        );
        $where = array('idx' => intval($logIdx));
        $this->ci->log_invoice_touch_model->update($updateLogData, $where);

        $this->data['success'] = true;
        if ($status === 'SUCCESS') {
            $this->data['code'] = $this->ci->lang->line('INVOICE_TOUCH_' . $status);
        } else {
            $this->data['code'] = $this->ci->lang->line('INVOICE_' . $status);
        }
        $this->data['data'] = $returnData;

        // 更新觸發發票資料
        $this->ci->invoice_touch_model->updateInvoiceTouch($touchIdx, $this->data);

        if ($this->data['data']->Status === 'SUCCESS') {
            $this->ci->invoice_model->updateInvoiceStatus('issue', $invoiceData['idx'], 1);
            $this->ci->invoice_model->updateInvoiceStatus('touch', $invoiceData['idx'], 1);
        }
        $this->ci->invoice_model->updateInvoiceStatus('lock', $invoiceData['idx'], 1);

        // 取得輸出 data
        $result = $this->ci->Pay2goPaymentOutout->invoiceTouchOutput($this->action['action_code'], $postData, $this->data);

        // output log gateway
        $paymentLogIdx = $this->ci->Pay2goPaymentLog->updatePaymentLog($paymentLogIdx, $result);

        return $result;
    }

    /**
     * 作廢發票
     * @param  array  $postData     [description]
     * @param  array  $responseData [description]
     * @return [type]               [description]
     */
    public function invalid(array $postData, array $responseData)
    {
        // 檢查 service code 是否為觸發開立發票
        if ($this->option['option_group'] !== __FUNCTION__) {
            return 'SYS_70005';
        }

        // 判斷 postData 有無 OrderID，沒有需要從 responseData 中取得
        if ((!isset($postData['_Data']['InvoiceNumber']) || empty($postData['_Data']['InvoiceNumber'])) && isset($responseData['issue']['Result']['InvoiceNumber'])) {
            $postData['_Data']['InvoiceNumber'] = $responseData['issue']['Result']['InvoiceNumber'];
        }

        // 檢查 _Data 資料是否正確
        $postData['_Data'] = $this->ci->Pay2goValid->{__FUNCTION__}($postData['_Data']);
        if (!is_array($postData['_Data'])) {
            return $postData['_Data'];
        }

        // 取得要觸發開立發票的資料
        $invoiceData = $this->ci->invoice_model->getInvoiceToInvalid($postData);
        if (is_null($invoiceData)) {
            return 'INVOICE_61002';
        }

        // 更新發票狀態為處理中
        $this->ci->invoice_model->updateInvoiceStatus('lock', $invoiceData['idx'], 0);

        // log gateway
        $paymentLogIdx = $this->ci->Pay2goPaymentLog->insertPaymentLog(__FUNCTION__, $this->connectLogIdx, $postData);

        // insert invoice touch data
        $this->ci->load->model('invoice_invalid_model');
        $invalidIdx = $this->ci->invoice_invalid_model->insertInvoiceInvalid($invoiceData, $postData);

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
        $invalidData = $this->ci->pay2goDataTransform->{__FUNCTION__}($postData['_Data']);

        // 送去加值營運中心做作廢發票
        $invalidData['paymentLogIdx']      = $paymentLogIdx;
        $invalidData['supplierIdx']        = $this->supplier['idx'];
        $invalidData['WeCanPayMerchantID'] = $postData['MerchantID'];

        // load request log model
        $this->ci->load->model('log_invoice_invalid_model');

        $gateway = $this->config['invoice_invaild_url'];

        $inputArray = array(
            'RespondType'   => $invalidData['RespondType'],
            'Version'       => $invalidData['Version'], //串接程式版本
            'TimeStamp'     => $invalidData['TimeStamp'], //時間戳記
            'InvoiceNumber' => $invalidData['InvoiceNumber'],
            'InvalidReason' => $invalidData['InvalidReason'],
        );

        // 加密函式
        $pay2goPostData = $this->ci->Pay2goCryptography->encryption($cashConfig['config_data']->Key, $cashConfig['config_data']->Iv, $inputArray);

        // curl post
        $curlData = array(
            'MerchantID_' => $cashConfig['config_data']->MerchantID,
            'PostData_'   => $pay2goPostData,
        );

        // 記錄log
        $logIdx = $this->ci->log_invoice_invalid_model->insertLogInvoiceInvalid($invalidData, $curlData);

        $json       = curlPost($gateway, $curlData);
        $returnData = json_decode($json);
        if (isset($returnData->Result)) {
            $returnData->Result = json_decode($returnData->Result);
        }

        // 狀態
        $status = $this->checkReturnData($invalidData, $returnData, __FUNCTION__);

        // 更新log
        $updateLogData = array(
            'status'      => $status,
            'return_data' => $json,
            'ip'          => $_SERVER['REMOTE_ADDR'],
            'return_time' => date('Y-m-d H:i:s'),
        );
        $where = array('idx' => intval($logIdx));
        $this->ci->log_invoice_invalid_model->update($updateLogData, $where);

        $this->data['success'] = true;
        if ($status === 'SUCCESS') {
            $this->data['code'] = $this->ci->lang->line('INVOICE_INVALID_' . $status);
        } else {
            $this->data['code'] = $this->ci->lang->line('INVOICE_' . $status);
        }
        $this->data['data'] = $returnData;

        // 更新觸發發票資料
        $this->ci->invoice_invalid_model->updateInvoiceInvalid($invalidIdx, $this->data);

        if ($this->data['data']->Status === 'SUCCESS') {
            $this->ci->invoice_model->updateInvoiceStatus('invalid', $invoiceData['idx'], 1);
        }
        $this->ci->invoice_model->updateInvoiceStatus('lock', $invoiceData['idx'], 1);

        // 取得輸出 data
        $result = $this->ci->Pay2goPaymentOutout->invoiceInvalidOutput($this->action['action_code'], $postData, $this->data);

        // output log gateway
        $paymentLogIdx = $this->ci->Pay2goPaymentLog->updatePaymentLog($paymentLogIdx, $result);

        return $result;
    }

    /**
     * 折讓發票
     * @param  array  $postData     [description]
     * @param  array  $responseData [description]
     * @return [type]               [description]
     */
    public function allowance(array $postData, array $responseData)
    {
        // 檢查 service code 是否為開立發票
        if ($this->option['option_group'] !== __FUNCTION__) {
            return 'SYS_70005';
        }

        // 預設立即確認折讓
        $postData['_Data']['Status'] = '1';

        // 檢查 _Data 資料是否正確
        $postData['_Data'] = $this->ci->Pay2goValid->{__FUNCTION__}($postData['_Data']);
        if (!is_array($postData['_Data'])) {
            return $postData['_Data'];
        }

        // get invoice data
        $invoiceData = $this->ci->invoice_model->getInvoiceByInvoiceNumber($postData['MerchantID'], $postData['_Data']['InvoiceNumber']);
        if (is_null($invoiceData)) {
            return 'INVOICE_62002';
        }

        // postData 存入訂單編號
        $postData['_Data']['OrderID'] = $invoiceData['order_id'];

        // 更新發票狀態為處理中
        $this->ci->invoice_model->updateInvoiceStatus('lock', $invoiceData['idx'], 0);

        // log gateway
        $paymentLogIdx = $this->ci->Pay2goPaymentLog->insertPaymentLog(__FUNCTION__, $this->connectLogIdx, $postData);

        // insert invoice touch data
        $this->ci->load->model('invoice_allowance_model');
        $allowanceIdx = $this->ci->invoice_allowance_model->insertInvoiceAllowance($invoiceData, $postData);

        // 取得此商店在金流商的相關設定
        $cashConfig = $this->ci->cash_config_model->getCashConfigByTerminalIdxAndServiceIdx($this->terminal['idx'], $this->supplier['idx'], $this->product['idx']);
        if (is_null($cashConfig)) {
            return 'SYS_70002';
        }

        $cashConfig['config_data']       = json_decode($cashConfig['config_data']);
        $postData['_Data']['MerchantID'] = $cashConfig['config_data']->MerchantID;
        $postData['_Data']['Key']        = $cashConfig['config_data']->Key;
        $postData['_Data']['Iv']         = $cashConfig['config_data']->Iv;

        // 折讓發票資料轉換
        $allowanceData = $this->ci->pay2goDataTransform->{__FUNCTION__}($postData['_Data']);

        // 送去金流商做折讓
        $allowanceData['paymentLogIdx'] = $paymentLogIdx;
        $allowanceData['supplierIdx']   = $this->supplier['idx'];

        // load request log model
        $this->ci->load->model('log_invoice_allowance_model');

        $gateway = $this->config['invoice_allowance_url'];

        $inputArray = array(
            'RespondType'     => $allowanceData['RespondType'],
            'Version'         => $allowanceData['Version'],
            'TimeStamp'       => $allowanceData['TimeStamp'],
            'InvoiceNo'       => $allowanceData['InvoiceNo'],
            'MerchantOrderNo' => $allowanceData['MerchantOrderNo'],
            'ItemName'        => $allowanceData['ItemName'],
            'ItemCount'       => $allowanceData['ItemCount'],
            'ItemUnit'        => $allowanceData['ItemUnit'],
            'ItemPrice'       => $allowanceData['ItemPrice'],
            'ItemAmt'         => $allowanceData['ItemAmt'],
            'ItemTaxAmt'      => $allowanceData['ItemTaxAmt'],
            'TotalAmt'        => $allowanceData['TotalAmt'],
            'Status'          => $allowanceData['Status'],
        );

        // 加密函式
        $pay2goPostData = $this->ci->Pay2goCryptography->encryption($cashConfig['config_data']->Key, $cashConfig['config_data']->Iv, $inputArray);

        // curl post
        $curlData = array(
            'MerchantID_' => $cashConfig['config_data']->MerchantID,
            'PostData_'   => $pay2goPostData,
        );

        // 記錄log
        $logIdx = $this->ci->log_invoice_allowance_model->insertLogInvoiceAllowance($allowanceData, $curlData);

        $json       = curlPost($gateway, $curlData);
        $returnData = json_decode($json);
        if (isset($returnData->Result)) {
            $returnData->Result = json_decode($returnData->Result);
        }

        // 狀態
        $status = $this->checkReturnData($allowanceData, $returnData, __FUNCTION__);

        // 更新log
        $updateLogData = array(
            'status'      => $status,
            'return_data' => $json,
            'ip'          => $_SERVER['REMOTE_ADDR'],
            'return_time' => date('Y-m-d H:i:s'),
        );
        $where = array('idx' => intval($logIdx));
        $this->ci->log_invoice_allowance_model->update($updateLogData, $where);

        $this->data['success'] = true;
        if ($status === 'SUCCESS') {
            $this->data['code'] = $this->ci->lang->line('INVOICE_ALLOWANCE_' . $status);
        } else {
            $this->data['code'] = $this->ci->lang->line('INVOICE_' . $status);
        }
        $this->data['data'] = $returnData;

        // 更新觸發發票資料
        $this->ci->invoice_allowance_model->updateInvoiceAllowance($allowanceIdx, $this->data);

        if ($this->data['data']->Status === 'SUCCESS') {
            $param = array(
                'allowance_no'     => $returnData->Result->AllowanceNo,
                'allowance_amount' => $returnData->Result->AllowanceAmt,
            );
            $this->ci->invoice_model->updateInvoiceStatus('allowance', $invoiceData['idx'], 1, $param);
        }
        $this->ci->invoice_model->updateInvoiceStatus('lock', $invoiceData['idx'], 1);

        // 取得輸出 data
        $result = $this->ci->Pay2goPaymentOutout->invoiceAllowanceOutput($this->action['action_code'], $postData, $this->data);

        // output log gateway
        $paymentLogIdx = $this->ci->Pay2goPaymentLog->updatePaymentLog($paymentLogIdx, $result);

        return $result;
    }

    public function search(array $postData, array $responseData)
    {
        // 檢查 service code 是否為開立發票
        if ($this->option['option_group'] !== __FUNCTION__) {
            return 'SYS_70005';
        }

        // 檢查 _Data 資料是否正確
        $postData['_Data'] = $this->ci->Pay2goValid->{__FUNCTION__}($postData['_Data']);
        if (!is_array($postData['_Data'])) {
            return $postData['_Data'];
        }

        // get invoice data
        $invoiceData = $this->ci->invoice_model->getInvoiceByInvoiceNumber($postData['MerchantID'], $postData['_Data']['InvoiceNumber']);
        if (is_null($invoiceData)) {
            return 'INVOICE_62002';
        }

        // 更新發票狀態為處理中
        $this->ci->invoice_model->updateInvoiceStatus('lock', $invoiceData['idx'], 0);

        // log gateway
        $paymentLogIdx = $this->ci->Pay2goPaymentLog->insertPaymentLog(__FUNCTION__, $this->connectLogIdx, $postData);

        // insert invoice touch data
        $this->ci->load->model('invoice_search_model');
        $searchIdx = $this->ci->invoice_search_model->insertInvoiceSearch($invoiceData, $postData);

        // 取得此商店在金流商的相關設定
        $cashConfig = $this->ci->cash_config_model->getCashConfigByTerminalIdxAndServiceIdx($this->terminal['idx'], $this->supplier['idx'], $this->product['idx']);
        if (is_null($cashConfig)) {
            return 'SYS_70002';
        }

        $cashConfig['config_data']       = json_decode($cashConfig['config_data']);
        $postData['_Data']['MerchantID'] = $cashConfig['config_data']->MerchantID;
        $postData['_Data']['Key']        = $cashConfig['config_data']->Key;
        $postData['_Data']['Iv']         = $cashConfig['config_data']->Iv;

        // 折讓發票資料轉換
        $searchData = $this->ci->pay2goDataTransform->{__FUNCTION__}($postData['_Data']);

        // 送去加值營運中心做查詢
        $searchData['paymentLogIdx'] = $paymentLogIdx;
        $searchData['supplierIdx']   = $this->supplier['idx'];

        // load request log model
        $this->ci->load->model('log_invoice_search_model');

        $gateway = $this->config['invoice_search_url'];

        $inputArray = array(
            'RespondType'     => $searchData['RespondType'],
            'Version'         => $searchData['Version'],
            'TimeStamp'       => $searchData['TimeStamp'],
            'SearchType'      => $searchData['SearchType'],
            'MerchantOrderNo' => $invoiceData['order_id'],
            'TotalAmt'        => intval($invoiceData['total_amount']),
            'InvoiceNumber'   => $searchData['InvoiceNumber'],
            'RandomNum'       => $invoiceData['random_number'],
        );

        // 加密函式
        $pay2goPostData = $this->ci->Pay2goCryptography->encryption($cashConfig['config_data']->Key, $cashConfig['config_data']->Iv, $inputArray);

        // curl post
        $curlData = array(
            'MerchantID_' => $cashConfig['config_data']->MerchantID,
            'PostData_'   => $pay2goPostData,
        );

        // 記錄log
        $logIdx = $this->ci->log_invoice_search_model->insertLogInvoiceSearch($searchData, $curlData);

        $json       = curlPost($gateway, $curlData);
        $returnData = json_decode($json);
        if (isset($returnData->Result)) {
            $returnData->Result = json_decode($returnData->Result);
        }

        // 狀態
        $status = $this->checkReturnData($searchData, $returnData, __FUNCTION__);

        // 更新log
        $updateLogData = array(
            'status'      => $status,
            'return_data' => $json,
            'ip'          => $_SERVER['REMOTE_ADDR'],
            'return_time' => date('Y-m-d H:i:s'),
        );
        $where = array('idx' => intval($logIdx));
        $this->ci->log_invoice_search_model->update($updateLogData, $where);

        $this->data['success'] = true;
        if ($status === 'SUCCESS') {
            $this->data['code'] = $this->ci->lang->line('INVOICE_SEARCH_' . $status);
        } else {
            $this->data['code'] = $this->ci->lang->line('INVOICE_' . $status);
        }
        $this->data['data'] = $returnData;

        // 更新查詢發票資料
        $this->ci->invoice_search_model->updateInvoiceSearch($searchIdx, $this->data);

        $this->ci->invoice_model->updateInvoiceStatus('lock', $invoiceData['idx'], 1);

        // 取得輸出 data
        $result = $this->ci->Pay2goPaymentOutout->invoiceSearchOutput($this->action['action_code'], $postData, $this->data);

        // output log gateway
        $paymentLogIdx = $this->ci->Pay2goPaymentLog->updatePaymentLog($paymentLogIdx, $result);

        return $result;

        MY_Controller::dumpData($postData, $invoiceData);
    }

    /**
     * 檢查回來的結果
     * @param  {array} $data       [原始要post的資料]
     * @param  {array} $returnData [送pay2go回來的json decode資料]
     * @param  {string} $method     [方式]
     * @return {string}             [交易狀態]
     */
    private function checkReturnData($data, $returnData, $method)
    {
        $status = (isset($returnData->Status) && $returnData->Status) ? $returnData->Status : 'CHECK_FAIL';

        if (!empty($data) && !empty($returnData) && !empty($method)) {
            // check return data
            if (is_object($returnData) && isset($returnData->Status)) {
                if ($returnData->Status === 'SUCCESS' && isset($returnData->Result->CheckCode)) {
                    switch ($method) {
                        case 'issue':
                        case 'touch':
                            $checkData = array(
                                'MerchantID'      => $data['MerchantID'], //商店代號
                                'MerchantOrderNo' => $data['MerchantOrderNo'], //商店自訂單號(訂單編號)
                                'InvoiceTransNo'  => $returnData->Result->InvoiceTransNo, //智付寶電子發票開立序號
                                'TotalAmt'        => $returnData->Result->TotalAmt, //發票金額
                                'RandomNum'       => $returnData->Result->RandomNum, //發票防偽隨機碼
                            );
                            break;
                        case 'invalid':
                            $this->ci->load->model('invoice_model');
                            $invoiceData = $this->ci->invoice_model->getInvoiceByInvoiceNumber($data['WeCanPayMerchantID'], $returnData->Result->InvoiceNumber);
                            $checkData   = array(
                                'MerchantID'      => $data['MerchantID'], //商店代號
                                'MerchantOrderNo' => $invoiceData['order_id'], //商店自訂單號(訂單編號)
                                'InvoiceTransNo'  => $invoiceData['invoice_trade_no'], //智付寶電子發票開立序號
                                'TotalAmt'        => floatval($invoiceData['total_amount']), //發票金額
                                'RandomNum'       => $invoiceData['random_number'], //發票防偽隨機碼
                            );
                            break;
                        case 'search':
                            $checkData = array(
                                'MerchantID'      => $data['MerchantID'], //商店代號
                                'MerchantOrderNo' => $returnData->Result->MerchantOrderNo, //商店自訂單號(訂單編號)
                                'InvoiceTransNo'  => $returnData->Result->InvoiceTransNo, //智付寶電子發票開立序號
                                'TotalAmt'        => $returnData->Result->TotalAmt, //發票金額
                                'RandomNum'       => $returnData->Result->RandomNum, //發票防偽隨機碼
                            );
                            break;
                        default:
                            $checkData = array();
                            break;
                    }
                    ksort($checkData);
                    $checkStr     = http_build_query($checkData);
                    $checkCodeStr = 'HashIV=' . $data['HashIV'] . '&' . $checkStr . '&HashKey=' . $data['HashKey'];
                    $checkCode    = strtoupper(hash('sha256', $checkCodeStr));

                    // 驗證CheckCode、金額、訂單編號
                    if ($returnData->Result->CheckCode != $checkCode || $data['MerchantID'] != $returnData->Result->MerchantID) {
                        //比對有問題
                        $status = 'CHECK_FAIL';
                    } else {
                        $status = $returnData->Status;
                    }
                }
            }
        }

        return $status;
    }
}
