<?php

class Invoice_valid extends BaseCheck
{
    public function __construct()
    {
        parent::__construct();
    }

    public function issue(array $data)
    {
    	// 訂單編號
    	if (isset($data['OrderID'])) {
            if (!$this->validOrderId($data['OrderID'])) {
                return 'TRA_20003';
            }
        } else {
            return 'TRA_20003';
        }
        // 開立發票方式
        if (!isset($data['Status']) || !is_numeric($data['Status'])) {
        	return 'INVOICE_60005';
        }
        // 買受人姓名
        if (!isset($data['BuyerName']) || empty($data['BuyerName'])) {
        	return 'INVOICE_60000';
        } else if (strlen($data['BuyerName']) > 50) {
        	return 'INVOICE_60001';
        }
        // 買受人統編
        if (!isset($data['BuyerUBN']) || empty($data['BuyerUBN'])) {
        	$data['Category'] = 'B2C';
            $data['BuyerUBN'] = '';
        } else if (strlen($data['BuyerUBN']) === 8 && is_numeric($data['BuyerUBN'])) {
        	$data['Category'] = 'B2B';
        } else {
        	return 'INVOICE_60002';
        }
        // 買受人電子信箱
        if (!isset($data['BuyerEmail']) || empty($data['BuyerEmail'])) {
        	return 'INVOICE_60003';
        } else if (strlen($data['BuyerEmail']) > 50 || !$this->validEmail($data['BuyerEmail'])) {
        	return 'INVOICE_60004';
        }
        // 索取紙本發票
        if (!isset($data['PrintFlag']) || empty($data['PrintFlag'])) {
        	return 'INVOICE_60006';
        } else if (in_array($data['PrintFlag'], array('Y', 'N'))) {
        	if ($data['Category'] === 'B2B') {
        		$data['PrintFlag'] = 'Y';
        	}
        } else {
        	return 'INVOICE_60007';
        }
        // 稅別
        if (!isset($data['TaxType']) || empty($data['TaxType'])) {
        	return 'INVOICE_60008';
        } else if (!in_array($data['TaxType'], array('1', '2', '3', '9'))) {
        	return 'INVOICE_60009';
        }
        // 稅率
        if (!isset($data['TaxRate']) || empty($data['TaxRate'])) {
        	return 'INVOICE_60010';
        } else if (is_numeric($data['TaxRate'])) {
        	if ($data['TaxType'] === '2' || $data['TaxType'] === '3') {
        		if ($data['TaxRate'] !== '0') {
        			return 'INVOICE_60011';
        		}
        	}
        } else {
			return 'INVOICE_60011';
        }
        // 幣別
        if (isset($data['Currency'])) {
            if (!$this->validCurrency($data['Currency'])) {
                return 'TRA_20007';
            }
        } else {
            return 'TRA_20007';
        }
        // 金額
        if (isset($data['Amount'])) {
            if (!$this->validAmount($data['Amount'])) {
                return 'TRA_20002';
            }
        } else {
            return 'TRA_20002';
        }
        // 稅額
        if (isset($data['TaxAmount'])) {
            if (!$this->validAmount($data['TaxAmount'])) {
                return 'INVOICE_60012';
            }
        } else {
            return 'INVOICE_60012';
        }
        // 發票金額
        if (isset($data['TotalAmount'])) {
            if (!$this->validAmount($data['TotalAmount'])) {
                return 'INVOICE_60013';
            } else {
            	if (intval($data['TotalAmount']) !== (intval($data['Amount']) + intval($data['TaxAmount']))) {
            		return 'INVOICE_60013';
            	}
            }
        } else {
            return 'INVOICE_60013';
        }

        $itemCount = count($data['ItemName']);
        // 商品名稱
        if (!isset($data['ItemName']) || !is_array($data['ItemName']) || $itemCount === 0) {
        	return 'INVOICE_60014';
        } else {
        	foreach ($data['ItemName'] as $item) {
        		if (mb_strlen($item) > 30) {
        			return 'INVOICE_60015';
        		}
        	}
        }
        // 商品數量
        if (!isset($data['ItemCount']) || !is_array($data['ItemCount']) || count($data['ItemCount']) === 0) {
        	return 'INVOICE_60016';
        } else {
        	if (count($data['ItemCount']) === $itemCount) {
	        	foreach ($data['ItemCount'] as $count) {
	        		if (!is_numeric($count) && intval($count) > 0) {
	        			return 'INVOICE_60017';
	        		}
	        	}
	        } else {
	        	return 'INVOICE_60017';
	        }
        }
        // 商品單位
        if (!isset($data['ItemUnit']) || !is_array($data['ItemUnit']) || count($data['ItemUnit']) === 0) {
        	return 'INVOICE_60018';
        } else {
        	if (count($data['ItemUnit']) === $itemCount) {
	        	foreach ($data['ItemUnit'] as $unit) {
	        		if (mb_strlen($unit) > 2) {
	        			return 'INVOICE_60019';
	        		}
	        	}
	        } else {
	        	return 'INVOICE_60019';
	        }
        }
        // 商品單價
        if (!isset($data['ItemPrice']) || !is_array($data['ItemPrice']) || count($data['ItemPrice']) === 0) {
        	return 'INVOICE_60020';
        } else {
        	if (count($data['ItemPrice']) === $itemCount) {
	        	foreach ($data['ItemPrice'] as $price) {
	        		if (!is_numeric($price)) {
	        			return 'INVOICE_60021';
	        		}
	        	}
	        } else {
	        	return 'INVOICE_60021';
	        }
        }
        // 商品小計
        if (!isset($data['ItemAmount']) || !is_array($data['ItemAmount']) || count($data['ItemAmount']) === 0) {
        	return 'INVOICE_60022';
        } else {
        	if (count($data['ItemAmount']) === $itemCount) {
	        	foreach ($data['ItemAmount'] as $key => $amount) {
	        		if (!is_numeric($amount)) {
	        			return 'INVOICE_60022';
	        		} else {
	        			$subAmount = intval($data['ItemCount'][$key]) * intval($data['ItemPrice'][$key]);
	        			if (intval($amount) !== $subAmount) {
	        				return 'INVOICE_60022';
	        			}
	        		}
	        	}
	        } else {
	        	return 'INVOICE_60022';
	        }
        }

        // 備註
        if (!isset($data['Comment']) || empty($data['Comment'])) {
        	$data['Comment'] = '';
        }

        return $data;
    }

    public function touch(array $data)
    {
        // 訂單編號
        if (isset($data['OrderID'])) {
            if (!$this->validOrderId($data['OrderID'])) {
                return 'TRA_20003';
            }
        } else {
            return 'TRA_20003';
        }

        return $data;
    }
}
