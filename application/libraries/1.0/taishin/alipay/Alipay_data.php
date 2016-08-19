<?php

class Alipay_data extends BaseLibrary
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
            'BarCode'    => 'barcode',
            'OrderID'    => 'orderid',
            'Amount'     => 'amount',
            'OrderName'  => 'ordername',
            'OrderMemo'  => 'ordermemo',
        );
        $data = $this->dataIndexTrans($data, $fieldMapping);

        // 若沒有的欄位可給預設值的設定
        if (empty($data['timestamp'])) {
            $data['timestamp'] = date('YmdHis');
        }
        if (empty($data['gw'])) {
            $data['gw'] = 'ALIPAY_O';
        }

        return $data;
    }

    public function refund(array $data)
    {
        // 欄位對照轉換
        $fieldMapping = array(
            'OrderID'    => 'orderid',
            'Amount'     => 'amount',
        );
        $data = $this->dataIndexTrans($data, $fieldMapping);

        // 若沒有的欄位可給預設值的設定
        if (empty($data['timestamp'])) {
            $data['timestamp'] = date('YmdHis');
        }
        if (empty($data['gw'])) {
            $data['gw'] = 'ALIPAY_O';
        }
        if (empty($data['refundid'])) {
            $data['refundid'] = $data['orderid'];
        }
        if (empty($data['refundreason'])) {
            $data['refundreason'] = '測試';
        }

        return $data;
    }

    public function query(array $data)
    {
        // 欄位對照轉換
        $fieldMapping = array(
            'OrderID'    => 'id',
            // 'Type' => 'type'
        );
        $data = $this->dataIndexTrans($data, $fieldMapping);

        // 若沒有的欄位可給預設值的設定
        if (empty($data['timestamp'])) {
            $data['timestamp'] = date('YmdHis');
        }
        if (empty($data['gw'])) {
            $data['gw'] = 'ALIPAY_O';
        }
        // if ($data['type'] === '1') {
        //     $data['type'] = 'P';
        // } else if ($data['type'] === '2') {
        //     $data['type'] = 'R';
        // }

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
