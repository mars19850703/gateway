<?php

class Edc_set_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getEdcAppByAppName($edcIdx, $appName)
    {
        $this->db->select('a.*')
                 ->from($this->table . ' AS es')
                 ->join('Edc_app_mapping AS eam', 'es.idx = eam.edc_set_idx', 'left')
                 ->join('App AS a', 'eam.app_idx = a.idx', 'left')
                 ->where('es.idx', intval($edcIdx))
                 ->where('a.app_name', $appName);
        $query = $this->db->get();

        return $query->row_array();
    }

    public function getEdcSetBySetIdx($setIdx)
    {
        $where = array(
            'idx' => intval($setIdx)
        );

        return $this->select(false, 'array', $where);
    }

    public function getEdcSetAppByEdcSetIdx($edcSetIdx)
    {
        $this->db->select('a.*, as.supplier_name, as.supplier_en_name')
                 ->from($this->table . ' AS es')
                 ->join('Edc_app_mapping AS eam', 'es.idx = eam.edc_set_idx', 'left')
                 ->join('App AS a', 'eam.app_idx = a.idx', 'left')
                 ->join('App_supplier AS as', 'a.app_supplier_idx = as.idx', 'left')
                 ->where('es.idx', intval($edcSetIdx));
        $query = $this->db->get();

        return $query->result_array();
    }
}
