<?php

class Invoice_search_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function insertInvoiceSearch($invoiceData, $postData)
    {
        $insertData = array(
            'invoice_idx'    => intval($invoiceData['idx']),
            'merchant_id'    => $postData['MerchantID'],
            'supplier_idx'   => intval($postData['service'][0]),
            'product_idx'    => intval($postData['service'][1]),
            'action_idx'     => intval($postData['service'][2]),
            'option_idx'     => intval($postData['service'][3]),
            'invoice_number' => $postData['_Data']['InvoiceNumber'],
            'search_status'   => 0,
            'create_time'    => date('Y-m-d H:i:s'),
            'ip'             => $this->input->ip_address(),
        );

        return $this->insert($insertData);
    }

    public function updateInvoiceSearch($touchIdx, $transactionResult)
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
            $updateData['search_status'] = 1;
        }

        return $this->update($updateData, $where);
    }
}
