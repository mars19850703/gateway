<?php

class Edc_gateway_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getEdcGatewayVersion($edcModel, $edcVersion)
    {
        $where = array(
            'edc_model'   => $edcModel,
            'edc_version' => $edcVersion,
        );

        return $this->select(false, 'array', $where);
    }

    public function getGatewayVersion($edcModel, $appName, $appVersion, $gatewayVersion)
    {
        $where = array(
            'edc_model'       => $edcModel,
            'app_name'        => $appName,
            'app_version'     => $appVersion,
            'gateway_version' => $gatewayVersion,
        );

        return $this->select(false, 'array', $where);
    }
}
