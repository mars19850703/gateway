<?php

/**
 *
 */
class Log_invoice_touch_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *	新增logPayment資料
     *	@param  array  $data     [原始資料]
     *	@param  array  $postData [要curl出去的資料]
     *	@return int           [新增成功的id]
     */
    public function insertLogInvoiceTouch(array $data, array $postData)
    {
        // 記錄log
        $insertLogData = array(
            'log_payment_gateway_idx' => (isset($data['paymentLogIdx'])) ? $data['paymentLogIdx'] : 0,
            'supplier_idx'            => (isset($data['supplierIdx'])) ? $data['supplierIdx'] : 0,
            'input_data'              => json_encode($data),
            'post_data'               => json_encode($postData),
            'merchant_id'             => (isset($data['MerchantID'])) ? $data['MerchantID'] : 0,
            'order_id'                => (isset($data['MerchantOrderNo'])) ? $data['MerchantOrderNo'] : 0,
            'amount'                  => $data['TotalAmt'],
            'create_time'             => date("Y-m-d H:i:s"),
        );

        return $this->insert($insertLogData);
    }
}
