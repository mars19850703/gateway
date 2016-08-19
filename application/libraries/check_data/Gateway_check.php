<?php

class Gateway_check extends BaseCheck
{
	public function __construct()
	{
		parent::__construct();
        // load payment config
        $this->ci->config->load('param/gateway', true);
	}

	/**
	 *	檢查 ResType 參數是否正確
	 */
	public function response($type)
	{
        $responseType = $this->ci->config->item('response_type', 'param/gateway');
        if (!in_array(strtolower($type), $responseType)) {
            return false;
        }

        return true;
	}

	/**
	 *	檢查 MerchantID 是否為空
	 */
	public function merchant($merchantId)
	{
		if (is_null($merchantId) || empty($merchantId)) {
			return false;
		}

		return true;
	}

	/**
	 *	檢查端末代碼是否為空
	 */
	public function terminal($terminalId)
	{
		if (is_null($terminalId) || empty($terminalId)) {
			return false;
		}

		return true;
	}

	/**
	 *	檢查端末代碼格式
	 */
	public function terminalFormat($terminalId)
	{
		if (strlen($terminalId) !== 4 || !is_numeric($terminalId)) {
			return false;
		}

		return true;
	}

	/**
	 *	檢查 gateway 版本是否為空
	 */
	public function gateway($gateway)
	{
		if (is_null($gateway) || empty($gateway)) {
			return false;
		}

		return true;
	}

	/**
	 *	檢查 _Data 是否為空
	 */
	public function data($data)
	{
		if (is_null($data) || empty($data)) {
			return false;
		}

		return true;
	}
}
