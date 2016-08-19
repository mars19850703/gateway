<?php

class Edc_app_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getEdcAppVersion($edcIdx, $appName)
    {
        $where = array(
            'edc_idx'  => intval($edcIdx),
            'app_name' => $appName,
        );

        return $this->select(false, 'array', $where);
    }

    public function getEdcApp($edcIdx)
    {
        $where = array(
            'edc_idx' => intval($edcIdx)
        );

        return $this->select(true, 'array', $where);
    }
}
