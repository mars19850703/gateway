<?php

class Spgateway_output_data extends BaseLibrary
{
    protected $config;
    protected $response;

    public function __construct()
    {
        parent::__construct();
        $this->resetResponse();

        // load config
        include dirname(__FILE__) . "/../Config.php";
        $this->config = $spgateway;

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
        $this->resetResponse();
        $this->response['Status']         = $transactionResult['code'];
        $base                             = $this->bulidBaseOutputData($postData);
        $transactionResult['CardType']    = $this->ci->credit_info->getCardType($postData['_Data']['CardNo']);
        $base['PaymentDate']              = date('Y/m/d');
        $base['PaymentTime']              = date('H:i:s');
        $this->response['GatewayStatus']  = $transactionResult['data']->Status;
        $this->response['GatewayMessage'] = $transactionResult['data']->Message;
        switch ($actionCode) {
            case 'credit':
                $param = $this->bulidCreditOutputData($transactionResult);
                break;
            default:
                break;
        }

        $this->response['Result'] = array_merge($base, $param);
        return $this->response;
    }

    public function requestOutput(array $postData, array $authData, array $transactionResult)
    {
        // MY_Controller::dumpData($postData, $authData, $transactionResult);

        $this->resetResponse();
        $this->response['Status']         = $transactionResult['code'];
        $this->response['GatewayStatus']  = $transactionResult['data']->Status;
        $this->response['GatewayMessage'] = $transactionResult['data']->Message;

        if ($transactionResult['code']) {
            $this->response['Result']                 = $this->bulidBaseOutputData($postData);
            $this->response['Result']['MerchantID']   = strval($postData['_Data']['MerchantID']);
            $this->response['Result']['TradeNo']      = $transactionResult['data']->Result->TradeNo;
            $this->response['Result']['AuthBankCode'] = $authData['auth_bank'];
            $this->response['Result']['AuthCode']     = $authData['auth_code'];
            $this->response['Result']['Gateway']      = $authData['supplier']['supplier_code'];
            $this->response['Result']['GatewayName']  = $authData['supplier']['supplier_name'];
            $this->response['Result']['CardType']     = $this->ci->credit_info->getCardType($authData['card6no'], intval($authData['card_length']));
            $this->response['Result']['Card6No']      = $authData['card6no'];
            $this->response['Result']['Card4No']      = $authData['card4no'];
            $this->response['Result']['RequestDate']  = date('Y/m/d');
            $this->response['Result']['RequestTime']  = date('H:i:s');
        }

        if (isset($this->config['auth_bank'][$authData['auth_bank']])) {
            $this->response['Result']['AuthBank'] = $this->config['auth_bank'][$authData['auth_bank']];
        } else {
            $this->response['Result']['AuthBank'] = $authData['auth_bank'];
        }

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
                'OrderID'    => $query['order_id'],
                'Currency'   => $query['currency'],
                'Amount'     => $query['amount'],
                'Date'       => $query['auth_date'] . ' ' . $query['auth_time'],
                'StatusCode' => $this->getOrderStatusCode($query),
                'Status'     => $this->getOrderStatus($query),
            );
            if ($action['action_code'] === 'credit') {
                $temp['Card6No'] = $query['card6no'];
                $temp['Card4No'] = $query['card4no'];
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

    private function bulidCreditOutputData(array $transactionResult)
    {
        $parameters = array(
            'Gateway'      => '',
            'GatewayName'  => '',
            'MerchantID'   => '',
            'TradeNo'      => '',
            'Auth'         => '',
            'AuthBankCode' => '',
            'AuthBank'     => '',
            'AuthDate'     => '',
            'AuthTime'     => '',
            'CardType'     => '',
            'Card6No'      => '',
            'Card4No'      => '',
            'Inst'         => '',
            'InstFirst'    => '',
            'InstEach'     => '',
        );
        if ($transactionResult['code'] === 'PAYMENT_00000') {
            $date                       = $transactionResult['data']->Result->AuthDate;
            $time                       = str_split($transactionResult['data']->Result->AuthTime, 2);
            $parameters['Gateway']      = $transactionResult['gateway'];
            $parameters['GatewayName']  = $transactionResult['gatewayName'];
            $parameters['MerchantID']   = $transactionResult['data']->Result->MerchantID;
            $parameters['TradeNo']      = $transactionResult['data']->Result->TradeNo;
            $parameters['Auth']         = $transactionResult['data']->Result->Auth;
            $parameters['AuthBankCode'] = $transactionResult['data']->Result->AuthBank;
            if (isset($this->config['auth_bank'][$transactionResult['data']->Result->AuthBank])) {
                $parameters['AuthBank'] = $this->config['auth_bank'][$transactionResult['data']->Result->AuthBank];
            } else {
                $parameters['AuthBank'] = $transactionResult['data']->Result->AuthBank;
            }
            $parameters['AuthDate']  = substr($date, 0, 4) . '/' . substr($date, 4, 2) . '/' . substr($date, 6, 2);
            $parameters['AuthTime']  = implode(':', $time);
            $parameters['CardType']  = $transactionResult['CardType'];
            $parameters['Card6No']   = $transactionResult['data']->Result->Card6No;
            $parameters['Card4No']   = $transactionResult['data']->Result->Card4No;
            $parameters['Inst']      = strval($transactionResult['data']->Result->Inst);
            $parameters['InstFirst'] = strval($transactionResult['data']->Result->InstFirst);
            $parameters['InstEach']  = strval($transactionResult['data']->Result->InstEach);
        }

        return $parameters;
    }

    private function getOrderStatus($query)
    {
        if ($query['request_status'] === '1') {
            if (floatval($query['request_amount']) > floatval($query['refund_amount'])) {
                return '授權成功，已請款，可退款。';
            } else {
                return '授權成功，退款完成。';
            }
        } else if ($query['cancel_status'] === '1') {
            return '授權成功，取消交易完成。';
        } else if ($query['auth_status'] === '1') {
            return '授權成功，可取消，可請款';
        }
    }

    private function getOrderStatusCode($query)
    {
        if ($query['request_status'] === '1') {
            if (floatval($query['request_amount']) > floatval($query['refund_amount'])) {
                return '1';
            } else {
                return '2';
            }
        } else if ($query['cancel_status'] === '1') {
            return '3';
        } else if ($query['auth_status'] === '1') {
            return '4';
        }
    }
}
