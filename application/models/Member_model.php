<?php

class Member_model extends MY_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	public function getMemberByMemberIdx($memberIdx)
	{
		$where = array(
			"idx" => intval($memberIdx)
		);

		return $this->select(false, "array", $where, 1);
	}
}
