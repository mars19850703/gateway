<?php

class Invoice_touch_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function insertInvoiceTouch($invoiceIdx, $postData)
    {
        $insertData = array(
            "invoice_idx"  => intval($invoiceIdx),
            "merchant_id"  => $postData["MerchantID"],
            "supplier_idx" => intval($postData["service"][0]),
            "product_idx"  => intval($postData["service"][1]),
            "action_idx"   => intval($postData["service"][2]),
            "option_idx"   => intval($postData["service"][3]),
            "order_id"     => $postData["_Data"]["OrderID"],
            "amount"       => $postData["_Data"]["TotalAmt"],
            "touch_status" => 0,
            "create_time"  => date("Y-m-d H:i:s"),
            "ip"           => $this->input->ip_address(),
        );

        return $this->insert($insertData);
    }

    public function updateInvoiceTouch($touchIdx, $transactionResult)
    {
        $where = array(
            'idx' => intval($touchIdx),
        );

        $updateData = array(
            'response_code' => $transactionResult['data']->Status,
            'response_msg'  => $transactionResult['data']->Message,
            'modify_time'   => date('Y-m-d H:i:s'),
        );

        if ($transactionResult["data"]->Status === "SUCCESS") {
            $updateData["touch_status"] = 1;
        }

        return $this->update($updateData, $where);
    }
}
