<?php

class Cron extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function request()
    {
        printf("========== %s start ==========\n", date('Y-m-d H:i:s'));

        $this->load->model('auth_model');
        $this->load->model('merchant_model');
        $this->load->model('terminal_model');
        $this->load->model('edc_model');
        $this->load->model('supplier_model');
        $this->load->model('product_model');
        $this->load->model('action_model');
        $this->load->model('option_model');
        $this->load->model('product_model');
        $toRequest = $this->auth_model->getBatchAuthToRequest();

        $total = count($toRequest);
        printf("Total : %d \n", $total);

        // MY_Controller::dumpData(ENVIRONMENT, date('Y-m-d H:i:s'));

        $i = 1;
        foreach ($toRequest as $request) {
            $merchant = $this->merchant_model->getMerchantByMerchantId($request['merchant_id']);
            $terminal = $this->terminal_model->getTerminalByMerchantIdxAndTerminalCode($merchant['idx'], $request['terminal_code']);
            $edc      = $this->edc_model->getEdcByEdcIdx($terminal['edc_idx']);
            $supplier = $this->supplier_model->getSupplierByIdx($request['supplier_idx']);
            $product  = $this->product_model->getProductByIdx($request['product_idx']);
            $action   = $this->action_model->getActionByIdx($request['action_idx']);
            $option   = $this->option_model->getOptionByIdx($request['option_idx']);

            $actionCode = $action['action_code'];
            $service    = array(
                $request['supplier_idx'],
                $request['product_idx'],
                $request['action_idx'],
                $request['option_idx'],
            );
            // get edc config
            $edcConfig = json_decode($terminal['edc_config'], true);

            $module = $request['gateway_version'] . '/' . $supplier['supplier_code'] . '/' . $product['product_code'] . '/' . $actionCode;
            $this->load->library($module);

            // set config
            $this->$actionCode->setEdcConfig($edcConfig);
            // set connect log idx
            $this->$actionCode->setConnectLogIdx($this->data['connectLogIdx']);
            // set merchant
            $this->$actionCode->setMerchant($merchant);
            // set terminal
            $this->$actionCode->setTerminal($terminal);
            // set supplier
            $this->$actionCode->setSupplier($supplier);
            // set product
            $this->$actionCode->setProduct($product);
            // set action
            $this->$actionCode->setAction($action);
            // set optional
            $this->$actionCode->setOption($option);
            // run init
            $this->$actionCode->init($option);

            $data = array(
                'OrderID'     => $request['order_id'],
                'Currency'    => $request['currency'],
                'Amount'      => $request['amount'],
                'ServiceCode' => str_pad($request['supplier_idx'], 3, '0', STR_PAD_LEFT) . str_pad($request['product_idx'], 3, '0', STR_PAD_LEFT) . str_pad($request['action_idx'], 3, '0', STR_PAD_LEFT) . str_pad($request['option_idx'], 3, '0', STR_PAD_LEFT),
                'EDCID'       => $edc['edc_id'],
                'EDCMac'      => $edc['edc_mac'],
                'AppName'     => $request['app_name'],
            );

            // 重整 postData
            $postData = array(
                'ResType'    => 'json',
                'MerchantID' => $request['merchant_id'],
                'TerminalID' => $request['terminal_code'],
                'Gateway'    => $request['gateway_version'],
                '_Data'      => $data,
                'service'    => $service,
            );

            // MY_Controller::dumpData($postData);

            $result = $this->$actionCode->request($postData, $this->response);

            // log record
            $this->load->model('log_request_cron_model');
            $insertData = array(
                'auth_idx'      => $request['idx'],
                'supplier_idx'  => $supplier['idx'],
                'post_data'     => json_encode($postData),
                'response_data' => json_encode($result),
                'create_time'   => date('Y-m-d H:i:s'),
            );
            $this->log_request_cron_model->insert($insertData);

            printf("%s, %d of %d, auth_idx : %d \n", date('Y-m-d H:i:s'), $i, $total, $request['idx']);
            $i++;

            // if (is_array($result)) {
            //     $this->response['request'][$request['order_id']] = $result;
            // } else {
            //     $this->response['request'][$request['order_id']] = $this->gateway_output->error($result);
            // }
        }

        printf("========== %s end ========== \n", date('Y-m-d H:i:s'));

        // MY_Controller::dumpData($this->response);
    }

    public function queryToUpdate()
    {
        printf("========== %s start ==========\n", date('Y-m-d H:i:s'));

        $this->load->model('auth_model');
        $this->load->model('merchant_model');
        $this->load->model('terminal_model');
        $this->load->model('edc_model');
        $this->load->model('supplier_model');
        $this->load->model('product_model');
        $this->load->model('action_model');
        $this->load->model('option_model');
        $this->load->model('product_model');
        $toQuery = $this->auth_model->getBatchAuthToRequest();

        $total = count($toQuery);
        printf("Total : %d \n", $total);

        // MY_Controller::dumpData(ENVIRONMENT, date('Y-m-d H:i:s'), $toQuery, $this->db->last_query());

        $i = 1;
        foreach ($toQuery as $query) {
            $merchant = $this->merchant_model->getMerchantByMerchantId($query['merchant_id']);
            $terminal = $this->terminal_model->getTerminalByMerchantIdxAndTerminalCode($merchant['idx'], $query['terminal_code']);
            $edc      = $this->edc_model->getEdcByEdcIdx($terminal['edc_idx']);
            $supplier = $this->supplier_model->getSupplierByIdx($query['supplier_idx']);
            $product  = $this->product_model->getProductByIdx($query['product_idx']);
            $action   = $this->action_model->getActionByIdx($query['action_idx']);
            $option   = $this->option_model->getOptionByIdx($query['option_idx']);

            $actionCode = $action['action_code'];
            $service    = array(
                $query['supplier_idx'],
                $query['product_idx'],
                $query['action_idx'],
                $query['option_idx'],
            );
            // get edc config
            $edcConfig = json_decode($terminal['edc_config'], true);

            $module = $query['gateway_version'] . '/' . $supplier['supplier_code'] . '/' . $product['product_code'] . '/' . $actionCode;
            $this->load->library($module);

            // set config
            $this->$actionCode->setEdcConfig($edcConfig);
            // set connect log idx
            $this->$actionCode->setConnectLogIdx($this->data['connectLogIdx']);
            // set merchant
            $this->$actionCode->setMerchant($merchant);
            // set terminal
            $this->$actionCode->setTerminal($terminal);
            // set supplier
            $this->$actionCode->setSupplier($supplier);
            // set product
            $this->$actionCode->setProduct($product);
            // set action
            $this->$actionCode->setAction($action);
            // set optional
            $this->$actionCode->setOption($option);
            // run init
            $this->$actionCode->init($option);

            $data = array(
                'OrderID'     => $query['order_id'],
                'Currency'    => $query['currency'],
                'Amount'      => $query['amount'],
                'ServiceCode' => str_pad($query['supplier_idx'], 3, '0', STR_PAD_LEFT) . str_pad($query['product_idx'], 3, '0', STR_PAD_LEFT) . str_pad($query['action_idx'], 3, '0', STR_PAD_LEFT) . str_pad($query['option_idx'], 3, '0', STR_PAD_LEFT),
                'EDCID'       => $edc['edc_id'],
                'EDCMac'      => $edc['edc_mac'],
                'AppName'     => $query['app_name'],
                'is_tms'      => 1,
            );

            // 重整 postData
            $postData = array(
                'ResType'    => 'json',
                'MerchantID' => $query['merchant_id'],
                'TerminalID' => $query['terminal_code'],
                'Gateway'    => $query['gateway_version'],
                '_Data'      => $data,
                'service'    => $service,
            );

            // MY_Controller::dumpData($postData);

            $result = $this->$actionCode->{__FUNCTION__}($postData, $this->response);

            // log record
            $this->load->model('log_query_cron_model');
            $insertData = array(
                'auth_idx'      => $query['idx'],
                'supplier_idx'  => $supplier['idx'],
                'post_data'     => json_encode($postData),
                'response_data' => json_encode($result),
                'create_time'   => date('Y-m-d H:i:s'),
            );
            $this->log_query_cron_model->insert($insertData);

            printf("%s, %d of %d, auth_idx : %d \n", date('Y-m-d H:i:s'), $i, $total, $query['idx']);
            $i++;

            print_r($result);
            echo "\n";

            // if (is_array($result)) {
            //     $this->response['request'][$request['order_id']] = $result;
            // } else {
            //     $this->response['request'][$request['order_id']] = $this->gateway_output->error($result);
            // }
        }

        printf("========== %s end ========== \n", date('Y-m-d H:i:s'));

        // MY_Controller::dumpData($this->response);
    }
}
