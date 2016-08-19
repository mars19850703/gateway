<?php

class Merchant_model extends MY_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	public function getMerchantByMerchantId($merchantId)
	{
		$where = array(
			"merchant_id" => $merchantId
		);

		return $this->select(false, "array", $where, 1);
	}

	public function getMerchantByMerchantIdx($merchantIdx)
	{
		$where = array(
			'idx' => intval($merchantIdx)
		);

		return $this->select(false, 'array', $where, 1);
	}

	public function getMerchantAndCompanyByMerchantId($merchantId)
	{
		$sql = $this->db->select('*')
				 		->from($this->table . ' AS mer')
				 		->join('Member AS mem', 'mer.member_idx = mem.idx', 'left')
				 		->where('mer.merchant_id', $merchantId)
				 		->get();
 		return $sql->row_array();
	}
}
