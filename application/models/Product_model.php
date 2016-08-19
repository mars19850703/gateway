<?php

class Product_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getProductByIdx($idx)
    {
        $where = array(
            "idx"            => intval($idx),
            "product_status" => 1,
        );

        return $this->select(false, "array", $where);
    }

    public function getProductBySupplierIdxAndCode($supplierIdx, $code)
    {
        $where = array(
            'supplier_idx' => intval($supplierIdx),
            "product_code" => $code,
        );

        return $this->select(false, "array", $where);
    }
}
