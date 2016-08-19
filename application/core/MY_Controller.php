<?php

class MY_Controller extends CI_Controller
{
    /** 資料物件 **/
    public $data;
    /** 回傳訊息 **/
    protected $response;
    // 參數名稱，是否必填
    protected $fields;

    public function __construct()
    {
        parent::__construct();
        // load 連線紀錄 model
        $this->load->model('log_connect_model');
        // load check data library
        $this->load->library('check_data/gateway_check');
        $this->data     = array();
        $this->response = array();
    }

    public static function dumpData()
    {
        $data = func_get_args();
        echo '<pre>';
        foreach ($data as $d) {
            var_dump($d);
            echo '<br/>';
        }
        die;
    }

    public static function printData()
    {
        $data = func_get_args();
        echo '<pre>';
        foreach ($data as $d) {
            print_r($d);
            echo '<br/>';
        }
        die;
    }

    public function _remap($method, $arguments = array())
    {
        if (method_exists($this, $method)) {

            // MY_Controller::dumpData($method, $arguments, get_class($this), $_POST);

            $class = get_class($this);
            $noRecord = array(
                'Test',
                'Cron'
            );

            if (!in_array($class, $noRecord)) {
                // log
                $this->data['connectLogIdx'] = $this->log_connect_model->insertConnect($class, $method, $arguments, $_POST);
            } else {
                $this->data['connectLogIdx'] = 0;
            }

            // load 輸出 library
            $outputParam = array(
                'lang'          => 'zh',
                'connectLogIdx' => $this->data['connectLogIdx'],
            );
            $this->load->library('output/gateway_output', $outputParam);
            $this->gateway_output->setMode($method);
            $this->gateway_output->setConstantsFile('gateway');
            if ($class === 'Sms') {
                $this->gateway_output->setMsgPrefix('SMS_MESSAGE_');
            } else {
                // $this->gateway_output->setMsgPrefix(strtoupper($class) . '_');
            }
            return call_user_func_array(array($this, $method), $arguments);
        }

        show_404();
    }
}
