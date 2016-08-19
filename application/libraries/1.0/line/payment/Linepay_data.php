<?php

class Linepay_data extends BaseLibrary
{
    protected $config;

    public function __construct()
    {
        parent::__construct();
    }

    public function auth(array $data)
    {
        // 欄位對照轉換
        $fieldMapping = array(
            'ProductName' => 'productName',
            'ProductImg'  => 'productImageUrl',
            'Amount'      => 'amount',
            'Currency'    => 'currency',
            'BarCode'     => 'oneTimeKey',
            'OrderID'     => 'orderId',
            'OrderName'   => 'ordername',
            'OrderMemo'   => 'ordermemo',
        );
        $data = $this->dataIndexTrans($data, $fieldMapping);

        // 若沒有的欄位可給預設值的設定
        // 預設自動請款
        if (empty($data['capture'])) {
            $data['capture'] = true;
        }
        if ($data['currency'] === 'NTD') {
            $data['currency'] = 'TWD';
        }

        return $data;
    }

    public function refund(array $data)
    {
        $fieldMapping = array(
            'Amount' => 'refundAmount',
        );

        $data = $this->dataIndexTrans($data, $fieldMapping);

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
