<?php

class Cash_config_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getCashConfigByTerminalIdxAndServiceIdx($terminalIdx, $supplierIdx, $productIdx = null)
    {
        $where = array(
            "terminal_idx" => intval($terminalIdx),
            "supplier_idx" => intval($supplierIdx),
            // "product_idx"  => intval($productIdx),
        );

        return $this->select(false, "array", $where);
    }

    public function getCashConfigByTerminalIdx($terminalIdx)
    {
        $where = array(
            "terminal_idx" => intval($terminalIdx),
        );

        return $this->select(false, "array", $where);
    }
}
