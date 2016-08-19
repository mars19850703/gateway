<?php

class Payment_log extends BaseLibrary
{
    public function __construct()
    {
        parent::__construct();
        $this->ci->load->model("log_payment_gateway_model");
    }

    public function insertPaymentLog($action, $connectLogIdx, $postData)
    {
        switch ($action) {
            case 'issue':
            case 'touch':
                $insertData = $this->bulidInvoiceIssueLogData($postData);
                break;
            case 'invalid':
                $insertData = $this->bulidInvoiceInvalidLogData($postData);
                break;
            default:
                $insertData = array();
                break;
        }

        $insertData["type"]            = $action;
        $insertData["log_connect_idx"] = intval($connectLogIdx);
        $insertData["create_time"]     = date("Y-m-d H:i:s");

        return $this->ci->log_payment_gateway_model->insertPaymentGatewayLog($insertData);
    }

    public function updatePaymentLog($paymentLogIdx, $result)
    {
        return $this->ci->log_payment_gateway_model->updatePaymentGatewayLog($paymentLogIdx, $result);
    }

    private function bulidInvoiceIssueLogData($postData)
    {
        $data = array(
            "merchant_id"     => $postData["MerchantID"],
            "service_code"    => $postData["_Data"]["ServiceCode"],
            "order_id"        => $postData["_Data"]["OrderID"],
            "input_data"      => json_encode($postData),
        );

        return $data;
    }

    private function bulidInvoiceInvalidLogData($postData)
    {
        $data = array(
            "merchant_id"     => $postData["MerchantID"],
            "service_code"    => $postData["_Data"]["ServiceCode"],
            "input_data"      => json_encode($postData),
        );

        return $data;
    }
}
