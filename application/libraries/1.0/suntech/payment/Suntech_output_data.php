<?php

class Suntech_output_data extends BaseLibrary
{
    protected $config;
    protected $response;

    public function __construct()
    {
        parent::__construct();
        $this->resetResponse();
        $this->ci->load->library('check_data/credit_info');
    }

    protected function resetResponse()
    {
        $this->response = array(
            'Status'  => 'PAYMENT_00000',
            'Message' => '',
        );
    }

    /**
     *    金流商回傳結果，將之轉成統一輸出格式
     *
     *    @param $actionCode string 服務類型的功能代號
     *    @param $postData array 傳入的資料
     *    @param $transactionResult array 交易結果資料
     */
    public function authOutput($actionCode, array $postData, array $transactionResult)
    {
        // MY_Controller::dumpData($actionCode, $postData, $transactionResult);

        $this->resetResponse();
        $this->response['Status']         = $transactionResult['code'];
        $base                             = $this->bulidBaseOutputData($postData);
        $transactionResult['CardType']    = $this->ci->credit_info->getCardType($postData['_Data']['CardNo']);
        $base['PaymentDate']              = date('Y/m/d');
        $base['PaymentTime']              = date('H:i:s');
        if (isset($transactionResult['data']->RespCode)) {
            $this->response['GatewayStatus']  = $transactionResult['data']->RespCode;
            $this->response['GatewayMessage'] = $transactionResult['data']->RespCode_Str;
        } else {
            $this->response['GatewayStatus']  = 'FAIL';
            $this->response['GatewayMessage'] = $transactionResult['data']->ErrorMessage;
        }
        switch ($actionCode) {
            case 'credit':
                $param = $this->bulidCreditOutputData($postData, $transactionResult);
                break;
            default:
                break;
        }

        $this->response['Result'] = array_merge($base, $param);
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

    private function bulidCreditOutputData(array $postData, array $transactionResult)
    {
        $parameters = array(
            'Gateway'      => '',
            'GatewayName'  => '',
            'MerchantID'   => '',
            'TradeNo'      => '',
            'Auth'         => '',
            'AuthDate'     => '',
            'AuthTime'     => '',
            'CardType'     => '',
            'Card6No'      => '',
            'Card4No'      => '',
        );
        if ($transactionResult['code'] === 'PAYMENT_00000') {
            $parameters['Gateway']     = $transactionResult['gateway'];
            $parameters['GatewayName'] = $transactionResult['gatewayName'];
            $parameters['MerchantID']  = $transactionResult['data']->web;
            $parameters['TradeNo']     = $transactionResult['data']->BuySafeNo;
            $parameters['Auth']        = $transactionResult['data']->ApproveCode;
            $parameters['AuthDate']    = date('Y/m/d');
            $parameters['AuthTime']    = date('H:i:s');
            $parameters['CardType']    = $transactionResult['CardType'];
            $parameters['Card6No']     = substr($postData['_Data']['CardNo'], 0, 6);
            $parameters['Card4No']     = substr($postData['_Data']['CardNo'], -4);
        }

        return $parameters;
    }
}
