<?php

class Option_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getOptionByIdx($idx)
    {
        $where = array(
            "idx"           => intval($idx),
            'option_status' => 1,
        );

        return $this->select(false, "array", $where);
    }
}
