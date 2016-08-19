<?php

class Auth_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function CheckTransactionUnique($transactionNo)
    {
        $where = array(
            'transaction_no' => $transactionNo,
        );

        $result = $this->select(false, 'array', $where, 1);

        if (is_null($result)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *  紀錄交易認證
     *
     *  @param {int} $paymentLogIdx 這筆交易認證的 payment log idx
     *  @param {array} $merchant 商店資訊
     *  @param {array} $postData 傳入的交易資料
     *  @return insertId || false
     */
    public function insertAuth($paymentLogIdx, array $merchant, array $edc, array $postData)
    {
        $payInfo = array(
            'method' => 'CREDIT_CARD',
            'amount' => floatval($postData['_Data']['Amount']),
        );
        $insertData = array(
            'log_payment_gateway_idx' => intval($paymentLogIdx),
            'gateway_version'         => $postData['Gateway'],
            'edc_idx'                 => $edc['idx'],
            'app_name'                => $postData['_Data']['AppName'],
            'app_version'             => $postData['_Data']['AppVersion'],
            'terminal_code'           => $postData['TerminalID'],
            'member_idx'              => $merchant['member_idx'],
            'merchant_id'             => $postData['MerchantID'],
            'supplier_idx'            => intval($postData['service'][0]),
            'product_idx'             => intval($postData['service'][1]),
            'action_idx'              => intval($postData['service'][2]),
            'option_idx'              => intval($postData['service'][3]),
            'transaction_no'          => $postData['transactionNo'],
            'order_id'                => $postData['_Data']['OrderID'],
            'currency'                => $postData['_Data']['Currency'],
            'amount'                  => floatval($postData['_Data']['Amount']),
            'inst'                    => intval($postData['_Data']['Inst']),
            'product_desc'            => $postData['_Data']['ProDesc'],
            'active_request'          => intval($postData['_Data']['ActReq']),
            'pay_info'                => json_encode($payInfo),
            'auth_status'             => 0,
            'create_time'             => date('Y-m-d H:i:s'),
            'token'                   => openssl_digest($postData['_Data']['CardNo'], 'sha256'),
            'ip'                      => $this->input->ip_address(),
        );

        if (isset($postData['_Data']['CardNo']) && !empty($postData['_Data']['CardNo'])) {
            $insertData['card_length'] = strlen($postData['_Data']['CardNo']);
            $insertData['card6no']     = substr($postData['_Data']['CardNo'], 0, 6);
            $insertData['card4no']     = substr($postData['_Data']['CardNo'], -4, 4);
        }

        return $this->insert($insertData);
    }

    /**
     *  紀錄台新支付寶交易認證
     *
     *  @param {int} $paymentLogIdx 這筆交易認證的 payment log idx
     *  @param {array} $merchant 商店資訊
     *  @param {array} $postData 傳入的交易資料
     *  @return insertId || false
     */
    public function insertAlipay($paymentLogIdx, $merchant, $edc, array $postData)
    {
        $proDesc = array(
            'OrderName' => $postData['_Data']['OrderName'],
            'OrderMemo' => $postData['_Data']['OrderMemo'],
        );
        $payInfo = array(
            'method' => 'BALANCE',
            'amount' => floatval($postData['_Data']['Amount']),
        );

        $insertData = array(
            'log_payment_gateway_idx' => intval($paymentLogIdx),
            'gateway_version'         => $postData['Gateway'],
            'edc_idx'                 => $edc['idx'],
            'app_name'                => $postData['_Data']['AppName'],
            'app_version'             => $postData['_Data']['AppVersion'],
            'terminal_code'           => $postData['TerminalID'],
            'member_idx'              => $merchant['member_idx'],
            'merchant_id'             => $postData['MerchantID'],
            'supplier_idx'            => intval($postData['service'][0]),
            'product_idx'             => intval($postData['service'][1]),
            'action_idx'              => intval($postData['service'][2]),
            'option_idx'              => intval($postData['service'][3]),
            'transaction_no'          => $postData['transactionNo'],
            'order_id'                => $postData['_Data']['OrderID'],
            'currency'                => $postData['_Data']['Currency'],
            'amount'                  => floatval($postData['_Data']['Amount']),
            'inst'                    => 0,
            'product_desc'            => json_encode($proDesc),
            'active_request'          => 0,
            'auth_status'             => 0,
            'create_time'             => date('Y-m-d H:i:s'),
            'token'                   => '',
            'ip'                      => $this->input->ip_address(),
            'card_length'             => 0,
            'card6no'                 => '',
            'card4no'                 => '',
        );

        return $this->insert($insertData);
    }

    /**
     *  紀錄台新支付寶交易認證
     *
     *  @param {int} $paymentLogIdx 這筆交易認證的 payment log idx
     *  @param {array} $merchant 商店資訊
     *  @param {array} $postData 傳入的交易資料
     *  @return insertId || false
     */
    public function insertLinepay($paymentLogIdx, $merchant, $edc, array $postData)
    {
        $proDesc = array(
            'ProductName' => $postData['_Data']['ProductName'],
        );

        $insertData = array(
            'log_payment_gateway_idx' => intval($paymentLogIdx),
            'gateway_version'         => $postData['Gateway'],
            'edc_idx'                 => $edc['idx'],
            'app_name'                => $postData['_Data']['AppName'],
            'app_version'             => $postData['_Data']['AppVersion'],
            'terminal_code'           => $postData['TerminalID'],
            'member_idx'              => $merchant['member_idx'],
            'merchant_id'             => $postData['MerchantID'],
            'supplier_idx'            => intval($postData['service'][0]),
            'product_idx'             => intval($postData['service'][1]),
            'action_idx'              => intval($postData['service'][2]),
            'option_idx'              => intval($postData['service'][3]),
            'transaction_no'          => $postData['transactionNo'],
            'order_id'                => $postData['_Data']['OrderID'],
            'currency'                => $postData['_Data']['Currency'],
            'amount'                  => floatval($postData['_Data']['Amount']),
            'inst'                    => 0,
            'product_desc'            => json_encode($proDesc),
            'active_request'          => 0,
            'auth_status'             => 0,
            'create_time'             => date('Y-m-d H:i:s'),
            'token'                   => '',
            'ip'                      => $this->input->ip_address(),
            'card_length'             => 0,
            'card6no'                 => '',
            'card4no'                 => '',
        );

        return $this->insert($insertData);
    }

    /**
     *  更新授權結果
     *
     *  @param $authId int 授權資料流水號
     *  @param $transactionResult array 交易結果
     */
    public function updateTransactionAuth($authIdx, $transactionResult)
    {
        $where = array(
            'idx' => intval($authIdx),
        );

        $updateData = array(
            'response_code' => $transactionResult['data']->Status,
            'response_msg'  => $transactionResult['data']->Message,
            'modify_time'   => date('Y-m-d H:i:s'),
            'auth_status'   => 2,
        );

        if ($transactionResult['code'] === 'PAYMENT_00000') {
            $updateData['auth_status'] = 1;
            $updateData['trade_no']    = $transactionResult['data']->Result->TradeNo;
            $updateData['auth_bank']   = $transactionResult['data']->Result->AuthBank;
            $updateData['auth_code']   = $transactionResult['data']->Result->Auth;
            $updateData['auth_date']   = $transactionResult['data']->Result->AuthDate;
            $updateData['auth_time']   = $transactionResult['data']->Result->AuthTime;
            $updateData['inst_first']  = floatval($transactionResult['data']->Result->InstFirst);
            $updateData['inst_each']   = floatval($transactionResult['data']->Result->InstEach);
        }

        return $this->update($updateData, $where);
    }

    /**
     *  更新授權結果
     *
     *  @param $authId int 授權資料流水號
     *  @param $transactionResult array 交易結果
     */
    public function updateAlipayTransactionAuth($authIdx, $transactionResult)
    {
        $where = array(
            'idx' => intval($authIdx),
        );

        $updateData = array(
            'response_code' => $transactionResult['data']->return_code,
            'response_msg'  => $transactionResult['data']->return_message,
            'modify_time'   => date('Y-m-d H:i:s'),
            'auth_status'   => 2,
        );

        if ($transactionResult['code'] === 'ALIPAY_00000') {
            $updateData['auth_status']    = 1;
            $time                         = strtotime($transactionResult['data']->timestamp);
            $authDate                     = date('Y-m-d', $time);
            $authTime                     = date('H:i:s', $time);
            $updateData['auth_date']      = $authDate;
            $updateData['auth_time']      = $authTime;
            $updateData['inst_first']     = floatval($transactionResult['data']->amount);
            $updateData['request_status'] = 1;
        }

        return $this->update($updateData, $where);
    }

    /**
     *  更新授權結果
     *
     *  @param $authId int 授權資料流水號
     *  @param $transactionResult array 交易結果
     */
    public function updateLinepayTransactionAuth($authIdx, $paymentData, $transactionResult)
    {
        $where = array(
            'idx' => intval($authIdx),
        );

        $updateData = array(
            'response_code' => $transactionResult['data']['returnCode'],
            'response_msg'  => $transactionResult['data']['returnMessage'],
            'modify_time'   => date('Y-m-d H:i:s'),
            'auth_status'   => 2,
        );

        if ($transactionResult['code'] === 'LINEPAY_AUTH_00000') {
            $updateData['auth_status']    = 1;
            $updateData['request_status'] = 1;
            $updateData['trade_no']       = $transactionResult['data']['info']['transactionId'];
            $time                         = strtotime($transactionResult['data']['info']['transactionDate']);
            $updateData['auth_date']      = date('Y-m-d', $time);
            $updateData['auth_time']      = date('H:i:s', $time);
            $updateData['inst_first']     = floatval($paymentData['amount']);
            $updateData['pay_info']       = json_encode($transactionResult['data']['info']['payInfo']);
        } elseif ($transactionResult['code'] === 'LINEPAY_AUTHCHECK_00000') {
            $updateData['auth_status'] = 1;
            $updateData['trade_no']    = $transactionResult['data']['info']['transactionId'];
        }

        return $this->update($updateData, $where);
    }

    /**
     *  更新授權結果
     *
     *  @param $authId int 授權資料流水號
     *  @param $transactionResult array 交易結果
     */
    public function updateSuntechTransactionAuth($authIdx, $transactionResult)
    {
        $where = array(
            'idx' => intval($authIdx),
        );

        $updateData = array(
            'modify_time' => date('Y-m-d H:i:s'),
            'auth_status' => 2,
        );
        if (isset($transactionResult['data']->RespCode)) {
            $updateData['response_code'] = $transactionResult['data']->RespCode;
            $updateData['response_msg']  = $transactionResult['data']->RespCode_Str;
        } else {
            $updateData['response_code'] = 'FAIL';
            $updateData['response_msg']  = $transactionResult['data']->ErrorMessage;
        }

        if ($transactionResult['code'] === 'PAYMENT_00000') {
            $updateData['auth_status']    = 1;
            $updateData['trade_no']       = $transactionResult['data']->BuySafeNo;
            $authDate                     = date('Y-m-d');
            $authTime                     = date('H:i:s');
            $updateData['auth_code']      = $transactionResult['data']->ApproveCode;
            $updateData['auth_date']      = $authDate;
            $updateData['auth_time']      = $authTime;
            $updateData['inst_first']     = floatval($transactionResult['data']->MN);
            $updateData['request_status'] = 1;
        }

        return $this->update($updateData, $where);
    }

    public function updateAlipayOrderStatus($type, $authIdx, $queryReturnData)
    {
        $where = array(
            'idx' => intval($authIdx),
        );

        $status     = $queryReturnData->status;
        $updateData = array();
        if (is_numeric($status) && intval($status) > 0) {
            if ($type === 'P') {
                $updateData['auth_status'] = 1;
            } else if ($type === 'R') {
                $updateData['refund_status'] = 1;
            }
        }

        return $this->update($updateData, $where);
    }

    public function getAuthByMerchantIdAndOrderId($merchantId, $orderId)
    {
        $where = array(
            'merchant_id' => $merchantId,
            'order_id'    => $orderId,
        );

        return $this->select(false, 'array', $where);
    }

    public function getSuccessAuthToCancel(array $postData)
    {
        $where = array(
            'auth_status'   => 1,
            'lock_status'   => 1,
            'merchant_id'   => $postData['MerchantID'],
            'order_id'      => $postData['_Data']['OrderID'],
            'terminal_code' => $postData['TerminalID'],
        );

        return $this->select(false, 'array', $where);
    }

    public function getSuccessAuthToRequest(array $postData)
    {
        $where = array(
            'auth_status'    => 1,
            'lock_status'    => 1,
            'request_status' => 0,
            'cancel_status'  => 0,
            'merchant_id'    => $postData['MerchantID'],
            'order_id'       => $postData['_Data']['OrderID'],
            'terminal_code'  => $postData['TerminalID'],
            'currency'       => $postData['_Data']['Currency'],
        );

        $this->db->select('*')
            ->from($this->table)
            ->where($where);
        $query = $this->db->get();

        return $query->row_array();
    }

    public function getSuccessAuthToRefund(array $postData)
    {
        $where = array(
            'auth_status'   => 1,
            'lock_status'   => 1,
            'to_request'    => 0,
            'cancel_status' => 0,
            'merchant_id'   => $postData['MerchantID'],
            'order_id'      => $postData['_Data']['OrderID'],
            'terminal_code' => $postData['TerminalID'],
            'currency'      => $postData['_Data']['Currency'],
        );

        $this->db->select('*')
            ->from($this->table)
            ->where($where)
            ->group_start()
            ->where('request_status', 1)
            ->or_where('request_status', 2)
            ->or_where('refund_status', 1)
            ->or_where('refund_status', 2)
            ->group_end();
        $query = $this->db->get();

        return $query->row_array();
    }

    public function getSuccessAuthToRefundForAlipay(array $postData)
    {
        $where = array(
            'auth_status'   => 1,
            'lock_status'   => 1,
            'to_request'    => 0,
            'cancel_status' => 0,
            'refund_status' => 0,
            'merchant_id'   => $postData['MerchantID'],
            'order_id'      => $postData['_Data']['OrderID'],
            'terminal_code' => $postData['TerminalID'],
            'currency'      => $postData['_Data']['Currency'],
        );

        $this->db->select('*')
            ->from($this->table)
            ->where($where)
            ->group_start()
            ->where('request_status', 1)
            ->or_where('request_status', 2)
            ->group_end();
        $query = $this->db->get();

        return $query->row_array();
    }

    public function getSuccessAuthToRefundForLinepay(array $postData)
    {
        $where = array(
            'auth_status'   => 1,
            'lock_status'   => 1,
            'to_request'    => 0,
            'cancel_status' => 0,
            'merchant_id'   => $postData['MerchantID'],
            'order_id'      => $postData['_Data']['OrderID'],
            'terminal_code' => $postData['TerminalID'],
        );

        $this->db->select('*')
            ->from($this->table)
            ->where($where)
            ->group_start()
            ->where('request_status', 1)
            ->or_where('refund_status', 1)
            ->group_end();
        $query = $this->db->get();

        return $query->row_array();
    }

    /**
     *  根據 terminal code 取得最近一筆交易
     *  ToDo : 應該嚴謹的補上 product_idx & action_idx
     */
    public function getRecentlyAuthByTerminalCode($supplierIdx, $productIdx, $merchantId, $terminalCode)
    {
        $where = array(
            'supplier_idx'  => intval($supplierIdx),
            'product_idx'   => intval($productIdx),
            'merchant_id'   => $merchantId,
            'terminal_code' => $terminalCode,
        );

        $order = array(
            'create_time' => 'desc',
        );

        return $this->select(false, 'array', $where, $order, 1);
    }

    public function updateAmountByTypeAndAuthIdx($type, $authData, $amount)
    {
        $where = array(
            'idx' => $authData['idx'],
        );

        if ($type === 'request') {
            $updateData = array(
                'request_amount' => $amount,
            );
            if ((intval($authData['amount']) - intval($amount)) === 0) {
                $updateData['request_status'] = 1;
            } else {
                $updateData['request_status'] = 2;
            }
        } else if ($type === 'refund') {
            $updateData = array(
                'refund_amount' => $amount,
            );
            if ((intval($authData['amount']) - intval($amount)) === 0) {
                $updateData['refund_status'] = 1;
            } else {
                $updateData['refund_status'] = 2;
            }
        }

        return $this->update($updateData, $where);
    }

    public function updateAuthStatus($type, $authIdx, $status)
    {
        $where = array(
            'idx' => intval($authIdx),
        );

        $updateData = array();
        switch ($type) {
            case 'cancel':
                $updateData['cancel_status'] = intval($status);
                break;
            case 'lock':
                $updateData['lock_status'] = intval($status);
                break;
            case 'toRequest':
                $updateData['to_request'] = intval($status);
                break;
            case 'request':
                $updateData['request_status'] = intval($status);
                break;
            default:
                break;
        }

        return $this->update($updateData, $where);
    }

    public function updateAuthForLinepay($authIdx, $returnData)
    {
        $where = array(
            'idx' => intval($authIdx),
        );

        $time       = strtotime($returnData['info']['transactionDate']);
        $updateData = array(
            'auth_status'    => 1,
            'request_status' => 1,
            'trade_no'       => $returnData['info']['transactionId'],
            'auth_date'      => date('Y-m-d', $time),
            'auth_time'      => date('H:i:s', $time),
            'pay_info'       => json_encode($returnData['info']['payInfo']),
        );

        return $this->update($updateData, $where);
    }

    public function getAuthForPaymentQuery($supplierIdx, $productIdx, $postData)
    {
        $where = array(
            'supplier_idx'  => intval($supplierIdx),
            'product_idx'   => intval($productIdx),
            'merchant_id'   => $postData['MerchantID'],
            'terminal_code' => $postData['TerminalID'],
        );
        if (isset($postData['_Data']['OrderID'])) {
            $where['order_id'] = $postData['_Data']['OrderID'];
            $result            = $this->select(true, 'array', $where);
        } else if (isset($postData['_Data']['CardNo'])) {
            $where['token'] = openssl_digest($postData['_Data']['CardNo'], 'sha256');
            $order          = array('create_time', 'desc');
            $result         = $this->select(true, 'array', $where, $order, 5);
        }

        return $result;
    }

    public function getAuthTotalForNowByToday($terminalCode, $merchantId, array $service)
    {
        $where = array(
            'terminal_code' => $terminalCode,
            'merchant_id'   => $merchantId,
            'auth_date'     => date('Y-m-d'),
            'auth_status'   => 1,
        );

        $this->db->select('count(*) AS total, sum(amount) AS amount');
        $this->db->from($this->table);
        $this->db->where($where);

        $query = $this->db->get();

        return $query->row_array();
    }

    public function getBatchAuthToRequest()
    {
        $where = array(
            'active_request' => 1,
            'auth_status'    => 1,
            'cancel_status'  => 0,
            'request_status' => 0,
            'refund_status'  => 0,
            'lock_status'    => 1,
            'to_request'     => 1,
            'create_time >=' => date('Y-m-d', strtotime("-1 days")) . ' 21:00:00',
            'modify_time <=' => date('Y-m-d') . ' 21:00:00',
        );

        return $this->select(true, 'array', $where, array('idx' => 'asc'));
    }

    public function getAuthByOrderIdAndTradeNo($orderId, $tradeNo)
    {
        $where = array(
            'order_id' => $orderId,
            'trade_no' => $tradeNo,
        );

        return $this->select(false, 'array', $where);
    }

    public function getAuthByIdx($authIdx)
    {
        $where = array(
            'idx' => intval($authIdx),
        );

        return $this->select(false, 'array', $where);
    }
}
