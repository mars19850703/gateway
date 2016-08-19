<?php

class Alipay_output extends BaseLibrary
{
    protected $config;
    protected $response;

    public function __construct()
    {
        parent::__construct();
        $this->resetResponse();
    }

    protected function resetResponse()
    {
        $this->response = array(
            'Status'  => 'ALIPAY_00000',
            'Message' => '',
        );
    }

    /**
     *    金流商回傳結果，將之轉成統一輸出格式
     *
     *    @param $postData array 傳入的資料
     *    @param $transactionResult array 交易結果資料
     */
    public function authOutput(array $postData, array $transactionResult)
    {
        $this->resetResponse();
        $this->response['Status']         = $transactionResult['code'];
        $this->response['GatewayStatus']  = $transactionResult['data']->return_code;
        $this->response['GatewayMessage'] = is_object($transactionResult['data']->return_message) ? '' : $transactionResult['data']->return_message;

        $this->response['Result']                = $this->bulidBaseOutputData($postData);
        $this->response['Result']['MerchantID']  = $transactionResult['data']->merchantid;
        $this->response['Result']['PaymentDate'] = date('Y/m/d');
        $this->response['Result']['PaymentTime'] = date('H:i:s');
        $this->response['Result']['Gateway']     = $transactionResult['gateway'];
        $this->response['Result']['GatewayName'] = $transactionResult['gatewayName'];
        return $this->response;
    }

    public function refundOutput(array $postData, array $authData, array $transactionResult)
    {
        $this->resetResponse();
        $this->response['Status']         = $transactionResult['code'];
        $this->response['GatewayStatus']  = $transactionResult['data']->return_code;
        $this->response['GatewayMessage'] = is_object($transactionResult['data']->return_message) ? '' : $transactionResult['data']->return_message;

        $this->response['Result']                = $this->bulidBaseOutputData($postData);
        $this->response['Result']['MerchantID']  = $transactionResult['data']->merchantid;
        $this->response['Result']['PaymentDate'] = date('Y/m/d');
        $this->response['Result']['PaymentTime'] = date('H:i:s');
        $this->response['Result']['Gateway']     = $transactionResult['gateway'];
        $this->response['Result']['GatewayName'] = $transactionResult['gatewayName'];
        return $this->response;
    }

    public function queryOutput(array $action, array $outputData)
    {
        $this->resetResponse();
        $this->response['Status'] = 'PAYMENT_QUERY_00000';

        // MY_Controller::dumpData($this->response);

        $output = array();
        foreach ($outputData as &$query) {
            $temp = array(
                'OrderID'  => $query['order_id'],
                'Currency' => $query['currency'],
                'Date'     => $query['auth_date'] . ' ' . $query['auth_time'],
            );
            if ($query['refund_status'] === '1') {
                $temp['OrderStatus'] = '退款成功';
                $temp['Amount'] = '-' . $query['amount'];
            } elseif ($query['request_status'] === '1' && $query['auth_status'] === '1') {
                $temp['OrderStatus'] = '付款成功';
                $temp['Amount'] = $query['amount'];
            }

            $output[] = $temp;
        }
        $this->response['Result'] = $output;

        return $this->response;
    }

    private function bulidBaseOutputData(array $postData)
    {
        $base = array(
            'ServiceCode'     => $postData['_Data']['ServiceCode'],
            'ProcessTerminal' => $postData['MerchantID'] . str_pad($postData['TerminalID'], 4, '0', STR_PAD_LEFT),
            'OrderID'         => strval($postData['_Data']['OrderID']),
            'ProcessNo'       => $postData['transactionNo'],
            'Currency'        => $postData['_Data']['Currency'],
            'Amount'          => strval($postData['_Data']['Amount']),
        );

        return $base;
    }
}
