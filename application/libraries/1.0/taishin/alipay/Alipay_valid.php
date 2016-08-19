<?php

class Alipay_valid extends BaseCheck
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
            $data['Currency'] = 'NTD';
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
            return 'ALIPAY_10000';
        }

        $this->ci->load->model('merchant_model');
        $merchant = $this->ci->merchant_model->getMerchantAndCompanyByMerchantId($postData['MerchantID']);

        // 沒有帶入商品名稱，預設為商店名稱
        if (!isset($data['OrderName']) || empty($data['OrderName'])) {
            // return 'TRA_20011';
            $data['OrderName'] = $merchant['merchant_name'];
        }

        // 沒有帶入商品描述，預設為公司名稱
        if (!isset($data['OrderMemo']) || empty($data['OrderMemo'])) {
            if ($merchant['member_type'] === '1') {
                $data['OrderMemo'] = mb_substr($merchant['member_name'], 0, 1);
                $length            = mb_strlen(mb_substr($merchant['member_name'], 1, -1));
                for ($i = 0; $i < $length; $i++) {
                    $data['OrderMemo'] .= 'O';
                }
                $data['OrderMemo'] .= mb_substr($merchant['member_name'], -1);
            } else if ($merchant['member_type'] === '2') {
                $data['OrderMemo'] = $merchant['member_company_name'];
            }
        }

        return $data;
    }

    public function refund(array $postData)
    {
        $data = $postData['_Data'];

        if (isset($data['OrderID'])) {
            if (!$this->validOrderId($data['OrderID'])) {
                return 'TRA_20003';
            }
        } else {
            return 'TRA_20003';
        }

        if (isset($data['Currency'])) {
            if (!$this->validCurrency($data['Currency'])) {
                return 'TRA_20007';
            }
        } else {
            $data['Currency'] = 'NTD';
        }

        // // 檢查金額
        // if (isset($data['Amount'])) {
        //     if (!$this->validAmount($data['Amount'])) {
        //         return 'TRA_20002';
        //     }
        // } else {
        //     return 'TRA_20002';
        // }

        return $data;
    }

    public function query(array $postData)
    {
        $data = $postData['_Data'];

        // if (isset($data['Type'])) {
        //     if (is_numeric($data['Type'])) {
        //         if ($data['Type'] === '1' || $data['Type'] === '2') {

        //         } else {
        //             return 'TRA_20014';
        //         }
        //     } else {
        //         return 'TRA_20014';
        //     }
        // } else {
        //     return 'TRA_20013';
        // }

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
