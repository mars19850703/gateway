<?php

class Query_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function insertQuery($authIdx, $postData)
    {
        $insertData = array(
            'auth_idx'     => $authIdx,
            'merchant_id'  => $postData['MerchantID'],
            'supplier_idx' => intval($postData['service'][0]),
            'product_idx'  => intval($postData['service'][1]),
            'action_idx'   => intval($postData['service'][2]),
            'option_idx'   => intval($postData['service'][3]),
            'order_id'     => $postData['_Data']['OrderID'],
            'amount'       => $postData['_Data']['Amount'],
            'query_status' => 0,
            'create_time'  => date('Y-m-d H:i:s'),
            'ip'           => $this->input->ip_address(),
            'is_tms'       => $postData['_Data']['is_tms'],
        );

        return $this->insert($insertData);
    }

    public function updateQuery($queryIdx, $transactionResult)
    {
        $where = array(
            'idx' => $queryIdx,
        );

        $updateData = array(
            'pay_time'      => $transactionResult['data']->Result->PayTime,
            'fund_time'     => $transactionResult['data']->Result->FundTime,
            'response_code' => $transactionResult['data']->Status,
            'response_msg'  => $transactionResult['data']->Message,
            'modify_time'   => date('Y-m-d H:i:s'),
        );

        if ($transactionResult['data']->Status === 'SUCCESS') {
            $updateData['query_status'] = 1;
        }

        return $this->update($updateData, $where);
    }
}
