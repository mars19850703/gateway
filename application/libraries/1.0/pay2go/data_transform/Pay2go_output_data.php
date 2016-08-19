<?php

class Pay2go_output_data extends BaseLibrary
{
    protected $config;
    protected $response;

    public function __construct()
    {
        parent::__construct();
        $this->resetResponse();

        // load config
        include dirname(__FILE__) . "/../Config.php";
        $this->config = $pay2go;

        $this->ci->load->library('check_data/credit_info');
    }

    protected function resetResponse()
    {
        $this->response = array(
            'Status'  => 'PAYMENT_00000',
            'Message' => '',
        );
    }

    public function invoiceIssueOutput($actionCode, array $postData, array $transactionResult)
    {
        $this->resetResponse();
        $this->response['Status']         = $transactionResult['code'];
        $this->response['GatewayStatus']  = $transactionResult['data']->Status;
        $this->response['GatewayMessage'] = $transactionResult['data']->Message;
        if ($transactionResult['code'] === 'INVOICE_ISSUE_00000') {
            $this->response['Result'] = array(
                'Status'         => $postData['_Data']['Status'],
                'MerchantID'     => $transactionResult['data']->Result->MerchantID,
                'InvoiceTransNo' => $transactionResult['data']->Result->InvoiceTransNo,
                'OrderID'        => $transactionResult['data']->Result->MerchantOrderNo,
                'TotalAmount'    => $transactionResult['data']->Result->TotalAmt,
                'InvoiceNumber'  => $transactionResult['data']->Result->InvoiceNumber,
                'RandomNum'      => $transactionResult['data']->Result->RandomNum,
                'CreateTime'     => $transactionResult['data']->Result->CreateTime,
                'BarCode'        => $transactionResult['data']->Result->BarCode,
                'QRcodeL'        => $transactionResult['data']->Result->QRcodeL,
                'QRcodeR'        => $transactionResult['data']->Result->QRcodeR,
            );
        } else {
            $this->response['Result'] = array(
                'Status'         => '',
                'MerchantID'     => '',
                'InvoiceTransNo' => '',
                'OrderID'        => '',
                'TotalAmount'    => '',
                'InvoiceNumber'  => '',
                'RandomNum'      => '',
                'CreateTime'     => '',
                'BarCode'        => '',
                'QRcodeL'        => '',
                'QRcodeR'        => '',
            );
        }

        return $this->response;
    }

    public function invoiceTouchOutput($actionCode, array $postData, array $transactionResult)
    {
        $this->resetResponse();
        $this->response['Status']         = $transactionResult['code'];
        $this->response['GatewayStatus']  = $transactionResult['data']->Status;
        $this->response['GatewayMessage'] = $transactionResult['data']->Message;
        if ($transactionResult['code'] === 'INVOICE_TOUCH_00000') {
            $this->response['Result'] = array(
                'MerchantID'     => $transactionResult['data']->Result->MerchantID,
                'InvoiceTransNo' => $transactionResult['data']->Result->InvoiceTransNo,
                'OrderID'        => $transactionResult['data']->Result->MerchantOrderNo,
                'TotalAmount'    => $transactionResult['data']->Result->TotalAmt,
                'InvoiceNumber'  => $transactionResult['data']->Result->InvoiceNumber,
                'RandomNum'      => $transactionResult['data']->Result->RandomNum,
                'CreateTime'     => $transactionResult['data']->Result->CreateTime,
            );
        } else {
            $this->response['Result'] = array(
                'MerchantID'     => '',
                'InvoiceTransNo' => '',
                'OrderID'        => '',
                'TotalAmount'    => '',
                'InvoiceNumber'  => '',
                'RandomNum'      => '',
                'CreateTime'     => '',
            );
        }

        return $this->response;
    }

    public function invoiceInvalidOutput($actionCode, array $postData, array $transactionResult)
    {
        $this->resetResponse();
        $this->response['Status']         = $transactionResult['code'];
        $this->response['GatewayStatus']  = $transactionResult['data']->Status;
        $this->response['GatewayMessage'] = $transactionResult['data']->Message;
        if ($transactionResult['code'] === 'INVOICE_INVALID_00000') {
            $this->response['Result'] = array(
                'MerchantID'     => $transactionResult['data']->Result->MerchantID,
                'InvoiceNumber'  => $transactionResult['data']->Result->InvoiceNumber,
                'CreateTime'     => $transactionResult['data']->Result->CreateTime,
            );
        } else {
            $this->response['Result'] = array(
                'MerchantID'     => '',
                'InvoiceNumber'  => '',
                'CreateTime'     => '',
            );
        }

        return $this->response;
    }

    public function invoiceAllowanceOutput($actionCode, array $postData, array $transactionResult)
    {
        $this->resetResponse();
        $this->response['Status']         = $transactionResult['code'];
        $this->response['GatewayStatus']  = $transactionResult['data']->Status;
        $this->response['GatewayMessage'] = $transactionResult['data']->Message;
        if ($transactionResult['code'] === 'INVOICE_ALLOWANCE_00000') {
            $this->response['Result'] = array(
                'MerchantID'     => $transactionResult['data']->Result->MerchantID,
                'InvoiceNumber'  => $transactionResult['data']->Result->InvoiceNumber,
                'CreateTime'     => $transactionResult['data']->Result->CreateTime,
            );
        } else {
            $this->response['Result'] = array(
                'MerchantID'     => '',
                'InvoiceNumber'  => '',
                'CreateTime'     => '',
            );
        }

        return $this->response;
    }

    public function invoiceSearchOutput($actionCode, array $postData, array $transactionResult)
    {
        $this->resetResponse();
        $this->response['Status']         = $transactionResult['code'];
        $this->response['GatewayStatus']  = $transactionResult['data']->Status;
        $this->response['GatewayMessage'] = $transactionResult['data']->Message;
        if ($transactionResult['code'] === 'INVOICE_SEARCH_00000') {
            $this->response['Result'] = array(
                'MerchantID'     => $transactionResult['data']->Result->MerchantID,
                'InvoiceNumber'  => $transactionResult['data']->Result->InvoiceNumber,
                'CreateTime'     => $transactionResult['data']->Result->CreateTime,
            );
        } else {
            $this->response['Result'] = array(
                'MerchantID'     => '',
                'InvoiceNumber'  => '',
                'CreateTime'     => '',
            );
        }

        return $this->response;
    }
}
