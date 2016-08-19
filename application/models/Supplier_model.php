<?php

class Supplier_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getSupplierByIdx($idx)
    {
        $where = array(
            "idx"             => intval($idx),
            "supplier_status" => 1,
        );

        return $this->select(false, "array", $where);
    }

    public function getSupplierByCode($code)
    {
        $where = array(
            "supplier_code" => $code,
        );

        return $this->select(false, "array", $where);
    }
}
