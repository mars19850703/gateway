<?php

class Linepay_valid extends BaseCheck
{
    public function __construct()
    {
        parent::__construct();
    }

    public function auth(array $postData)
    {
        $data = $postData['_Data'];

        // 檢查 OrderID 參數
        if (isset($data['OrderID']) && !empty($data['OrderID'])) {
            if (strlen($data['OrderID']) > 16 || strlen($data['OrderID']) === 0) {
                if (!$this->validOrderId($data['OrderID'])) {
                    return 'TRA_20003';
                }
            }
        } else {
            if (!empty($data['EDCID']) && !empty($data['EDCMac'])) {
                $data['OrderID'] = substr($postData['TerminalID'], 2) . time();
            } else {
                return 'TRA_20003';
            }
        }

        // 檢查幣別
        if (isset($data['Currency'])) {
            if (!$this->validCurrency($data['Currency'])) {
                return 'TRA_20007';
            }
        } else {
            return 'TRA_20007';
        }

        // 檢查金額
        if (isset($data['Amount'])) {
            if (!$this->validAmount($data['Amount'])) {
                return 'TRA_20002';
            }
        } else {
            return 'TRA_20002';
        }

        // 檢查是否有支付寶 BarCode 資訊
        if (!isset($data['BarCode']) || empty($data['BarCode'])) {
            return 'LINEPAY_10000';
        } elseif (strlen($data['BarCode']) > 12 || strlen($data['BarCode']) < 12) {
            return 'LINEPAY_10001';
        }

        $this->ci->load->model('merchant_model');
        $merchant = $this->ci->merchant_model->getMerchantAndCompanyByMerchantId($postData['MerchantID']);

        // 沒有帶入商品名稱，預設為商店名稱
        if (!isset($data['ProductName']) || empty($data['ProductName'])) {
            // return 'TRA_20011';
            $data['ProductName'] = $merchant['merchant_name'];
        }

        return $data;
    }

    public function confirm(array $data)
    {
        // 檢查 OrderID 參數
        if (isset($data['OrderID']) && !empty($data['OrderID'])) {
            if (strlen($data['OrderID']) > 16 || strlen($data['OrderID']) === 0) {
                if (!$this->validOrderId($data['OrderID'])) {
                    return 'TRA_20003';
                }
            }
        }

        return $data;
    }

    public function refund(array $data)
    {
        // 檢查 OrderID 參數
        if (isset($data['OrderID']) && !empty($data['OrderID'])) {
            if (strlen($data['OrderID']) > 16 || strlen($data['OrderID']) === 0) {
                if (!$this->validOrderId($data['OrderID'])) {
                    return 'TRA_20003';
                }
            }
        }

        return $data;
    }

    public function query(array $data)
    {
        // 檢查 OrderID 參數
        if (isset($data['OrderID']) && !empty($data['OrderID'])) {
            if (strlen($data['OrderID']) > 16 || strlen($data['OrderID']) === 0) {
                if (!$this->validOrderId($data['OrderID'])) {
                    return 'TRA_20003';
                }
            }
        }

        return $data;
    }
}
