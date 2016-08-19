<?php

class Profile extends BaseLibrary
{
    protected $data;
    protected $config;

    public function __construct($data)
    {
        parent::__construct();
        $this->data             = $data;
        $this->config           = array();
        $this->config['Server'] = array();
    }

    public function getConfigByEdc()
    {
        $this->config['EDC']                  = $this->getEdcAppSetting();
        $this->config['Server']['Merchant']   = $this->getMerchantSetting();
        $this->config['Server']['EDCSetting'] = $this->getEdcClientSetting();
        $temp                                 = $this->getEdcService();
        $this->config['Server']               = array_merge($this->config['Server'], $temp);

        // MY_Controller::dumpData($this->data, $this->config);
        
        return $this->config;
    }

    protected function getEdcAppSetting()
    {
        $this->ci->load->model('edc_set_model');
        $appSetting = array();
        $edcApp     = $this->ci->edc_set_model->getEdcSetAppByEdcSetIdx($this->data['terminal']['edc_set_idx']);
        foreach ($edcApp as $app) {
            $appSetting[$app['app_name']] = json_decode($app['edc_config'], true);
        }

        return $appSetting;
    }

    protected function getMerchantSetting()
    {
        $this->ci->load->model('merchant_model');
        $merchant       = $this->ci->merchant_model->getMerchantByMerchantIdx($this->data['terminal']['merchant_idx']);
        $serverMerchant = array(
            'MerchantID'  => $merchant['merchant_id'],
            'TerminalID'  => $this->data['terminal']['terminal_code'],
            'Title'       => $merchant['merchant_name'],
            'EDC_SetName' => $this->data['terminal']['edc_set_name'],
            'EDCID'       => $this->data['edc']['edc_id'],
            'EDCMac'      => $this->data['edc']['edc_mac'],
            'EDCModel'    => $this->data['edc']['device_name'],
        );

        // get member
        $this->ci->load->model('member_model');
        $member = $this->ci->member_model->getMemberByMemberIdx($merchant['member_idx']);
        if ($member['member_type'] === '1') {
            $serverMerchant['Company'] = mb_substr($member['member_name'], 0, 1);
            $length                    = mb_strlen($member['member_name']) - 2;
            for ($i = 0; $i < $length; $i++) {
                $serverMerchant['Company'] .= 'O';
            }
            $serverMerchant['Company'] .= mb_substr($member['member_name'], -1);
        } elseif ($member['member_type'] === '2') {
            $serverMerchant['Company'] = $member['member_company_name'];
        }

        return $serverMerchant;
    }

    protected function getEdcClientSetting()
    {
        $clientSetting = json_decode($this->data['terminal']['edc_client_config'], true);

        return $clientSetting;
    }

    protected function getEdcService()
    {
        //取得設定檔取4塊設定檔，1：信用卡，2：代銷代售&票券，3：會員扣款，4：設定，機器設定?
        $this->ci->load->model('terminal_service_mapping_model');
        $edcService = $this->ci->terminal_service_mapping_model->getServiceMappingByTerminalIdx($this->data['terminal']['idx']);

        $config = array();
        $temp   = array();
        foreach ($edcService as $service) {
            $key = 'Key_' . $service['edc_category'];
            if (!isset($temp[$key])) {
                $temp[$key] = array();
            }
            if (!isset($temp[$key][$service['supplier_code']])) {
                $temp[$key][$service['supplier_code']] = array(
                    'production' => array(),
                );
            }
            if (!isset($temp[$key][$service['supplier_code']]['production'][$service['product_code']])) {
                $temp[$key][$service['supplier_code']]['production'][$service['product_code']] = array(
                    'action' => array(),
                );
            }
            if (!isset($temp[$key][$service['supplier_code']]['production'][$service['product_code']]['action'][$service['action_code']])) {
                $temp[$key][$service['supplier_code']]['production'][$service['product_code']]['action'][$service['action_code']] = array(
                    'option' => array(),
                );
            }
            if (!isset($temp[$key][$service['supplier_code']]['production'][$service['product_code']]['action'][$service['action_code']]['option'][$service['option_group']])) {
                $temp[$key][$service['supplier_code']]['production'][$service['product_code']]['action'][$service['action_code']]['option'][$service['option_group']] = array(
                    'Method' => array(),
                );
            }
            $temp[$key][$service['supplier_code']]['production'][$service['product_code']]['action'][$service['action_code']]['option'][$service['option_group']]['Method'][] = array(
                'name' => $service['option_code'],
                'code' => str_pad($service['supplier_idx'], 3, '0', STR_PAD_LEFT) . str_pad($service['product_idx'], 3, '0', STR_PAD_LEFT) . str_pad($service['action_idx'], 3, '0', STR_PAD_LEFT) . str_pad($service['option_idx'], 3, '0', STR_PAD_LEFT),
            );
        }

        foreach ($temp as $key => $supplier) {
            $config[$key] = array();
            $i            = 0;
            foreach ($supplier as $supplierCode => $production) {
                $config[$key]['Service']['supplier'][$i] = array(
                    'name'       => $supplierCode,
                    'production' => array(),
                );
                $j = 0;
                foreach ($production['production'] as $productionCode => $action) {
                    $config[$key]['Service']['supplier'][$i]['production'][$j] = array(
                        'name'   => $productionCode,
                        'action' => array(),
                    );
                    $k = 0;
                    foreach ($action['action'] as $actionCode => $option) {
                        $config[$key]['Service']['supplier'][$i]['production'][$j]['action'][$k] = array(
                            'name'   => $actionCode,
                            'option' => array(),
                        );
                        foreach ($option['option'] as $optionGroup => $optional) {
                            $config[$key]['Service']['supplier'][$i]['production'][$j]['action'][$k]['option'][] = array(
                                'name'   => $optionGroup,
                                'method' => $optional['Method'],
                            );
                        }
                        $k++;
                    }
                    $j++;
                }
                $i++;
            }
        }

        return $config;
    }
}
