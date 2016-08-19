<?php

class Payment extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->fields = array(
            'ResType',
            'MerchantID',
            'TerminalID',
            '_Data',
            'Gateway',
        );

        $this->response = array(
            'Status' => 'Group',
        );
    }

    public function group()
    {
        // 設定額外參數
        $otherFields = array(
            // key server index
            'KI',
        );

        // array merge
        $this->fields = array_merge($this->fields, $otherFields);

        // MY_Controller::dumpData($this->data);

        // 取得傳入參數
        $this->data['postData'] = $this->input->post($this->fields, true);

        // 共同資料檢查
        $this->checkCommonParam(__FUNCTION__);

        // 更改錯誤訊息回傳方式
        $this->gateway_output->setMode('return');

        // MY_Controller::dumpData($this->data);

        // 將所有資料依序載入相對應的 modules 來處理
        foreach ($this->data['postData']['_Data'] as $method => $data) {

            // MY_Controller::dumpData($data);
            
            // 判斷資料格式是否正確
            if (!is_array($data)) {
                // 更改錯誤訊息回傳方式
                $this->gateway_output->setMode('gateway');
                $this->gateway_output->error('SYS_60003');
            }

            // 檢查服務代碼是否為空
            if (!isset($data['ServiceCode'])) {
                $this->response[$method] = $this->gateway_output->error('SYS_70003');
                continue;
            } else if (empty($data['ServiceCode'])) {
                $this->response[$method] = $this->gateway_output->error('SYS_70003');
                continue;
            }

            // 檢查終端代碼是否綁定 EDC
            if (!is_null($this->data['edc'])) {
                // 判斷 EDCID & EDCMac 是否有傳入
                if (!isset($data['EDCID']) && empty($data['EDCID'])) {
                    $this->response[$method] = $this->gateway_output->error('EDC_90000');
                    continue;
                }
                if (!isset($data['EDCMac']) && empty($data['EDCMac'])) {
                    $this->response[$method] = $this->gateway_output->error('EDC_90001');
                    continue;
                }
                // 判斷 EDCID & EDCMac 是否正確
                if ($data['EDCID'] !== $this->data['edc']['edc_id']) {
                    $this->response[$method] = $this->gateway_output->error('EDC_90002');
                    continue;
                }
                if ($data['EDCMac'] !== $this->data['edc']['edc_mac']) {
                    $this->response[$method] = $this->gateway_output->error('EDC_90003');
                    continue;
                }
                // 判斷 EDC 狀態
                if ($this->data['edc']['edc_status'] !== '1') {
                    $this->response[$method] = $this->gateway_output->error('EDC_90005');
                    continue;
                }

                // 檢查 AppName 是否為空
                if (!isset($data['AppName']) || is_null($data['AppName']) || empty($data['AppName'])) {
                    $this->response[$method] = $this->gateway_output->error('EDC_90009');
                    continue;
                }

                // 檢查 edc 是否有此 app
                $this->load->model('edc_set_model');
                $this->data['edcApp'] = $this->edc_set_model->getEdcAppByAppName($this->data['terminal']['edc_set_idx'], $data['AppName']);
                if (is_null($this->data['edcApp'])) {
                    $this->response[$method] = $this->gateway_output->error('EDC_90008');
                    continue;
                }
            } else {
                // 如果有傳入，則吐出錯誤
                if (isset($data['EDCID']) && !empty($data['EDCID'])) {
                    $this->response[$method] = $this->gateway_output->error('SYS_60002');
                    continue;
                }
                if (isset($data['EDCMac']) && !empty($data['EDCMac'])) {
                    $this->response[$method] = $this->gateway_output->error('SYS_60002');
                    continue;
                }
            }

            // MY_Controller::dumpData($this->data);

            // 判斷 service code 是否為 12 碼
            if (strlen($data['ServiceCode']) !== 12) {
                $this->response[$method] = $this->gateway_output->error('SYS_70005');
                continue;
            }

            // 分解 service code
            $service = str_split($data['ServiceCode'], 3);

            // 取得 ServiceCode 的各項資料
            $this->load->model('supplier_model');
            $supplier = $this->supplier_model->getSupplierByIdx($service[0]);
            if (is_null($supplier)) {
                $this->response[$method] = $this->gateway_output->error('SYS_70005');
                continue;
            }
            $this->load->model('product_model');
            $product = $this->product_model->getProductByIdx($service[1]);
            if (is_null($product)) {
                $this->response[$method] = $this->gateway_output->error('SYS_70005');
                continue;
            }
            $this->load->model('action_model');
            $action = $this->action_model->getActionByIdx($service[2]);
            if (is_null($action)) {
                $this->response[$method] = $this->gateway_output->error('SYS_70005');
                continue;
            }
            $this->load->model('option_model');
            $option = $this->option_model->getOptionByIdx($service[3]);
            if (is_null($option)) {
                $this->response[$method] = $this->gateway_output->error('SYS_70005');
                continue;
            }

            // 檢查服務代碼是否有綁定
            $this->load->model('terminal_service_mapping_model');
            $terminalServiceMapping = $this->terminal_service_mapping_model->getServiceMappingByTerminalIdxAndServiceIdx($this->data['terminal']['idx'], $supplier['idx'], $product['idx'], $action['idx'], $option['idx']);
            if (is_null($terminalServiceMapping)) {
                $this->response[$method] = $this->gateway_output->error('SYS_70005');
                continue;
            } else if ($terminalServiceMapping['service_status'] == '0') {
                $this->response[$method] = $this->gateway_output->error('SYS_70004');
                continue;
            } else if ($terminalServiceMapping['service_status'] == '8') {
                $this->response[$method] = $this->gateway_output->error('SYS_70013');
                continue;
            } else if ($terminalServiceMapping['service_status'] == '9') {
                $this->response[$method] = $this->gateway_output->error('SYS_70014');
                continue;
            } else if ($terminalServiceMapping['service_status'] == '1') {
                $this->response[$method] = $this->gateway_output->error('SYS_70006');
                continue;
            }

            // load module
            $actionCode = $action['action_code'];

            // get edc config
            $edcConfig = json_decode($this->data['terminal']['edc_config'], true);

            // set data ActReq 參數
            $actReq = $edcConfig[$this->data['edcApp']['app_name']]['ActReq'];

            // 判斷 gateway 版本是否正確
            if ($this->data['postData']['Gateway'] !== $this->data['edcApp']['gateway_version']) {
                $this->response[$method] = $this->gateway_output->error('SYS_20009');
                continue;
            }

            // get path
            $modulePath = APPPATH . 'libraries/' . $this->data['postData']['Gateway'];
            if (!file_exists($modulePath)) {
                $this->response[$method] = $this->gateway_output->error('SYS_70010');
                continue;
            }

            // MY_Controller::dumpData(APPPATH);

            $module = $this->data['postData']['Gateway'] . '/' . $supplier['supplier_code'] . '/' . $product['product_code'] . '/' . $actionCode;
            $this->load->library($module);

            // set edc
            $this->$actionCode->setEdc($this->data['edc']);
            // set config
            $this->$actionCode->setEdcConfig($edcConfig);
            // set connect log idx
            $this->$actionCode->setConnectLogIdx($this->data['connectLogIdx']);
            // set merchant
            $this->$actionCode->setMerchant($this->data['merchant']);
            // set terminal
            $this->$actionCode->setTerminal($this->data['terminal']);
            // set supplier
            $this->$actionCode->setSupplier($supplier);
            // set product
            $this->$actionCode->setProduct($product);
            // set action
            $this->$actionCode->setAction($action);
            // set optional
            $this->$actionCode->setOption($option);
            // init modules
            $this->$actionCode->init();

            // 重整 postData
            $postData = array(
                'ResType'    => $this->data['postData']['ResType'],
                'MerchantID' => $this->data['postData']['MerchantID'],
                'TerminalID' => $this->data['postData']['TerminalID'],
                'Gateway'    => $this->data['postData']['Gateway'],
                '_Data'      => $data,
                'service'    => $service,
            );

            // 用 option_group 來 call 相對應 function
            $moduleMethod = $option['option_group'];

            // 判斷是否由後台送來做請款
            if (isset($postData['_Data']['is_tms']) && $postData['_Data']['is_tms'] === 1 && $moduleMethod === 'auth') {
                $moduleMethod = 'request';
            }

            // MY_Controller::dumpData($postData, $actionCode, $moduleMethod);

            if (method_exists($this->$actionCode, $moduleMethod)) {
                // execute
                $result = $this->$actionCode->$moduleMethod($postData, $this->response);
                if (is_array($result)) {
                    $this->response[$method] = $result;
                } else {
                    $this->response[$method] = $this->gateway_output->error($result);
                }

                // 自動請款
                if ($moduleMethod === 'auth' && intval($actReq) === 1 && $supplier['supplier_code'] === 'spgateway') {
                    // execute
                    // $result = $this->$actionCode->request($postData, $this->response);
                    $result = $this->$actionCode->requestToSign($postData, $this->response);
                    if (is_array($result)) {
                        $this->response['request'] = $result;
                    } else {
                        $this->response['request'] = $this->gateway_output->error($result);
                    }
                }
            } else {
                $this->response[$method] = $this->gateway_output->error('SYS_70012');
                continue;
            }
        }

        // 結果輸出
        $this->gateway_output->groupOutput($this->response);
    }

    public function handovers()
    {
        // 設定額外參數
        $otherFields = array(
            // key server index
            'KI',
        );

        // array merge
        $this->fields = array_merge($this->fields, $otherFields);

        // 取得傳入參數
        $this->data['postData'] = $this->input->post($this->fields, true);

        // 共同資料檢查
        $this->checkCommonParam(__FUNCTION__);

        // MY_Controller::dumpData($this->data);

        // 檢查服務代碼是否為空
        if (!isset($this->data['postData']['_Data']['ServiceCode'])) {
            $this->gateway_output->error('SYS_70003');
        } else if (empty($this->data['postData']['_Data']['ServiceCode'])) {
            $this->gateway_output->error('SYS_70003');
        }

        if (!is_null($this->data['edc'])) {
            // 判斷 EDCID & EDCMac 是否有傳入
            if (!isset($this->data['postData']['_Data']['EDCID']) || empty($this->data['postData']['_Data']['EDCID'])) {
                $this->gateway_output->error('EDC_90000');
            }
            if (!isset($this->data['postData']['_Data']['EDCMac']) || empty($this->data['postData']['_Data']['EDCMac'])) {
                $this->gateway_output->error('EDC_90001');
            }
            // 判斷 EDCID & EDCMac 是否正確
            if ($this->data['postData']['_Data']['EDCID'] !== $this->data['edc']['edc_id']) {
                $this->gateway_output->error('EDC_90002');
            }
            if ($this->data['postData']['_Data']['EDCMac'] !== $this->data['edc']['edc_mac']) {
                $this->gateway_output->error('EDC_90003');
            }
            // 判斷 EDC 狀態
            if ($this->data['edc']['edc_status'] !== '1') {
                $this->gateway_output->error('EDC_90005');
            }

            // 檢查 AppName 是否為空
            if (is_null($this->data['postData']['_Data']['AppName']) || empty($this->data['postData']['_Data']['AppName'])) {
                $this->gateway_output->error('EDC_90009');
            }

            // 檢查 edc 是否有此 app
            $this->load->model('edc_set_model');
            $this->data['edcApp'] = $this->edc_set_model->getEdcAppByAppName($this->data['terminal']['edc_set_idx'], $this->data['postData']['_Data']['AppName']);
            if (is_null($this->data['edcApp'])) {
                $this->gateway_output->error('EDC_90008');
            }
        } else {
            // // 如果有傳入，則吐出錯誤
            // if (isset($this->data['postData']['_Data']['EDCID']) && !empty($this->data['postData']['_Data']['EDCID'])) {
            //     $this->gateway_output->error('SYS_60002');
            // }
            // if (isset($this->data['postData']['_Data']['EDCMac']) && !empty($this->data['postData']['_Data']['EDCMac'])) {
            //     $this->gateway_output->error('SYS_60002');
            // }
        }

        // 判斷 service code 是否為 12 碼
        if (strlen($this->data['postData']['_Data']['ServiceCode']) !== 12) {
            $this->gateway_output->error('SYS_70005');
        }

        // 分解 service code
        $service = str_split($this->data['postData']['_Data']['ServiceCode'], 3);

        // 取得 ServiceCode 的各項資料
        $this->load->model('supplier_model');
        $supplier = $this->supplier_model->getSupplierByIdx($service[0]);
        if (is_null($supplier)) {
            $this->gateway_output->error('SYS_70005');
        }
        $this->load->model('product_model');
        $product = $this->product_model->getProductByIdx($service[1]);
        if (is_null($product)) {
            $this->gateway_output->error('SYS_70005');
        }
        $this->load->model('action_model');
        $action = $this->action_model->getActionByIdx($service[2]);
        if (is_null($action)) {
            $this->gateway_output->error('SYS_70005');
        }
        $this->load->model('option_model');
        $option = $this->option_model->getOptionByIdx($service[3]);
        if (is_null($option)) {
            $this->gateway_output->error('SYS_70005');
        }

        // 檢查服務代碼是否有綁定
        $this->load->model('terminal_service_mapping_model');
        $terminalServiceMapping = $this->terminal_service_mapping_model->getServiceMappingByTerminalIdxAndServiceIdx($this->data['terminal']['idx'], $supplier['idx'], $product['idx'], $action['idx'], $option['idx']);
        if (is_null($terminalServiceMapping)) {
            $this->gateway_output->error('SYS_70005');
        } else if ($terminalServiceMapping['service_status'] == '0' || $terminalServiceMapping['service_status'] == '9') {
            $this->gateway_output->error('SYS_70004');
        } else if ($terminalServiceMapping['service_status'] == '1') {
            $this->gateway_output->error('SYS_70006');
        }

        // 檢查 service code 是否為交班
        if ($option['option_group'] !== 'handovers') {
            $this->gateway_output->error('SYS_70005');
        }

        // MY_Controller::dumpData($this->data);

        $output = array();
        $this->load->model('auth_model');
        $this->load->model('cancel_model');
        $this->load->model('request_model');
        $this->load->model('refund_model');

        $this->load->library('order/payment_order');
        $output['AccessNo'] = $this->payment_order->generateAccessNo();
        $output['Date']     = date('Y/m/d H:i:s');
        $output['Auth']     = $this->auth_model->getAuthTotalForNowByToday($this->data['postData']['TerminalID'], $this->data['postData']['MerchantID'], $service);
        if (is_null($output['Auth']['amount'])) {
            $output['Auth']['amount'] = '0';
        }
        $output['Request'] = $this->request_model->getRequestTotalForNowByToday($this->data['postData']['TerminalID'], $this->data['postData']['MerchantID'], $service);
        if (is_null($output['Request']['amount'])) {
            $output['Request']['amount'] = '0';
        }
        $output['Cancel'] = $this->cancel_model->getCancelTotalForNowByToday($this->data['postData']['TerminalID'], $this->data['postData']['MerchantID'], $service);
        if (is_null($output['Cancel']['amount'])) {
            $output['Cancel']['amount'] = '0';
        }
        $output['Refund'] = $this->refund_model->getRefundTotalForNowByToday($this->data['postData']['TerminalID'], $this->data['postData']['MerchantID'], $service);
        if (is_null($output['Refund']['amount'])) {
            $output['Refund']['amount'] = '0';
        }

        $this->load->model('access_model');
        $this->access_model->insertAccess($output);

        // MY_Controller::dumpData($output);

        $this->response['Status'] = 'HANDOVERS_00000';
        $this->response['Result'] = $output;

        $this->gateway_output->resultOutput($this->response);
    }

    public function reprint()
    {
        // 設定額外參數
        $otherFields = array(
            // key server index
            'KI',
        );

        // array merge
        $this->fields = array_merge($this->fields, $otherFields);

        // 取得傳入參數
        $this->data['postData'] = $this->input->post($this->fields, true);

        // 共同資料檢查
        $this->checkCommonParam(__FUNCTION__);

        // MY_Controller::dumpData($this->data);

        // 檢查服務代碼是否為空
        if (!isset($this->data['postData']['_Data']['ServiceCode'])) {
            $this->gateway_output->error('SYS_70003');
        } else if (empty($this->data['postData']['_Data']['ServiceCode'])) {
            $this->gateway_output->error('SYS_70003');
        }

        if (!is_null($this->data['edc'])) {
            // 判斷 EDCID & EDCMac 是否有傳入
            if (!isset($this->data['postData']['_Data']['EDCID']) || empty($this->data['postData']['_Data']['EDCID'])) {
                $this->gateway_output->error('EDC_90000');
            }
            if (!isset($this->data['postData']['_Data']['EDCMac']) || empty($this->data['postData']['_Data']['EDCMac'])) {
                $this->gateway_output->error('EDC_90001');
            }
            // 判斷 EDCID & EDCMac 是否正確
            if ($this->data['postData']['_Data']['EDCID'] !== $this->data['edc']['edc_id']) {
                $this->gateway_output->error('EDC_90002');
            }
            if ($this->data['postData']['_Data']['EDCMac'] !== $this->data['edc']['edc_mac']) {
                $this->gateway_output->error('EDC_90003');
            }
            // 判斷 EDC 狀態
            if ($this->data['edc']['edc_status'] !== '1') {
                $this->gateway_output->error('EDC_90005');
            }

            // 檢查 AppName 是否為空
            if (is_null($this->data['postData']['_Data']['AppName']) || empty($this->data['postData']['_Data']['AppName'])) {
                $this->gateway_output->error('EDC_90009');
            }

            // 檢查 edc 是否有此 app
            $this->load->model('edc_set_model');
            $this->data['edcApp'] = $this->edc_set_model->getEdcAppByAppName($this->data['terminal']['edc_set_idx'], $this->data['postData']['_Data']['AppName']);
            if (is_null($this->data['edcApp'])) {
                $this->gateway_output->error('EDC_90008');
            }
        } else {
            // // 如果有傳入，則吐出錯誤
            // if (isset($this->data['postData']['_Data']['EDCID']) && !empty($this->data['postData']['_Data']['EDCID'])) {
            //     $this->gateway_output->error('SYS_60002');
            // }
            // if (isset($this->data['postData']['_Data']['EDCMac']) && !empty($this->data['postData']['_Data']['EDCMac'])) {
            //     $this->gateway_output->error('SYS_60002');
            // }
        }

        // 判斷 service code 是否為 12 碼
        if (strlen($this->data['postData']['_Data']['ServiceCode']) !== 12) {
            $this->gateway_output->error('SYS_70005');
        }

        // 分解 service code
        $service = str_split($this->data['postData']['_Data']['ServiceCode'], 3);

        // 取得 ServiceCode 的各項資料
        $this->load->model('supplier_model');
        $supplier = $this->supplier_model->getSupplierByIdx($service[0]);
        if (is_null($supplier)) {
            $this->gateway_output->error('SYS_70005');
        }
        $this->load->model('product_model');
        $product = $this->product_model->getProductByIdx($service[1]);
        if (is_null($product)) {
            $this->gateway_output->error('SYS_70005');
        }
        $this->load->model('action_model');
        $action = $this->action_model->getActionByIdx($service[2]);
        if (is_null($action)) {
            $this->gateway_output->error('SYS_70005');
        }
        $this->load->model('option_model');
        $option = $this->option_model->getOptionByIdx($service[3]);
        if (is_null($option)) {
            $this->gateway_output->error('SYS_70005');
        }

        // 檢查服務代碼是否有綁定
        $this->load->model('terminal_service_mapping_model');
        $terminalServiceMapping = $this->terminal_service_mapping_model->getServiceMappingByTerminalIdxAndServiceIdx($this->data['terminal']['idx'], $supplier['idx'], $product['idx'], $action['idx'], $option['idx']);
        if (is_null($terminalServiceMapping)) {
            $this->gateway_output->error('SYS_70005');
        } else if ($terminalServiceMapping['service_status'] == '0' || $terminalServiceMapping['service_status'] == '9') {
            $this->gateway_output->error('SYS_70004');
        } else if ($terminalServiceMapping['service_status'] == '1') {
            $this->gateway_output->error('SYS_70006');
        }

        // 檢查 service code 是否為重印
        if ($option['option_group'] !== 'reprint') {
            $this->gateway_output->error('SYS_70005');
        }

        $this->load->model('auth_model');
        if ($this->data['postData']['_Data']['Type'] === 'auth') {
            if (isset($this->data['postData']['_Data']['OrderID'])) {
                if (empty($this->data['postData']['_Data']['OrderID'])) {
                    if ($this->data['postData']['_Data']['Type'] === 'auth') {
                        $authData = $this->auth_model->getRecentlyAuthByTerminalCode($supplier['idx'], $product['idx'], $this->data['postData']['MerchantID'], $this->data['postData']['TerminalID']);
                    } else {
                        $this->response['Status'] = 'TRA_20003';
                        $this->gateway_output->resultOutput($this->response);
                    }
                } else {
                    $authData = $this->auth_model->getAuthByMerchantIdAndOrderId($this->data['postData']['MerchantID'], $this->data['postData']['_Data']['OrderID']);
                }
            } else {
                if ($this->data['postData']['_Data']['Type'] === 'auth') {
                    $authData = $this->auth_model->getRecentlyAuthByTerminalCode($supplier['idx'], $product['idx'], $this->data['postData']['MerchantID'], $this->data['postData']['TerminalID']);
                } else {
                    $this->response['Status'] = 'TRA_20003';
                    $this->gateway_output->resultOutput($this->response);
                }
            }
        } else {
            if (isset($this->data['postData']['_Data']['OrderID']) && !empty($this->data['postData']['_Data']['Type'])) {
                $authData = $this->auth_model->getAuthByMerchantIdAndOrderId($this->data['postData']['MerchantID'], $this->data['postData']['_Data']['OrderID']);
            } else {
                $this->response['Status'] = 'TRA_20009';
                $this->gateway_output->resultOutput($this->response);
            }
        }

        if (is_null($authData)) {
            $this->response['Status'] = 'TRA_20003';
            $this->gateway_output->resultOutput($this->response);
        } else if ($authData['auth_status'] !== '1') {
            $this->response['Status'] = 'TRA_20003';
            $this->gateway_output->resultOutput($this->response);
        }

        // require module config
        include APPPATH . 'libraries/' . $authData['gateway_version'] . '/' . $supplier['supplier_code'] . '/Config.php';
        $config = ${$supplier['supplier_code']};
        $this->load->library('check_data/credit_info');
        $parameters = array();
        $this->load->model('cash_config_model');
        $cashConfig = $this->cash_config_model->getCashConfigByTerminalIdxAndServiceIdx($this->data['terminal']['idx'], $supplier['idx']);
        $cashConfig['config_data'] = json_decode($cashConfig['config_data'], true);

        switch ($this->data['postData']['_Data']['Type']) {
            case 'auth':
                $this->response['Status'] = 'REPRINT_00000';
                $parameters               = array(
                    'ServiceCode'     => str_pad($authData['supplier_idx'], 3, '0', STR_PAD_LEFT) . str_pad($authData['product_idx'], 3, '0', STR_PAD_LEFT) . str_pad($authData['action_idx'], 3, '0', STR_PAD_LEFT) . str_pad($authData['option_idx'], 3, '0', STR_PAD_LEFT),
                    'ProcessTerminal' => $authData['merchant_id'] . $authData['terminal_code'],
                    'OrderID'         => $authData['order_id'],
                    'ProcessNo'       => $authData['transaction_no'],
                    'Currency'        => $authData['currency'],
                    'Amount'          => $authData['amount'],
                    'PaymentDate'     => str_replace('-', '/', substr($authData['modify_time'], 0, 10)),
                    'PaymentTime'     => substr($authData['modify_time'], 11),
                    'Gateway'         => $supplier['supplier_code'],
                    'GatewayName'     => $supplier['supplier_name'],
                    'MerchantID'      => $cashConfig['config_data']['MerchantID'],
                    'TradeNo'         => $authData['trade_no'],
                    'Auth'            => $authData['auth_code'],
                    'AuthBankCode'    => $authData['auth_bank'],
                    'AuthBank'        => $config['auth_bank'][$authData['auth_bank']],
                    'AuthDate'        => str_replace('-', '/', $authData['auth_date']),
                    'AuthTime'        => $authData['auth_time'],
                    'CardType'        => $this->credit_info->getCardType($authData['card6no'], $authData['card_length']),
                    'Card6No'         => $authData['card6no'],
                    'Card4No'         => $authData['card4no'],
                    'Inst'            => $authData['inst'],
                    'InstFirst'       => $authData['inst_first'],
                    'InstEach'        => $authData['inst_each'],
                );
                break;
            case 'cancel':
                if ($authData['cancel_status'] === '1') {
                    $this->response['Status'] = 'REPRINT_00000';
                    $this->load->model('cancel_model');
                    $cancelData = $this->cancel_model->getCancelByAuthIdx($authData['idx']);
                    $parameters = array(
                        'ServiceCode'     => str_pad($authData['supplier_idx'], 3, '0', STR_PAD_LEFT) . str_pad($authData['product_idx'], 3, '0', STR_PAD_LEFT) . str_pad($authData['action_idx'], 3, '0', STR_PAD_LEFT) . str_pad($authData['option_idx'], 3, '0', STR_PAD_LEFT),
                        'ProcessTerminal' => $authData['merchant_id'] . $authData['terminal_code'],
                        'OrderID'         => $authData['order_id'],
                        'ProcessNo'       => $authData['transaction_no'],
                        'Currency'        => $authData['currency'],
                        'Amount'          => $authData['amount'],
                        'RequestDate'     => str_replace('-', '/', substr($cancelData['modify_time'], 0, 10)),
                        'RequestTime'     => substr($cancelData['modify_time'], 11),
                        'Gateway'         => $supplier['supplier_code'],
                        'GatewayName'     => $supplier['supplier_name'],
                        'MerchantID'      => $cashConfig['config_data']['MerchantID'],
                        'TradeNo'         => $authData['trade_no'],
                        'AuthCode'        => $authData['auth_code'],
                        'AuthBankCode'    => $authData['auth_bank'],
                        'AuthBank'        => $config['auth_bank'][$authData['auth_bank']],
                        'CardType'        => $this->credit_info->getCardType($authData['card6no'], $authData['card_length']),
                        'Card6No'         => $authData['card6no'],
                        'Card4No'         => $authData['card4no'],
                    );
                } else {
                    $this->response['Status'] = 'TRA_20003';
                }
                break;
            case 'refund':
                if ($authData['refund_status'] === '1') {
                    $this->response['Status'] = 'REPRINT_00000';
                    $this->load->model('refund_model');
                    $refundData = $this->refund_model->getRefundByAuthIdx($authData['idx']);
                    $parameters = array(
                        'ServiceCode'     => str_pad($authData['supplier_idx'], 3, '0', STR_PAD_LEFT) . str_pad($authData['product_idx'], 3, '0', STR_PAD_LEFT) . str_pad($authData['action_idx'], 3, '0', STR_PAD_LEFT) . str_pad($authData['option_idx'], 3, '0', STR_PAD_LEFT),
                        'ProcessTerminal' => $authData['merchant_id'] . $authData['terminal_code'],
                        'OrderID'         => $authData['order_id'],
                        'ProcessNo'       => $authData['transaction_no'],
                        'Currency'        => $authData['currency'],
                        'Amount'          => $authData['amount'],
                        'RequestDate'     => str_replace('-', '/', substr($refundData['modify_time'], 0, 10)),
                        'RequestTime'     => substr($refundData['modify_time'], 11),
                        'Gateway'         => $supplier['supplier_code'],
                        'GatewayName'     => $supplier['supplier_name'],
                        'MerchantID'      => $cashConfig['config_data']['MerchantID'],
                        'TradeNo'         => $authData['trade_no'],
                        'AuthCode'        => $authData['auth_code'],
                        'AuthBankCode'    => $authData['auth_bank'],
                        'AuthBank'        => $config['auth_bank'][$authData['auth_bank']],
                        'CardType'        => $this->credit_info->getCardType($authData['card6no'], $authData['card_length']),
                        'Card6No'         => $authData['card6no'],
                        'Card4No'         => $authData['card4no'],
                    );
                } else {
                    $this->response['Status'] = 'TRA_20003';
                }
                break;
            default:
                $this->response['Status'] = 'TRA_20010';
                break;
        }

        $this->response['Result'] = $parameters;

        $this->gateway_output->resultOutput($this->response);
    }

    protected function checkCommonParam($method = '')
    {
        // 檢查 ResType 參數是否正確
        if (!$this->gateway_check->response($this->data['postData']['ResType'])) {
            $this->gateway_output->error('SYS_70001');
        }

        // set output type
        $this->gateway_output->setOutputType(strtolower($this->data['postData']['ResType']));

        // 檢查 MerchantID 是否為空
        if (!$this->gateway_check->merchant($this->data['postData']['MerchantID'])) {
            $this->gateway_output->error('MER_10002');
        }

        // 檢查 TerminalID 是否為空 & 是否為四碼數字
        if (!$this->gateway_check->terminal($this->data['postData']['TerminalID'])) {
            $this->gateway_output->error('TERMINAL_80000');
        } else if (!$this->gateway_check->terminalFormat($this->data['postData']['TerminalID'])) {
            $this->gateway_output->error('TERMINAL_80001');
        }

        // 檢查版本是否為空
        if (!$this->gateway_check->gateway($this->data['postData']['Gateway'])) {
            $this->gateway_output->error('SYS_20010');
        }

        // 檢查 _Data 是否為空
        if (!$this->gateway_check->data($this->data['postData']['_Data'])) {
            $this->gateway_output->error('SYS_60000');
        }

        // 檢查商店代號是否存在
        $this->load->model('merchant_model');
        $this->data['merchant'] = $this->merchant_model->getMerchantByMerchantId($this->data['postData']['MerchantID']);

        // 檢查 merchant 狀態
        if (is_null($this->data['merchant'])) {
            // 代號錯誤
            $this->gateway_output->error('MER_10000');
        } else if ($this->data['merchant']['merchant_status'] == '1') {
            // 商店代號審核中
            $this->gateway_output->error('MER_10004');
        } else if ($this->data['merchant']['merchant_status'] == '8' || $this->data['merchant']['merchant_status'] == '9') {
            // 商店代號停用
            $this->gateway_output->error('MER_10001');
        }

        // 檢查終端代碼是否存在
        $this->load->model('terminal_model');
        $this->data['terminal'] = $this->terminal_model->getTerminalByMerchantIdxAndTerminalCode($this->data['merchant']['idx'], $this->data['postData']['TerminalID']);
        if (is_null($this->data['terminal'])) {
            $this->gateway_output->error('TERMINAL_80001');
        }

        // 檢查終端代碼是否停用
        if ($this->data['terminal']['terminal_status'] == '0') {
            $this->gateway_output->error('TERMINAL_80002');
        }

        // 檢查終端代碼使用期限是否過期
        if (intval($this->data['terminal']['use_end_time']) < time()) {
            $this->gateway_output->error('TERMINAL_80003');
        }

        // 檢查終端代碼是否綁定 EDC
        $this->load->model('edc_model');
        $this->data['edc'] = $this->edc_model->getEdcByEdcIdx($this->data['terminal']['edc_idx']);

        // 判斷有沒有 KI
        $factor = null;
        if (isset($this->data['postData']['KI'])) {
            if (!is_null($this->data['postData']['KI']) && !empty($this->data['postData']['KI'])) {
                switch (ENVIRONMENT) {
                    case 'development':
                        $url = 'http://key.dev.wecanpay.com.tw/key/get/';
                        break;
                    case 'beta':
                        $url = 'http://key.beta.wecanpay.com.tw/key/get/';
                        break;
                    case 'preview':
                        $url = 'https://key.pre.wecanpay.com.tw/key/get/';
                        break;
                    case 'production':
                        $url = 'https://key.wecanpay.com.tw/key/get/';
                        break;
                    default:
                        $url = 'http://key.wecanpay.localhost/key/get/';
                        break;
                }

                $context = stream_context_create(array('http' => array('header' => 'Connection: close\r\n')));
                $result  = json_decode(file_get_contents($url . $this->data['postData']['KI'], false, $context));

                if ($result->Status) {
                    $factor = $result->Key;
                }
            }
        }

        // 將 Data 解密
        $this->load->library('cryptography/cryptography');
        if (is_null($this->data['edc'])) {
            $this->data['postData']['_Data'] = $this->cryptography->decryption($this->data['merchant']['merchant_key'], $this->data['merchant']['merchant_iv'], $this->data['postData']['_Data']);
        } else {
            if (!is_null($factor)) {
                $this->data['postData']['_Data'] = $this->cryptography->decryption($factor->key, $factor->iv, $this->data['postData']['_Data'], $factor);
            } else {
                $this->gateway_output->error('SYS_60004');
            }
        }

        if (is_bool($this->data['postData']['_Data']) || !$this->data['postData']['_Data']) {
            $this->gateway_output->error('SYS_60001');
        }

        if (!is_array($this->data['postData']['_Data']) || empty($this->data['postData']['_Data'])) {
            $this->gateway_output->error('SYS_60000');
        }

        // 記錄解密完資料
        $this->load->model('log_connect_decode_model');
        $this->log_connect_decode_model->insertConnectDecode($this->data['connectLogIdx'], get_class($this), $method, $this->data['postData']);
    }
}
