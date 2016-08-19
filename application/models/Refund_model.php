<?php

class Refund_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function insertRefund($authIdx, $postData)
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
            "refund_status" => 0,
            "create_time"   => date("Y-m-d H:i:s"),
            "ip"            => $this->input->ip_address(),
            'is_tms'        => $postData["_Data"]["is_tms"],
        );

        return $this->insert($insertData);
    }

    public function updateRefundForResult($refundIdx, $transactionResult)
    {
        $where = array(
            "idx" => $refundIdx,
        );

        $updateData = array(
            "response_code" => $transactionResult["data"]->Status,
            "response_msg"  => $transactionResult["data"]->Message,
            "modify_time"   => date("Y-m-d H:i:s"),
        );

        if ($transactionResult["data"]->Status === "SUCCESS") {
            $updateData["refund_status"] = 1;
        }

        return $this->update($updateData, $where);
    }

    public function updateAlipayRefundForResult($refundIdx, $transactionResult)
    {
        $where = array(
            "idx" => $refundIdx,
        );

        $updateData = array(
            "response_code" => $transactionResult["data"]->return_code,
            "response_msg"  => $transactionResult["data"]->return_message,
            "modify_time"   => date("Y-m-d H:i:s"),
        );

        if ($transactionResult["data"]->return_code === "000") {
            $updateData["refund_status"] = 1;
        }

        return $this->update($updateData, $where);
    }

    public function updateRefundForLinepay($refundIdx, $transactionResult)
    {
        $where = array(
            "idx" => $refundIdx,
        );

        $updateData = array(
            "response_code" => $transactionResult["data"]['returnCode'],
            "response_msg"  => $transactionResult["data"]['returnMessage'],
            "modify_time"   => date("Y-m-d H:i:s"),
        );

        if ($transactionResult["data"]['returnCode'] === "0000") {
            $updateData["refund_status"] = 1;
        }

        return $this->update($updateData, $where);
    }

    public function getRefundTotalForNowByToday($terminalCode, $merchantId, array $service)
    {
        $where = array(
            'a.terminal_code'  => $terminalCode,
            'r.merchant_id'    => $merchantId,
            // 'r.supplier_idx'   => intval($service[0]),
            // 'r.product_idx'    => intval($service[1]),
            // 'r.action_idx'     => intval($service[2]),
            // 'r.option_idx'     => intval($service[3]),
            'r.create_time >=' => date('Y-m-d') . ' 00:00:00',
            'r.create_time <=' => date('Y-m-d') . ' 23:59:59',
            'r.refund_status'  => 1,
        );

        $this->db->select('count(*) AS total, sum(r.amount) AS amount');
        $this->db->from($this->table . ' AS r');
        $this->db->join('Auth AS a', 'r.auth_idx = a.idx', 'left');
        $this->db->where($where);

        $query = $this->db->get();

        return $query->row_array();
    }

    public function getRefundByAuthIdx($authIdx)
    {
        $where = array(
            'auth_idx' => intval($authIdx),
        );

        return $this->select(false, 'array', $where);
    }
}
