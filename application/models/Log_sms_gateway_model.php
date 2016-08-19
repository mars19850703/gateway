<?php

class Log_sms_gateway_model extends MY_Model
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
    public function insertLogSmsGateway(array $data)
    {
        // 記錄log
        $insertLogData = array(
            'store_id'    => $data['StoreID'],
            'service_config_id'  => (isset($data['ServiceID'])) ? $data['ServiceID'] : 0,
            'input_data'  => json_encode($data),
            'ip'          => $this->input->ip_address(),
            // 'user_id'     => 0,
            'create_time' => date("Y-m-d H:i:s"),
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

}
