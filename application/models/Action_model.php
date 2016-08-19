<?php

class Action_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getActionByIdx($idx)
    {
        $where = array(
            "idx"           => intval($idx),
            'action_status' => 1,
        );

        return $this->select(false, "array", $where);
    }

    public function getActionBySupplierIdxAndProductIdxAndCode($supplierIdx, $productIdx, $code)
    {
        $where = array(
            'supplier_idx' => intval($supplierIdx),
            'product_idx'  => intval($productIdx),
            "action_code"  => $code,
        );

        return $this->select(false, "array", $where);
    }
}
