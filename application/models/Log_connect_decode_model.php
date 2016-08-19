<?php

class Log_connect_decode_model extends MY_Model
{
    protected $ignoreData;

    public function __construct()
    {
        parent::__construct();

        $this->ignoreData = array(
            'CardNo',
            'CardExpire',
            'Cvv2',
        );
    }

    public function insertConnectDecode($connectIdx, $class, $method, array $postData)
    {
        unset($postData['KI']);

        foreach ($postData['_Data'] as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    if (in_array($k, $this->ignoreData)) {
                        unset($postData['_Data'][$key][$k]);
                    }
                }
            } else {
                if (in_array($key, $this->ignoreData)) {
                    unset($postData['_Data'][$key]);
                }
            }
        }

        $insertData = array(
            'log_connect_idx' => intval($connectIdx),
            'class'           => $class,
            'method'          => $method,
            'origin_data'     => json_encode($postData),
            'ip'              => $this->input->ip_address(),
            'create_time'     => date('Y-m-d H:i:s'),
        );

        return $this->insert($insertData);
    }

    public function updateConnectDecode($connectIdx, array $output)
    {
        $where = array(
            'log_connect_idx' => intval($connectIdx),
        );
        $updateData = array(
            'output_data' => json_encode($output),
            'modify_time' => date('Y-m-d H:i:s'),
        );

        $this->update($updateData, $where);
    }
}
