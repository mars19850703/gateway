<?php

class Terminal_service_mapping_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getServiceMappingByTerminalIdxAndServiceIdx($terminalIdx, $supplierIdx, $productIdx, $actionIdx, $optionIdx)
    {
        $where = array(
            'terminal_idx' => intval($terminalIdx),
            'supplier_idx' => intval($supplierIdx),
            'product_idx' => intval($productIdx),
            'action_idx' => intval($actionIdx),
            'option_idx' => intval($optionIdx),
        );

        return $this->select(false, 'array', $where);
    }

    public function getServiceMappingByTerminalIdx($terminalIdx, $productCode = null)
    {
        $where = array(
            'terminal_idx' => intval($terminalIdx),
            'service_status' => 2,
        );

        if ($productCode) {
            $where['product_code'] = $productCode;
        }

        $this->db->select(array(
            'tsm.*',
            // 's.supplier_name',
            's.supplier_code',
            // 'p.product_name',
            'p.product_code',
            'p.edc_category',
            // 'a.action_name',
            'a.action_code',
            // 'o.option_name',
            'o.option_code',
            'o.option_group',
        ));
        $this->db->from($this->table .' AS tsm');
        $this->db->join('Supplier AS s', 'tsm.supplier_idx = s.idx', 'left');
        $this->db->join('Product AS p', 'tsm.product_idx = p.idx', 'left');
        $this->db->join('Action AS a', 'tsm.action_idx = a.idx', 'left');
        $this->db->join('Option AS o', 'tsm.option_idx = o.idx', 'left');
        $this->db->where($where);

        $query = $this->db->get();

        return $query->result_array();
    }

    public function getPay2goCreditServiceByTerminalIdx($terminalIdx)
    {
        $query = $this->db->select('*')
                          ->from($this->table . ' AS tsm')
                          ->join('Option AS o', 'tsm.option_idx = o.idx', 'left')
                          ->where('tsm.supplier_idx', 1)
                          ->where('tsm.product_idx', 1)
                          ->where('tsm.action_idx', 1)
                          ->where('tsm.terminal_idx', intval($terminalIdx))
                          ->get();

        return $query->result_array();
    }
}
