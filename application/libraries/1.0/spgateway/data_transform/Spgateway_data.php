<?php

class Spgateway_data extends BaseLibrary
{
    protected $config;

    public function __construct($config)
    {
        parent::__construct();

        $this->config = $config;
        // $this->ci->config->load('param/pay2go', true);
        // $this->ci->config->load('param/gateway', true);
    }

    /**
     * 信用卡資料欄位轉換
     * @param  array  $data [description]
     * @return [array]       [description]
     */
    public function auth($data = array())
    {
        // 欄位對照轉換
        $fieldMapping = array(
            'MerchantID' => 'MerchantID',
            'Key'        => 'HashKey',
            'Iv'         => 'HashIV',
            'Version'    => 'Version',
            'OrderID'    => 'MerchantOrderNo',
            'Amount'     => 'Amt',
            'ProDesc'    => 'ProdDesc',
            'Inst'       => 'Inst',
            'CardNo'     => 'CardNo',
            'CardExpire' => 'Exp',
            'Cvv2'       => 'CVC',
        );

        $data = $this->dataIndexTrans($data, $fieldMapping);

        // 若沒有的欄位可給預設值的設定
        if (empty($data['TimeStamp'])) {
            $data['TimeStamp'] = time();
        }
        if (empty($data['Pos_'])) {
            $data['Pos_'] = 'JSON';
        }
        if (empty($data['Version'])) {
            $data['Version'] = '1.0';
        }
        if (empty($data['PayerEmail'])) {
            $data['PayerEmail'] = '@.';
        }
        if (empty($data['ProdDesc'])) {
            $data['ProdDesc'] = 'product';
        }
        if (!isset($data['Inst'])) {
            $data['Inst'] = '0';
        }

        // //檢查資料
        // if (!$this->ci->checkData->check($requireField, $data)) {
        //     return false;
        // }

        // MY_Controller::dumpData($data, $fieldMapping);

        return $data;
    }

    /**
     * 取消授權
     * @param  array  $data [description]
     * @return array       [description]
     */
    public function cancel($data = array())
    {
        // 必填欄位
        $requireField = array(
            'MerchantID'      => true,
            'HashKey'         => true,
            'HashIV'          => true,
            'RespondType'     => true,
            'TimeStamp'       => true,
            'Version'         => true,
            'MerchantOrderNo' => false,
            'TradeNo'         => false,
            'Amt'             => true,
            'IndexType'       => true,
        );
        // 欄位對照轉換
        $fieldMapping = array(
            'MerchantID' => 'MerchantID',
            'Key'        => 'HashKey',
            'Iv'         => 'HashIV',
            // 'Version' => 'Version',
            'OrderID'    => 'MerchantOrderNo',
            'Amount'     => 'Amt',
        );

        $data = $this->dataIndexTrans($data, $fieldMapping);

        // 若沒有的欄位可給預設值的設定
        if (empty($data['TimeStamp'])) {
            $data['TimeStamp'] = time();
        }
        if (empty($data['RespondType'])) {
            $data['RespondType'] = 'JSON';
        }
        if (empty($data['Version'])) {
            $data['Version'] = '1.0';
        }
        if (empty($data['IndexType'])) {
            $data['IndexType']               = 1; //1：商店訂單編號，2：智付寶交易序號
            $requireField['MerchantOrderNo'] = true;
        } else if ($data['IndexType'] == 2) {
            $requireField['TradeNo'] = true;
        } else if (!in_array($data['IndexType'], $this->config['index_type'])) {
            return false;
        }

        // notify的url設定
        $notify_url        = $this->config['notify_url'];
        $data['NotifyURL'] = $notify_url . __FUNCTION__;

        // //檢查資料
        // if (!$this->ci->checkData->check($requireField, $data)) {
        //     return false;
        // }

        return $data;
    }

    /**
     * 請款
     * @param  array  $data [description]
     * @return array       [description]
     */
    public function request($data = array())
    {
        // 必填欄位
        $requireField = array(
            'MerchantID'      => true,
            'HashKey'         => true,
            'HashIV'          => true,
            'RespondType'     => true,
            'TimeStamp'       => true,
            'Version'         => true,
            'MerchantOrderNo' => false,
            'TradeNo'         => false,
            'Amt'             => true,
            'IndexType'       => true,
            'CloseType'       => true,
        );
        // 欄位對照轉換
        $fieldMapping = array(
            'MerchantID' => 'MerchantID',
            'Key'        => 'HashKey',
            'Iv'         => 'HashIV',
            // 'Version' => 'Version',
            'OrderID'    => 'MerchantOrderNo',
            'Amount'     => 'Amt',
        );

        $data = $this->dataIndexTrans($data, $fieldMapping);

        // 若沒有的欄位可給預設值的設定
        if (empty($data['TimeStamp'])) {
            $data['TimeStamp'] = time();
        }
        if (empty($data['RespondType'])) {
            $data['RespondType'] = 'JSON';
        }
        if (empty($data['Version'])) {
            $data['Version'] = '1.0';
        }
        if (empty($data['IndexType'])) {
            $data['IndexType']               = 1; //1：商店訂單編號，2：智付寶交易序號
            $requireField['MerchantOrderNo'] = true;
        } else if ($data['IndexType'] == 2) {
            $requireField['TradeNo'] = true;
        } else if (!in_array($data['IndexType'], $this->config['index_type'])) {
            return false;
        }
        // 設定請款
        $data['CloseType'] = 1;

        // notify的url設定
        $notify_url        = $this->config['notify_url'];
        $data['NotifyURL'] = $notify_url . __FUNCTION__;

        // //檢查資料
        // if (!$this->ci->checkData->check($requireField, $data)) {
        //     return false;
        // }

        return $data;
    }

    public function refund($data = array())
    {
        // 必填欄位
        $requireField = array(
            'MerchantID'      => true,
            'HashKey'         => true,
            'HashIV'          => true,
            'RespondType'     => true,
            'TimeStamp'       => true,
            'Version'         => true,
            'MerchantOrderNo' => false,
            'TradeNo'         => false,
            'Amt'             => true,
            'IndexType'       => true,
            'CloseType'       => true,
        );
        // 欄位對照轉換
        $fieldMapping = array(
            'MerchantID' => 'MerchantID',
            'Key'        => 'HashKey',
            'Iv'         => 'HashIV',
            // 'Version' => 'Version',
            'OrderID'    => 'MerchantOrderNo',
            'Amount'     => 'Amt',
        );

        $data = $this->dataIndexTrans($data, $fieldMapping);

        // 若沒有的欄位可給預設值的設定
        if (empty($data['TimeStamp'])) {
            $data['TimeStamp'] = time();
        }
        if (empty($data['RespondType'])) {
            $data['RespondType'] = 'JSON';
        }
        if (empty($data['Version'])) {
            $data['Version'] = '1.0';
        }
        if (empty($data['IndexType'])) {
            $data['IndexType']               = 1; //1：商店訂單編號，2：智付寶交易序號
            $requireField['MerchantOrderNo'] = true;
        } else if ($data['IndexType'] == 2) {
            $requireField['TradeNo'] = true;
        } else if (!in_array($data['IndexType'], $this->config['index_type'])) {
            return false;
        }
        // 設定退款
        $data['CloseType'] = 2;

        // notify的url設定
        $notify_url        = $this->config['notify_url'];
        $data['NotifyURL'] = $notify_url . __FUNCTION__;

        // //檢查資料
        // if (!$this->ci->checkData->check($requireField, $data)) {
        //     return false;
        // }

        return $data;
    }

    public function queryToUpdate($data = array())
    {
        // 欄位對照轉換
        $fieldMapping = array(
            'MerchantID' => 'MerchantID',
            'OrderID'    => 'MerchantOrderNo',
            'Key'        => 'HashKey',
            'Iv'         => 'HashIV',
            'Amount'     => 'Amt',
        );

        $data = $this->dataIndexTrans($data, $fieldMapping);

        // 若沒有的欄位可給預設值的設定
        if (empty($data['TimeStamp'])) {
            $data['TimeStamp'] = time();
        }
        if (empty($data['Version'])) {
            $data['Version'] = '1.1';
        }
        
        return $data;
    }

    /**
     *  發票資料欄位轉換
     *  @param  array  $data [description]
     *  @return [array]       [description]
     */
    public function issue($data = array())
    {
        // 欄位對照轉換
        $fieldMapping = array(
            'MerchantID'  => 'MerchantID',
            'Key'         => 'HashKey',
            'Iv'          => 'HashIV',
            'Version'     => 'Version',
            'OrderID'     => 'MerchantOrderNo',
            'Amount'      => 'Amt',
            'TaxAmount'   => 'TaxAmt',
            'TotalAmount' => 'TotalAmt',
            'ItemAmount'  => 'ItemAmt',
        );

        $data = $this->dataIndexTrans($data, $fieldMapping);

        // 若沒有的欄位可給預設值的設定
        if (empty($data['TimeStamp'])) {
            $data['TimeStamp'] = time();
        }
        if (empty($data['RespondType'])) {
            $data['RespondType'] = 'JSON';
        }
        if (empty($data['Version'])) {
            $data['Version'] = '1.3';
        }
        $data['ItemName']  = implode('|', $data['ItemName']);
        $data['ItemCount'] = implode('|', $data['ItemCount']);
        $data['ItemUnit']  = implode('|', $data['ItemUnit']);
        $data['ItemPrice'] = implode('|', $data['ItemPrice']);
        $data['ItemAmt']   = implode('|', $data['ItemAmt']);

        // //檢查資料
        // if (!$this->ci->checkData->check($requireField, $data)) {
        //     return false;
        // }

        // MY_Controller::dumpData($data, $fieldMapping);

        return $data;
    }

    /**
     *  發票資料欄位轉換
     *  @param  array  $data [description]
     *  @return [array]       [description]
     */
    public function touch($data = array())
    {
        // 欄位對照轉換
        $fieldMapping = array(
            'MerchantID'     => 'MerchantID',
            'Key'            => 'HashKey',
            'Iv'             => 'HashIV',
            'Version'        => 'Version',
            'OrderID'        => 'MerchantOrderNo',
            'TotalAmt'       => 'TotalAmt',
            'InvoiceTransNo' => 'InvoiceTransNo',
        );

        $data = $this->dataIndexTrans($data, $fieldMapping);

        // 若沒有的欄位可給預設值的設定
        if (empty($data['TimeStamp'])) {
            $data['TimeStamp'] = time();
        }
        if (empty($data['RespondType'])) {
            $data['RespondType'] = 'JSON';
        }
        if (empty($data['Version'])) {
            $data['Version'] = '1.0';
        }

        // //檢查資料
        // if (!$this->ci->checkData->check($requireField, $data)) {
        //     return false;
        // }

        // MY_Controller::dumpData($data, $fieldMapping);

        return $data;
    }

    /**
     * 陣列index轉換
     * @param  array  $data [description]
     * @return [array]       [description]
     */
    protected function dataIndexTrans($data = array(), $fieldMapping = array())
    {
        if (!empty($data) && !empty($fieldMapping)) {
            // 資料轉換
            foreach ($fieldMapping as $apiField => $transField) {
                if (isset($data[$apiField]) && $data[$apiField]) {
                    $data[$transField] = $data[$apiField];
                    if ($transField != $apiField) {
                        unset($data[$apiField]);
                    }
                }
            }
        }

        return $data;
    }
}
