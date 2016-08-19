<?php

class Edc_update_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getLatestEdcSetVersion($edcIdx, $deviceIdx)
    {
        $where = array(
            'edc_idx'       => intval($edcIdx),
            'update_type'   => 1,
            'update_status' => 0,
            'update_del'    => 0,
            'device_idx'    => intval($deviceIdx),
            'batch_time < ' => time(),
        );

        return $this->select(false, 'array', $where, array('idx' => 'desc'));
    }

    public function getLatestConfig($edcIdx, $deviceIdx)
    {
        $where = array(
            'edc_idx'       => intval($edcIdx),
            'update_type'   => 2,
            'update_status' => 0,
            'update_del'    => 0,
            'device_idx'    => intval($deviceIdx),
            'batch_time < ' => time(),
        );

        return $this->select(false, 'array', $where, array('idx' => 'desc'));
    }

    public function getConfigByIdx($configIdx, $edcIdx, $deviceIdx)
    {
        $where = array(
            'idx'           => intval($configIdx),
            'device_idx'    => intval($deviceIdx),
            'edc_idx'       => intval($edcIdx),
            'update_type'   => 2,
            'update_status' => 0,
            'update_del'    => 0,
            // 'edc_id'        => $edcId,
            // 'edc_mac'       => $edcMac,
        );

        return $this->select(false, 'array', $where);
    }

    public function updateEdcUpdateStatus($updateIdx, $edcIdx, $deviceIdx)
    {
        $where = array(
            'idx <='        => intval($updateIdx),
            'edc_idx'       => intval($edcIdx),
            'update_type'   => 1,
            'update_del'    => 0,
            'update_status' => 0,
            'device_idx'    => intval($deviceIdx),
        );

        $updateData = array(
            'update_status' => 1,
            'report_time'   => date('Y-m-d H:i:s'),
        );

        return $this->update($updateData, $where);
    }

    public function updateEdcConfigUpdateStatus($updateIdx, $edcIdx, $deviceIdx)
    {
        $where = array(
            'idx <='        => intval($updateIdx),
            'edc_idx'       => intval($edcIdx),
            'update_type'   => 2,
            'update_status' => 0,
            'update_del'    => 0,
            'device_idx'    => intval($deviceIdx),
        );

        $updateData = array(
            'update_status' => 1,
            'report_time'   => date('Y-m-d H:i:s'),
        );

        return $this->update($updateData, $where);
    }

    public function insertEdcConfigUpdate($operatorIdx, array $terminal)
    {
        $this->db->select('*')
            ->from('Edc')
            ->where('idx', intval($terminal['edc_idx']));
        $query = $this->db->get();
        $edc   = $query->row_array();

        $this->db->flush_cache();
        $insertData = array(
            'edc_idx'            => $edc['idx'],
            'edc_set_idx'        => intval($terminal['edc_set_idx']),
            'terminal_idx'       => intval($terminal['idx']),
            'update_type'        => 2,
            'update_priorty'     => 1,
            'device_idx'         => intval($edc['device_idx']),
            'batch_time'         => time(),
            'create_time'        => date('Y-m-d H:i:s'),
            'create_manager_idx' => intval($operatorIdx),
        );

        return $this->insert($insertData);
    }
}
