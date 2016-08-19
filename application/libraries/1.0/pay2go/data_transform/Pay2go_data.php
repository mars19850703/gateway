<?php

class Pay2go_data extends BaseLibrary
{
    protected $config;

    public function __construct($config)
    {
        parent::__construct();

        $this->config = $config;
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

        return $data;
    }

    /**
     *  觸發發票資料欄位轉換
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

        return $data;
    }

    /**
     *  作廢發票資料欄位轉換
     *  @param  array  $data [description]
     *  @return [array]       [description]
     */
    public function invalid($data = array())
    {
        // 欄位對照轉換
        $fieldMapping = array(
            'MerchantID'    => 'MerchantID',
            'Key'           => 'HashKey',
            'Iv'            => 'HashIV',
            'InvoiceNumber' => 'InvoiceNumber',
            'InvalidReason' => 'InvalidReason',
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

        return $data;
    }

    /**
     *  作廢發票資料欄位轉換
     *  @param  array  $data [description]
     *  @return [array]       [description]
     */
    public function allowance($data = array())
    {
        // 欄位對照轉換
        $fieldMapping = array(
            'MerchantID'    => 'MerchantID',
            'Key'           => 'HashKey',
            'Iv'            => 'HashIV',
            'InvoiceNumber' => 'InvoiceNo',
            'OrderID'       => 'MerchantOrderNo',
            'ItemAmount'    => 'ItemAmt',
            'ItemTaxAmount' => 'ItemTaxAmt',
            'TotalAmount'   => 'TotalAmt',
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
            $data['Version'] = '1.2';
        }
        $data['ItemName']   = implode('|', $data['ItemName']);
        $data['ItemCount']  = implode('|', $data['ItemCount']);
        $data['ItemUnit']   = implode('|', $data['ItemUnit']);
        $data['ItemPrice']  = implode('|', $data['ItemPrice']);
        $data['ItemAmt']    = implode('|', $data['ItemAmt']);
        $data['ItemTaxAmt'] = implode('|', $data['ItemTaxAmt']);

        return $data;
    }

    /**
     *  作廢發票資料欄位轉換
     *  @param  array  $data [description]
     *  @return [array]       [description]
     */
    public function search($data = array())
    {
        // 欄位對照轉換
        $fieldMapping = array(
            'MerchantID'    => 'MerchantID',
            'Key'           => 'HashKey',
            'Iv'            => 'HashIV',
            'InvoiceNumber' => 'InvoiceNumber',
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
            $data['Version'] = '1.1';
        }
        if (empty($data['SearchType'])) {
            $data['SearchType'] = '0';
        }

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
