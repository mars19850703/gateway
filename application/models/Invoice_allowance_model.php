<?php

class Invoice_allowance_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function insertInvoiceAllowance($invoiceData, $postData)
    {
        $insertData = array(
            'invoice_idx'      => intval($invoiceData['idx']),
            'merchant_id'      => $postData['MerchantID'],
            'supplier_idx'     => intval($postData['service'][0]),
            'product_idx'      => intval($postData['service'][1]),
            'action_idx'       => intval($postData['service'][2]),
            'option_idx'       => intval($postData['service'][3]),
            'order_id'         => $invoiceData['order_id'],
            'invoice_number'   => $postData['_Data']['InvoiceNumber'],
            'amount'           => floatval($postData['_Data']['TotalAmount']),
            'allowance_status' => 0,
            'item_name'        => implode('|', $postData['_Data']['ItemName']),
            'item_count'       => implode('|', $postData['_Data']['ItemCount']),
            'item_unit'        => implode('|', $postData['_Data']['ItemUnit']),
            'item_price'       => implode('|', $postData['_Data']['ItemPrice']),
            'item_amount'      => implode('|', $postData['_Data']['ItemAmount']),
            'item_tax_amount'  => implode('|', $postData['_Data']['ItemTaxAmount']),
            'create_time'      => date('Y-m-d H:i:s'),
            'ip'               => $this->input->ip_address(),
        );

        return $this->insert($insertData);
    }

    public function updateInvoiceAllowance($allowanceIdx, $transactionResult)
    {
        $where = array(
            'idx' => intval($allowanceIdx),
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
