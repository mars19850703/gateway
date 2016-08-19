<?php

class Log_payment_cancel_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 新增logPayment資料
     * @param  array  $data     [原始資料]
     * @param  array  $postData [要curl出去的資料]
     * @return int           [新增成功的id]
     */
    public function insertLogCancel(array $data, array $postData)
    {
        // 過濾資料
        $logData = $this->filterLogPayment($data);
        // 記錄log
        $insertLogData = array(
            'log_payment_gateway_idx' => (isset($data['paymentLogId'])) ? $data['paymentLogId'] : 0,
            'supplier_idx'            => (isset($data['supplierIdx'])) ? $data['supplierIdx'] : 0,
            'input_data'              => json_encode($logData),
            'post_data'               => json_encode($postData),
            'merchant_id'             => (isset($data['MerchantID'])) ? $data['MerchantID'] : 0,
            'order_id'                => (isset($data['MerchantOrderNo'])) ? $data['MerchantOrderNo'] : 0,
            'amount'                  => $data['Amt'],
            'create_time'             => date("Y-m-d H:i:s"),
        );
        return $this->insert($insertLogData);
    }

    public function insertLogCancelForLinepay($paymentLogIdx, $authData)
    {
        // 記錄log
        $insertLogData = array(
            'log_payment_gateway_idx' => (!empty($paymentLogIdx)) ? $paymentLogIdx : 0,
            'supplier_idx'            => (isset($authData['supplier_idx'])) ? $authData['supplier_idx'] : 0,
            'input_data'              => json_encode($authData),
            'post_data'               => json_encode($authData),
            'merchant_id'             => (isset($authData['merchant_id'])) ? $authData['merchant_id'] : 0,
            'order_id'                => (isset($authData['order_id'])) ? $authData['order_id'] : 0,
            'amount'                  => floatval($authData['amount']),
            'create_time'             => date("Y-m-d H:i:s"),
        );
        return $this->insert($insertLogData);
    }

    /**
     * 用條件查詢log資料
     * @param  boolean $multi 資料是多筆還是單筆
     * @param  array   $where 要查詢的條件
     * @return array         回傳log資料
     */
    public function getByParam($multi = false, array $where)
    {
        $this->db->from($this->table);

        if ($where) {
            $this->db->where($where);
        }

        $result = $this->db->get();

        if ($multi === true) {
            return $result->fetch_array();
        } else {
            return $result->row_array();
        }
    }

    /**
     *  過濾資料
     * @param  [array] $data [要過濾的Array]
     * @return [array]       [過濾完成的Array]
     */
    public function filterLogPayment($data)
    {
        // 要移除的參數
        $unsetIndex = array(
            'CardNo',
            'Exp',
            'CVC',
        );

        // 把卡號換成前六後四
        if (isset($data['CardNo']) && $data['CardNo']) {
            $data["Card6No"] = substr($data["CardNo"], 0, 6);
            $data["Card4No"] = substr($data["CardNo"], -4, 4);
        }

        // 移除參數
        foreach ($unsetIndex as $index) {
            if (isset($data[$index])) {
                unset($data[$index]);
            }
        }

        return $data;
    }

}
