<?php

class Edc_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getEdcByEdcIdAndEdcMac($edcId, $edcMac)
    {
        $this->db->select('e.*, d.device_name')
                 ->from($this->table . ' AS e')
                 ->join('Device AS d', 'e.device_idx = d.idx', 'left')
                 ->where('e.edc_id', $edcId)
                 ->where('e.edc_mac', $edcMac);
        $query = $this->db->get();

        return $query->row_array();  
    }

    public function getEdcByEdcIdx($edcIdx)
    {
        $this->db->select('e.*, d.device_name')
                 ->from($this->table . ' AS e')
                 ->join('Device AS d', 'e.device_idx = d.idx', 'left')
                 ->where('e.idx', intval($edcIdx));
        $query = $this->db->get();

        return $query->row_array(); 
    }
}
