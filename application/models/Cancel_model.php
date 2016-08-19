<?php

class Cancel_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function insertCancel($authIdx, $postData)
    {
        $insertData = array(
            "auth_idx"      => $authIdx,
            "merchant_id"   => $postData["MerchantID"],
            "supplier_idx"  => intval($postData["service"][0]),
            "product_idx"   => intval($postData["service"][1]),
            "action_idx"    => intval($postData["service"][2]),
            "option_idx"    => intval($postData["service"][3]),
            "order_id"      => $postData["_Data"]["OrderID"],
            "currency"      => $postData["_Data"]["Currency"],
            "amount"        => $postData["_Data"]["Amount"],
            "cancel_status" => 0,
            "create_time"   => date("Y-m-d H:i:s"),
            "ip"            => $this->input->ip_address(),
            'is_tms'        => $postData["_Data"]["is_tms"],
        );

        return $this->insert($insertData);
    }

    public function updateCancelForResult($cancelIdx, $transactionResult)
    {
        $where = array(
            "idx" => $cancelIdx,
        );

        $updateData = array(
            "response_code" => $transactionResult["data"]->Status,
            "response_msg"  => $transactionResult["data"]->Message,
            "modify_time"   => date("Y-m-d H:i:s"),
        );

        if ($transactionResult["data"]->Status === "SUCCESS") {
            $updateData["cancel_status"] = 1;
        }

        return $this->update($updateData, $where);
    }

    public function getCancelTotalForNowByToday($terminalCode, $merchantId, array $service)
    {
        $where = array(
            'a.terminal_code'  => $terminalCode,
            'c.merchant_id'    => $merchantId,
            // 'c.supplier_idx'   => intval($service[0]),
            // 'c.product_idx'    => intval($service[1]),
            // 'c.action_idx'     => intval($service[2]),
            // 'c.option_idx'     => intval($service[3]),
            'c.create_time >=' => date('Y-m-d') . ' 00:00:00',
            'c.create_time <=' => date('Y-m-d') . ' 23:59:59',
            'c.cancel_status'  => 1,
        );

        $this->db->select('count(*) AS total, sum(c.amount) AS amount');
        $this->db->from($this->table . ' AS c');
        $this->db->join('Auth AS a', 'c.auth_idx = a.idx', 'left');
        $this->db->where($where);

        $query = $this->db->get();

        return $query->row_array();
    }

    public function getCancelByAuthIdx($authIdx)
    {
        $where = array(
            'auth_idx' => intval($authIdx),
        );

        return $this->select(false, 'array', $where);
    }
}
