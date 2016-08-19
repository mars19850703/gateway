<?php

class Gateway_output extends BaseLibrary
{
    protected $lang;
    protected $connectLogIdx;
    protected $outputType;
    protected $outputMode;
    protected $outputConstantsFile;
    protected $outputMsgPrefix;
    protected $response;

    public function __construct($param)
    {
        parent::__construct();

        // 根據傳入參數，讀取不同語言輸出參數檔
        $this->lang = $param["lang"];
        // 根據傳入參數，update 連線資料
        $this->connectLogIdx = $param['connectLogIdx'];
        // 設定輸出類型預設值
        $this->outputType = "json";
        $this->response   = array(
            "Status"  => "00000",
            "Message" => "",
        );
    }

    public function getOutputType()
    {
        return $this->outputType;
    }

    public function getMode()
    {
        return $this->outputMode;
    }

    public function getConstantsFile()
    {
        return $this->outputConstantsFile;
    }

    public function getMsgPrefix()
    {
        return $this->outputMsgPrefix;
    }

    public function setOutputType($type)
    {
        $this->outputType = $type;
    }

    public function setMode($mode)
    {
        $this->outputMode = $mode;
    }

    public function setConstantsFile($file)
    {
        $this->outputConstantsFile = $file;
    }

    public function setMsgPrefix($prefix)
    {
        $this->outputMsgPrefix = $prefix;
    }

    /**
     *  輸出訊息
     *
     *  @param $response array 要輸出的資料
     */
    public function output(array $response)
    {
        // update connect log
        $this->outputLog($response);

        $this->ci->output->set_status_header(200);
        $this->outputType = strtolower($this->outputType);

        switch ($this->outputType) {
            case 'json':
                $response = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $this->ci->output->set_content_type('application/json', 'utf-8');
                break;
            default:
                $response = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $this->ci->output->set_content_type('application/json', 'utf-8');
                break;
        }

        $this->ci->output->set_output($response);
        $this->ci->output->_display();
        exit;
    }

    public function groupOutput($result)
    {
        foreach ($result as $method => &$resultData) {
            if ($method !== 'Status') {
                if (!$resultData["Status"]) {
                    $resultData["Status"] = "DEFAULT_00001";
                }
                $status = $resultData["Status"];
                $code   = explode('_', $status);
                $offset = count($code) - 1;
                if ($code[$offset] === '00000') {
                    $resultData["Status"] = $code[$offset];
                }

                $resultData["Message"] = $this->buildOutputMessage($status);
            }
        }
        if (isset($result['Status'])) {
            $result['Message'] = $this->buildOutputMessage($result['Status']);
        }

        $this->output($result);
    }

    public function resultOutput($result, $mehtod = null)
    {
        $status = $result["Status"];
        $code   = explode('_', $status);
        $offset = count($code) - 1;
        if ($code[$offset] === '00000') {
            $result["Status"] = $code[1];
        }
        if (is_null($mehtod)) {
            $result["Message"] = $this->buildOutputMessage($status);
        } else {
            $result["Message"] = $this->buildOutputMessage($status, $method);
        }

        $this->output($result);
    }

    /**
     *  驗證 or 檢查錯誤 output
     *
     *  @param $errorCode int 錯誤代碼 (必填)
     *  @param $replace1 array 錯誤訊息，需替換的文字1
     *  @param $replace2 array 錯誤訊息，需替換的文字2
     *  @param .....
     */
    public function error()
    {
        // 接參數
        $errorMsg = func_get_args();

        // 建立 output 資訊
        $this->bulidOutputResponse($errorMsg);

        if ($this->outputMode === "web") {
            // 輸出網頁顯示
            show_error("訊息：" . $this->response["Message"], 404, "錯誤代碼：" . $this->response["Status"]);
        } else if ($this->outputMode === "return") {
            return $this->response;
        } else {
            // api 輸出格式
            $this->output($this->response);
        }
    }

    private function bulidOutputResponse($errorMsg)
    {
        $this->response["Status"] = $errorMsg[0];
        unset($errorMsg[0]);
        // array key reset
        $errorMsg                  = array_values($errorMsg);
        $this->response["Message"] = $this->buildOutputMessage($this->response["Status"], null, $errorMsg);
    }

    /**
     *  建立輸出訊息
     *
     *  @param $code int 錯誤代碼
     *  @param $method string 要輸出內容的 prefix
     *  @param $replace array|string 要替換訊息的字串
     *
     *  @return string
     */
    private function buildOutputMessage($code, $method = null, $replace = null)
    {
        $this->ci->lang->load($this->outputConstantsFile, $this->lang);
        if (is_null($replace) || empty($replace)) {
            if (is_null($method)) {
                return $this->ci->lang->line($this->outputMsgPrefix . $code);
            } else {
                return $this->ci->lang->line($this->outputMsgPrefix . $method . '_' . $code);
            }
        } else {
            if (is_null($method)) {
                return vsprintf($this->ci->lang->line($this->outputMsgPrefix . $code), $replace);
            } else {
                return vsprintf($this->ci->lang->line($this->outputMsgPrefix . $method . '_' . $code), $replace);
            }
        }
    }

    private function outputLog($response)
    {
        if (!empty($this->connectLogIdx)) {
            $this->ci->load->model('log_connect_model');
            $this->ci->load->model('log_connect_decode_model');
            $this->ci->log_connect_model->updateConnect($this->connectLogIdx, $response);
            $this->ci->log_connect_decode_model->updateConnectDecode($this->connectLogIdx, $response);
        }
    }
}
