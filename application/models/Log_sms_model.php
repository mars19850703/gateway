<?php

class Log_sms_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 新增logSms資料
     * @param  array  $data     原始資料
     * @return int           新增成功的id
     */
    public function insertLogSms(array $data, $tradeNo, $mobile, $content, $responseUrl = '')
    {
        // 記錄log
        $insertLogData = array(
            'log_sms_gateway_id' => $data['log_sms_gateway_id'],
            'sms_company'        => (isset($data['sms_company'])) ? $data['sms_company'] : '',
            'store_id'           => $data['StoreID'],
            'service_config_idx' => (isset($data['ServiceID'])) ? $data['ServiceID'] : 0,
            'sms_type'           => (isset($data['sms_type'])) ? $data['sms_type'] : '',
            'mobile'             => $mobile,
            'content'            => $content,
            'sms_status'         => 1,
            'trade_no'           => $tradeNo,
            'response_url'       => $responseUrl,
            // 'modify_operator'    => 0,
            // 'modify_manager'     => 0,
            'create_time'        => date("Y-m-d H:i:s"),
        );
        return $this->insert($insertLogData);
    }

    /**
     * 用條件查詢log資料
     * @param  boolean $multi 資料是多筆還是單筆
     * @param  array   $where 要查詢的條件
     * @return array         回傳log資料
     */
    public function getByParam($multi = false, array $where)
    {
        return $this->select(false, "array", $where);
    }

    public function CheckTradeUnique($tradeNo)
    {
        $this->db->select("*");
        $this->db->from($this->table);
        $this->db->where("trade_no", $tradeNo);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return false;
        } else {
            return true;
        }
    }

}
