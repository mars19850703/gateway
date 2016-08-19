<?php

class Log_payment_query_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function insertLogQuery(array $data, array $postData)
    {
        // 記錄log
        $insertLogData = array(
            'log_payment_gateway_idx' => (isset($data['paymentLogId'])) ? $data['paymentLogId'] : 0,
            'supplier_idx'            => (isset($data['supplierIdx'])) ? $data['supplierIdx'] : 0,
            'input_data'              => json_encode($data),
            'post_data'               => json_encode($postData),
            'merchant_id'             => (isset($data['MerchantID'])) ? $data['MerchantID'] : 0,
            'order_id'                => (isset($data['MerchantOrderNo'])) ? $data['MerchantOrderNo'] : 0,
            'create_time'             => date("Y-m-d H:i:s"),
        );
        return $this->insert($insertLogData);
    }

    /**
     * 新增台新支付寶logPayment資料
     * @param  array  $postData     [傳入]
     * @param  array  $data  [轉換完的資料]
     * @param  array  $inputData    [要curl出去的資料]
     * @return int           [新增成功的id]
     */
    public function insertAlipayLogQuery(array $postData, array $data, array $inputData)
    {
        // 記錄log
        $insertLogData = array(
            'log_payment_gateway_idx' => (isset($data['paymentLogIdx'])) ? $data['paymentLogIdx'] : 0,
            'supplier_idx'            => (isset($data['supplierIdx'])) ? $data['supplierIdx'] : 0,
            'input_data'              => json_encode($data),
            'post_data'               => json_encode($inputData),
            'merchant_id'             => (isset($postData['MerchantID'])) ? $postData['MerchantID'] : 0,
            'order_id'                => (isset($data['id'])) ? $data['id'] : 0,
            'create_time'             => date("Y-m-d H:i:s"),
        );
        if ($inputData['type'] === 'P') {
            $insertLogData['type'] = 'auth';
        } else if ($inputData['type'] === 'R') {
            $insertLogData['type'] = 'refund';
        }

        return $this->insert($insertLogData);
    }

    public function insertLogQueryForLinepay($paymentLogIdx, $authData)
    {
        // 記錄log
        $insertLogData = array(
            'log_payment_gateway_idx' => (isset($paymentLogIdx)) ? $paymentLogIdx : 0,
            'supplier_idx'            => (isset($authData['supplier_idx'])) ? $authData['supplier_idx'] : 0,
            'type'                    => 'query',
            'input_data'              => json_encode($authData),
            'post_data'               => json_encode($authData),
            'merchant_id'             => (isset($authData['merchant_id'])) ? $authData['merchant_id'] : 0,
            'order_id'                => (isset($authData['order_id'])) ? $authData['order_id'] : 0,
            'create_time'             => date("Y-m-d H:i:s"),
        );

        return $this->insert($insertLogData);

    }

    public function insertLinepayLogQuery(array $postData)
    {
        // 記錄log
        $insertLogData = array(
            'log_payment_gateway_idx' => (isset($postData['paymentLogIdx'])) ? $postData['paymentLogIdx'] : 0,
            'supplier_idx'            => (isset($postData['service'][0])) ? intval($postData['service'][0]) : 0,
            'type'                    => 'confirm',
            'input_data'              => json_encode($postData),
            'post_data'               => json_encode($postData),
            'merchant_id'             => (isset($postData['MerchantID'])) ? $postData['MerchantID'] : 0,
            'order_id'                => (isset($postData['_Data']['OrderID'])) ? $postData['_Data']['OrderID'] : 0,
            'create_time'             => date("Y-m-d H:i:s"),
        );

        return $this->insert($insertLogData);
    }
}
