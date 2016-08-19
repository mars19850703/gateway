<?php

class Access_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function CheckAccessNoUnique($no)
    {
        $where = array(
            "access_no" => $no,
        );

        $result = $this->select(false, "array", $where, 1);

        if (is_null($result)) {
            return true;
        } else {
            return false;
        }
    }

    public function insertAccess(array $output)
    {
        $insertData = array(
            'access_no'   => $output['AccessNo'],
            'result'      => json_encode(array(
                'Auth'    => $output['Auth'],
                'Request' => $output['Request'],
                'Cancel'  => $output['Cancel'],
                'Refund'  => $output['Refund'],
            )),
            'create_time' => $output['Date'],
        );

        $this->insert($insertData);
    }
}
