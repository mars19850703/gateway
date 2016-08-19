<?php

class Notify extends BaseLibrary
{
    protected $merchant;
    protected $terminal;
    protected $config;

    public function __construct()
    {
        parent::__construct();
        // load permission array
        $this->ci->config->load('global_common', true);
        $this->ci->load->library('email');
    }

    public function verify()
    {
        $fields = array(
            'CheckCode',
            'MerchantID',
            'Date',
            'UseInfo',
            'CreditInst',
            'CreditRed',
        );
        $this->data['postData'] = $this->ci->input->post($fields, true);

        if ($this->checkCode()) {
            // get terminal service
            $this->ci->load->model('terminal_service_mapping_model');
            $this->ci->load->model('edc_update_model');

            foreach ($this->data['terminal'] as $terminal) {
                $service = $this->ci->terminal_service_mapping_model->getPay2goCreditServiceByTerminalIdx($terminal['idx']);
                foreach ($service as $s) {
                    if ($s['option_group'] === 'auth') {
                        if ($s['option_code'] === 'inst-0') {
                            if ($this->data['postData']['UseInfo'] === 'ON') {
                                $updateData = array(
                                    'service_status' => 2,
                                );
                            } else {
                                $updateData = array(
                                    'service_status' => 8,
                                );
                            }
                            $where = array(
                                'terminal_idx' => $s['terminal_idx'],
                                'supplier_idx' => $s['supplier_idx'],
                                'product_idx'  => $s['product_idx'],
                                'action_idx'   => $s['action_idx'],
                                'option_idx'   => $s['option_idx'],
                            );
                            $this->ci->terminal_service_mapping_model->update($updateData, $where);
                        } else {
                            if ($this->data['postData']['CreditInst'] === 'ON') {
                                $updateData = array(
                                    'service_status' => 2,
                                );
                            } else {
                                $updateData = array(
                                    'service_status' => 8,
                                );
                            }
                            $where = array(
                                'terminal_idx' => $s['terminal_idx'],
                                'supplier_idx' => $s['supplier_idx'],
                                'product_idx'  => $s['product_idx'],
                                'action_idx'   => $s['action_idx'],
                                'option_idx'   => $s['option_idx'],
                            );
                            $this->ci->terminal_service_mapping_model->update($updateData, $where);
                        }
                    }
                }

                // insert update config
                $this->ci->edc_update_model->insertEdcConfigUpdate(0, $terminal);
            }

            // send email to cs@wecanpay.tw
            $content = $this->ci->load->view('email/merchant/notify', $this->data, true);
            $email = $this->ci->config->item('notify_email', 'global_common');
            $this->ci->email->sendMail($email, array('email' => 'cs@wecanpay.tw'), '智付通信用卡開通通知信', $content);
        }
    }

    private function checkCode()
    {
        $checkArray = array(
            'MerchantID' => $this->data['postData']['MerchantID'],
            'Date'       => $this->data['postData']['Date'],
            'UseInfo'    => $this->data['postData']['UseInfo'],
            'CreditInst' => $this->data['postData']['CreditInst'],
            'CreditRed'  => $this->data['postData']['CreditRed'],
        );

        ksort($checkArray);

        $merchantId   = substr($this->data['postData']['MerchantID'], 3, 7);
        $terminalCode = substr($this->data['postData']['MerchantID'], 10, 4);

        // get merchant
        $this->ci->load->model('merchant_model');
        $this->data['merchant'] = $this->ci->merchant_model->getMerchantByMerchantId($merchantId);

        // get terminal
        $this->ci->load->model('terminal_model');
        $this->data['terminal'] = $this->ci->terminal_model->getTerminalByMerchantIdx($this->data['merchant']['idx'], $terminalCode);

        if (count($this->data['terminal']) !== 0) {
            $config    = $this->ci->config->item('spg_merchant', 'global_common');
            $checkStr  = http_build_query($checkArray);
            $checkCode = 'HashIV=' . $config['iv'] . '&' . $checkStr . '&HashKey=' . $config['key'];
            $checkCode = strtoupper(hash('sha256', $checkCode));

            if ($checkCode == $this->data['postData']['CheckCode']) {
                return true;
            }
        }

        return false;
    }
}
