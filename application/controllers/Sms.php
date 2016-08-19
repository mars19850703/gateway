<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 *
 *    @author Onion 201603
 */

class Sms extends MY_Controller
{
    protected $dataFields;

    public function __construct()
    {
        parent::__construct();
        // load payment config
        $this->config->load('param/gateway', true);

        // $this->load->library("check_data/gateway_check_data");

        $this->fields = array(
            "StoreID"      => true,
            "ResponseType" => true,
            'HashKey'      => true,
            "Ver"          => false,
            "Encoding"     => false,
            "Data"         => true,
        );

        $this->dataFields = array(
            "Mobile"      => true,
            'Content'     => true,
            'ReserveTime' => false,
            'CustomKey'   => false,
            'ResponseURL' => false,
        );
    }

    public function index()
    {
        // post data
        $this->data["post"] = $this->input->post(array_keys($this->fields), true);

        if (is_string($this->data['post']['Data'])) {
            $this->data['post']['Data'] = json_decode($this->data['post']['Data'], true);
        }

        // 檢查資料
        $this->checkCommonParam();

        $this->load->library("sms/mitake", null, 'sms');

        // 記錄log_sms_gateway
        $this->load->model('log_sms_gateway_model');
        $logSmsGatewayId                          = $this->log_sms_gateway_model->insertLogSmsGateway($this->data['post']);
        $this->data['post']['log_sms_gateway_id'] = $logSmsGatewayId;

        if (strpos($this->data['post']['Data']['Mobile'], ',') !== false) {
            $returnData = $this->sms->multiSms($this->data['post']);
        } else {
            $returnData = $this->sms->signleSms($this->data['post']);
        }

        // 檢查手機是否有用,分開，發送簡訊
        // if (mb_strlen($this->data['post']['Data']['Content']) > 70) {
        //     $this->mitake->longSms($this->data['post']['Data']);
        // }
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($returnData));   
    }

    public function multi()
    {
        $this->data["post"] = $this->input->post(array_keys($this->fields), true);

        if (is_string($this->data['post']['Data'])) {
            $this->data['post']['Data'] = json_decode($this->data['post']['Data'], true);
        }

        // 檢查資料
        $this->checkCommonParam(__FUNCTION__);

        $this->load->library("sms/mitake", null, 'sms');

        // 記錄log_sms_gateway
        $this->load->model('log_sms_gateway_model');
        $logSmsGatewayId                          = $this->log_sms_gateway_model->insertLogSmsGateway($this->data['post']);
        $this->data['post']['log_sms_gateway_id'] = $logSmsGatewayId;

        $returnData = $this->sms->multiSms($this->data['post'], 'multiArray');

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($returnData));   

    }

    /**
     * [notify description]
     * @param  [type] $smsCompany [description]
     * @return [type]             [description]
     */
    public function notify($smsCompany = null)
    {
        $this->load->helper('file');
        $this->load->config('param/sms', true);
        $this->load->model('log_sms_model');
        $Companys = $this->config->item('sms_company', 'param/sms');
        if (array_key_exists(strtolower($smsCompany), $Companys)) {
            if ($get = $this->input->get()) {
                write_file(APPPATH . '../files/get_sms_file_' . date("YmdHis") . '.txt', json_encode($get));
                // 更新資料
                $logSms = $this->log_sms_model->select(false, 'array', array('msg_id' => $get['msgid'], 'mobile'=> $get['dstaddr']));
                if ($logSms){
                    $where = array(
                        'idx' => $logSms['idx']
                    );
                    $updateData = array(
                        'response_code' => $get['StatusFlag'],
                        'response_content' => json_encode($get),
                        'response_ip' => $this->input->ip_address(),
                        'modify_time' => date("Y-m-d H:i:s"),
                    );
                    $this->log_sms_model->update($updateData, $where);

                    // response給介接者
                    if (!empty($logSms['response_url'])){
                        
                    }
                    
                }
                
            } 
        } 
          
    }

    /**
     *  gateway 共同檢查
     */
    private function checkCommonParam($method = 'single')
    {
        // load 檢查資料 library
        $this->load->library("check_data/sms_msg", null, "checkData");

        // 檢查商店代號是否空白
        if (is_null($this->data["post"]["StoreID"]) || empty($this->data["post"]["StoreID"])) {
            $this->gateway_output->error("10002", "sms");
        }

        // 檢查 ResponseType 參數是否正確
        $responseType = $this->config->item('response_type', 'param/gateway');
        if (!in_array(strtolower($this->data["post"]["ResponseType"]), $responseType)) {
            $this->gateway_output->error("70001", "sms");
        }

        // 檢查特店代號是否正確
        $this->load->model("merchant_model");
        $this->data["store"] = $this->merchant_model->getMerchantByMerchantId($this->data["post"]["StoreID"]);
        if (is_null($this->data["store"])) {
            // $this->gateway_output->error("10000", "sms");
        }

        // 檢查HashKey是否正確
        

        // 檢查是否有開通sms

        // 檢查餘額

        // 檢查傳入參數是否正確，若是multi就不檢查Data到下面去檢查
        if ($method == 'multi') {
            $this->fields['Data'] = false;
        }
        if (!$this->checkData->check($this->fields, $this->data["post"])) {
            $this->gateway_output->error("60002", "sms");
        }

        // // 檢查參數
        if ($method == 'single') {
            $this->data["post_data"] = $this->checkData->sms($this->data['post']['Data']);
            if (!$this->data["post_data"]) {
                $this->gateway_output->error("60003", "sms");
            }
        } else {
            $this->data["post_data"] = $this->checkData->multiSms($this->data['post']['Data'], $this->dataFields);
            if (!$this->data["post_data"]) {
                $this->gateway_output->error("60003", "sms");
            }
        }

    }

}
