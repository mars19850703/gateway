<?php

class Suntech_payment_log extends BaseLibrary
{
    public function __construct()
    {
        parent::__construct();
        $this->ci->load->model("log_payment_gateway_model");
    }

    public function insertPaymentLog($action, $connectLogIdx, $postData)
    {
        switch ($action) {
            case 'auth':
                $insertData = $this->buildAuthLogData($postData);
                break;
            case 'cancel':
            case 'request':
            case 'refund':
            case 'query':
            case 'queryToUpdate':
                $insertData = $this->buildRequestLogData($postData);
                break;
            case 'issue':
                $insertData = $this->bulidInvoiceIssueLogData($postData);
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

    private function buildAuthLogData($postData)
    {
        // 去除信用卡卡號，信用卡到期日，信用卡後面三碼
        unset($postData["_Data"]["CardNo"]);
        unset($postData["_Data"]["CardExpire"]);
        unset($postData["_Data"]["Cvv2"]);

        $data = array(
            "transaction_no"  => $postData["transactionNo"],
            "merchant_id"     => $postData["MerchantID"],
            "service_code"    => $postData["_Data"]["ServiceCode"],
            "order_id"        => $postData["_Data"]["OrderID"],
            "input_data"      => json_encode($postData),
        );

        return $data;
    }

    private function buildRequestLogData($postData)
    {
        $data = array(
            // "transaction_no"  => $postData["transactionNo"],
            "merchant_id"     => $postData["MerchantID"],
            "service_code"    => $postData["_Data"]["ServiceCode"],
            "order_id"        => $postData["_Data"]["OrderID"],
            "input_data"      => json_encode($postData),
        );

        return $data;
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
}
