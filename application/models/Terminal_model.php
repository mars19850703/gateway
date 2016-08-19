<?php

class Terminal_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getTerminalByMerchantIdxAndTerminalCode($merchantIdx, $terminalCode)
    {
        $this->db->select('t.*, es.edc_set_name')
                 ->from($this->table . ' AS t')
                 ->join('Edc_set AS es', 't.edc_set_idx = es.idx', 'left')
                 ->where('t.merchant_idx', intval($merchantIdx))
                 ->where('t.terminal_code', $terminalCode)
                 ->where('t.terminal_status', 1);
        $query = $this->db->get();

        return $query->row_array();
    }

    public function getTerminalByMemberIdxMerchantIdxAndTerminalCode($memberIdx, $merchantIdx, $terminalCode)
    {
        $where = array(
            "member_idx"  => intval($memberIdx),
            "merchant_idx"  => intval($merchantIdx),
            "terminal_code" => $terminalCode,            
        );
        $this->db->select('t.*, es.edc_set_name')
                 ->from($this->table . ' AS t')
                 ->join('Edc_set AS es', 't.edc_set_idx = es.idx', 'left')
                 ->where('t.member_idx', intval($member_idx))
                 ->where('t.merchant_idx', intval($merchantIdx))
                 ->where('t.terminal_code', $terminalCode);
        $query = $this->db->get();

        return $query->row_array();
    }

    public function getTerminalByEdcIdx($edcIdx)
    {
        $this->db->select('t.*, es.edc_set_name')
                 ->from($this->table . ' AS t')
                 ->join('Edc_set AS es', 't.edc_set_idx = es.idx', 'left')
                 ->where('t.edc_idx', intval($edcIdx))
                 ->where('t.terminal_status', 1);
        $query = $this->db->get();

        return $query->row_array();
    }

    public function getTerminalByMerchantIdx($merchantIdx)
    {
        $this->db->select('t.*, es.edc_set_name')
                 ->from($this->table . ' AS t')
                 ->join('Edc_set AS es', 't.edc_set_idx = es.idx', 'left')
                 ->where('t.merchant_idx', intval($merchantIdx))
                 ->where('t.terminal_status', 1);
        $query = $this->db->get();

        return $query->result_array();
    }
}
