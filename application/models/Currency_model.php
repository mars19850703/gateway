<?php

class Currency_model extends MY_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	public function getCurrencyByName($currencyName)
	{
		$where = array(
			"currency_name" => $currencyName
		);

		return $this->select(false, "array", $where);
	}
}
