<?php

class Log_payment_gateway_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function insertPaymentGatewayLog($data)
    {
        return $this->insert($data);
    }

    public function updatePaymentGatewayLog($paymentLogIdx, array $outputData)
    {
    	$where = array(
    		"idx" => $paymentLogIdx
		);

		$updateData = array(
            "modify_time" => date("Y-m-d H:i:s"),
			"output_data" => json_encode($outputData)
		);

		return $this->update($updateData, $where);
    }
}
