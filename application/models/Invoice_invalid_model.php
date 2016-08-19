<?php

class Invoice_invalid_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function insertInvoiceInvalid($invoiceData, $postData)
    {
        $insertData = array(
            'invoice_idx'    => intval($invoiceData['idx']),
            'merchant_id'    => $postData['MerchantID'],
            'supplier_idx'   => intval($postData['service'][0]),
            'product_idx'    => intval($postData['service'][1]),
            'action_idx'     => intval($postData['service'][2]),
            'option_idx'     => intval($postData['service'][3]),
            'order_id'       => $invoiceData['order_id'],
            'invoice_number' => $postData['_Data']['InvoiceNumber'],
            'invoice_reason' => $postData['_Data']['InvalidReason'],
            'amount'         => floatval($invoiceData['amount']),
            'invalid_status' => 0,
            'create_time'    => date('Y-m-d H:i:s'),
            'ip'             => $this->input->ip_address(),
        );

        return $this->insert($insertData);
    }

    public function updateInvoiceInvalid($touchIdx, $transactionResult)
    {
        $where = array(
            'idx' => intval($touchIdx),
        );

        $updateData = array(
            'response_code' => $transactionResult['data']->Status,
            'response_msg'  => $transactionResult['data']->Message,
            'modify_time'   => date('Y-m-d H:i:s'),
        );

        if ($transactionResult['data']->Status === 'SUCCESS') {
            $updateData['invalid_status'] = 1;
        }

        return $this->update($updateData, $where);
    }
}
