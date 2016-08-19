<?php

class Notify extends BaseLibrary
{
    public function __construct()
    {
        parent::__construct();
    }

    public function refund()
    {
        $fileds = array(
            'Status',
            'Message',
            'Result'
        );

        $this->data['postData']           = $this->ci->input->post($fields, $true);
        $this->data['postData']['Result'] = json_decode($this->data['postData']['Result'], true);

        // get merchant id
        $this->data['postData']['merchantId'] = substr($this->data['postData']['Result']['MerchantID'], 3, 7);
        // get terminal id
        $this->data['postData']['terminalId'] = substr($this->data['postData']['Result']['MerchantID'], 10, 4)

        // get auth info
        $this->ci->load->model('auth_model');
        $auth = $this->ci->auth_model->getAuthByMerchantIdAndOrderId($this->data['postData']['merchantId'], $this->data['postData']['Result']['MerchantOrderNo']);

        $this->ci->load->model('log_payment_notify_model');
        $insertData = array(
            'auth_idx'    => $auth['idx'],
            'type'        => __FUNCTION__,
            'status'      => $this->data['postData']['Status'],
            'message'     => $this->data['postData']['Message'],
            'merchant_id' => $this->data['postData']['merchantId'],
            'terminal_id' => $this->data['postData']['terminalId'],
            'amount'      => floatval($this->data['postData']['Result']['Amt']),
            'order_id'    => floatval($this->data['postData']['Result']['MerchantOrderNo']),
            'trade_no'    => floatval($this->data['postData']['Result']['TradeNo']),
            'create_time' => date('Y-m-d H:i:s')
        );

        $this->ci->log_payment_notify_model->insert($insertData);
    }
}
