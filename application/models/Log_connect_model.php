<?php

class Log_connect_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function insertConnect($class, $method, $arguments, array $data)
    {
        unset($data['KI']);

        $insertData = array(
            'class'       => $class,
            'method'      => $method,
            'arguments'   => json_encode($arguments),
            'origin_data' => json_encode($data),
            'ip'          => $this->input->ip_address(),
            'create_time' => date('Y-m-d H:i:s'),
        );

        return $this->insert($insertData);
    }

    public function updateConnect($idx, array $output)
    {
        $where = array(
            'idx' => intval($idx),
        );
        $updateData = array(
            'output_data' => json_encode($output),
            'modify_time' => date('Y-m-d H:i:s'),
        );

        $this->update($updateData, $where);
    }
}
