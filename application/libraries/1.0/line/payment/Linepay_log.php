<?php

class Linepay_log extends BaseLibrary
{
    public function __construct()
    {
        parent::__construct();
        $this->ci->load->model('log_payment_gateway_model');
    }

    public function insertPaymentLog($action, $connectLogIdx, $postData)
    {
        switch ($action) {
            case 'auth':
            case 'refund':
                $insertData = $this->buildPaymentLogData($postData);
                break;
            case 'query':
                $insertData = array(
                    'merchant_id'    => $postData['MerchantID'],
                    'service_code'   => $postData['_Data']['ServiceCode'],
                    'order_id'       => $postData['_Data']['OrderID'],
                    'input_data'     => json_encode($postData),
                );
                break;
            case 'confirmCancel':
                $insertData = array(
                    'merchant_id'    => $postData['merchant_id'],
                    'service_code'   => str_pad($postData['supplier_idx'], 3, '0', STR_PAD_LEFT) . str_pad($postData['product_idx'], 3, '0', STR_PAD_LEFT) . str_pad($postData['action_idx'], 3, '0', STR_PAD_LEFT) . str_pad($postData['option_idx'], 3, '0', STR_PAD_LEFT),
                    'order_id'       => $postData['order_id'],
                    'input_data'     => json_encode($postData),
                );
                break;
            default:
                $insertData = array();
                break;
        }

        $insertData['type']            = $action;
        $insertData['log_connect_idx'] = intval($connectLogIdx);
        $insertData['create_time']     = date('Y-m-d H:i:s');

        return $this->ci->log_payment_gateway_model->insertPaymentGatewayLog($insertData);
    }

    public function updatePaymentLog($paymentLogIdx, $result)
    {
        return $this->ci->log_payment_gateway_model->updatePaymentGatewayLog($paymentLogIdx, $result);
    }

    private function buildPaymentLogData($postData)
    {
        $data = array(
            'transaction_no' => $postData['transactionNo'],
            'merchant_id'    => $postData['MerchantID'],
            'service_code'   => $postData['_Data']['ServiceCode'],
            'order_id'       => $postData['_Data']['OrderID'],
            'input_data'     => json_encode($postData),
        );

        return $data;
    }
}
