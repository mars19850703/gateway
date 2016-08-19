<?php

class Mitake extends BaseLibrary
{
    protected $response = array(
        'Status'  => '',
        'Message' => '',
        'Data'    => array(),
    );
    protected $param = array(
        // 使用者帳號。SmGateway資料庫表格SMUser中需有此使用者，且狀態為啟用。
        'username' => '',
        // 使用者密碼。
        'password' => '',
        // 受訊方手機號碼。請填入09帶頭的手機號碼。
        'dstaddr'  => '',
        // smbody及DestName的編碼方式(預設值為Big5)
        'encoding' => '',
        // 收訊人名稱。若其他系統需要與簡訊資料進行系統整合，此欄位可填入來源系統所產生的Key值，以對應回來源資料庫。
        'DestName' => '',
        // 訊預約時間。也就是希望簡訊何時送達手機，格式為YYYY-MM-DD HH:NN:SS或YYYYMMDDHHNNSS，或是整數值代表幾秒後傳送。
        // 若預約時間大於系統時間，則為預約簡訊，預約時間離目前時間若預約時間已過或為空白則為即時簡訊。即時簡訊為儘早送出，若受到MsgType宵禁欄位的限制，就不一定是立刻送出。
        'dlvtime'  => '',
        // 簡訊有效期限。格式為YYYY-MM-DD HH:NN:SS或YYYYMMDDHHNNSS，或是整數值代表傳送後幾秒後內有效。
        // 請勿超過大哥大業者預設之24小時期限，以避免業者不回覆簡訊狀態。
        'vldtime'  => '',
        // 簡訊內容。必須為BIG-5編碼，長度70個中文字或是160個英數字。若有換行的需求，請填入ASCII Code 6代表換行。為避免訊息中有&電文分隔符號，請將此欄位進行url encode。
        // url encode時，空白可用%20或維持空白即可，請勿將空白encode為+號。若對url encode有任何的相容性疑慮，建議使用%FF的方式將參數內容全部編碼即可。
        'smbody'   => '',
        // 狀態回報網址。請參考狀態回報的說明。
        'response' => '',
        // 客戶簡訊ID。用於避免重複發送，若有提供此參數，則會判斷該簡訊ID是否曾經發送，若曾發送過，則直接回覆之前發送的回覆值，並加上Duplicate=Y。
        // 這個檢查機制只留存12小時內的資料，12小時候後重複的ClientID”可能”會被當成新簡訊。此外，ClientID必須維持唯一性，而非只在12小時內唯一，建議可使用GUID來保證其唯一性。
        'ClientID' => '',
    );

    public function __construct()
    {
        parent::__construct();

        $this->ci->load->helper(array('common'));
        $this->ci->load->config('param/sms', true);
        $this->ci->load->library("order/payment_order");
        $this->ci->load->model('log_sms_model');

        $this->param['username'] = $this->ci->config->item('mitake_username', 'param/sms');
        $this->param['password'] = $this->ci->config->item('mitake_password', 'param/sms');
    }

    public function signleSms(array $originData)
    {
        $originData['sms_company'] = strtolower(__Class__);
        $tradeNo                   = $this->ci->payment_order->generateSmsTradeNo();
        $responseUrl               = (!empty($data['ResponseUrl'])) ? $data['ResponseUrl'] : '';
        $data                      = $originData['Data'];

        $this->param['dstaddr']  = $data['Mobile'];
        $this->param['encoding'] = $originData['Encoding'];
        $this->param['smbody']   = $data['Content'];
        $this->param['response'] = $this->ci->config->item('mitake_response_url', 'param/sms');
        $postData                = http_build_query($this->param);

        // 寫入log_sms
        $logSmsId = $this->ci->log_sms_model->insertLogSms($originData, $tradeNo, $data['Mobile'], $data['Content'], $responseUrl);

        $return     = _curl('GET', $this->ci->config->item('mitake_single_sms_url', 'param/sms'), $postData);
        $returnData = _param2array($return['body']);

        // $returnData = array('msgid' => '11111', 'statuscode' => 1);
        // update log_sms
        $updateWhere   = array('idx' => $logSmsId);
        $updateLogData = array(
            'msg_id'        => $returnData['msgid'],
            'response_code' => $returnData['statuscode'],
        );
        $this->ci->log_sms_model->update($updateLogData, $updateWhere);

        $this->response['Status'] = '00000';
        $this->response['Data']   = array(
            'SmsID'      => $tradeNo,
            'StatusCode' => $returnData['statuscode'],
        );

        return $this->response;
    }

    public function multiSms(array $originData, $type = 'singleArray')
    {
        $originData['sms_company'] = strtolower(__Class__);
        $data                      = $originData['Data'];

        $postUrl = $this->ci->config->item('mitake_multi_sms_url', 'param/sms') . '?' . http_build_query(array('username' => $this->param['username'], 'password' => $this->param['password'], 'encoding' => $originData['Encoding']));

        //
        $tmpContent = "[{i}]\ndstaddr={mobile}\nsmbody={content}\nresponse={response_url}\n";

        $smsContent = '';

        if ($type == 'singleArray') {
            if (is_string($data['Mobile'])) {
                $mobiles = explode(',', $data['Mobile']);
            } else {
                $mobiles = $data['Mobile'];
            }
        } else {
            $mobiles      = array();
            $contents     = array();
            $responseUrls = array(); // 要reponse給介接者的url
            foreach ($data as $key => $d) {
                $tradeNo            = $this->ci->payment_order->generateSmsTradeNo();
                $mobiles[$key]      = $d['Mobile'];
                $contents[$key]     = $d['Content'];
                $responseUrls[$key] = (!empty($d['ResponseUrl'])) ? $d['ResponseUrl'] : '';

                $this->response['Data'][$key] = array(
                    'SmsID'      => $tradeNo,
                    'StatusCode' => 0,
                );
            }
        }

        $ids = array();
        foreach ($mobiles as $index => $mobile) {
            $tradeNo     = $this->ci->payment_order->generateSmsTradeNo();
            $content     = (isset($contents[$index])) ? $contents[$index] : $data['Content'];
            $responseUrl = '';
            if (!empty($responseUrls[$index])) {
                $responseUrl = $responseUrls[$index];
            } else if (!empty($data['ResponseUrl'])) {
                $responseUrl = $data['ResponseUrl'];
            }

            $tmp = $tmpContent;
            $tmp = str_replace(array('{i}', '{mobile}', '{content}', '{response_url}'), array($index, $mobile, $content, $this->ci->config->item('mitake_response_url', 'param/sms')), $tmp);
            $smsContent .= $tmp;

            // 寫入log_sms
            $logSmsId = $this->ci->log_sms_model->insertLogSms($originData, $tradeNo, $mobile, $content, $responseUrl);
            array_push($ids, $logSmsId);
        }

        $smsContent = str_replace("\xef\xbb\xbf", '', $smsContent);

        $return     = _curl('POST', $postUrl, $smsContent);
        $returnData = parse_ini_string($return['body'], true);
        // $returnData = array(array('msgid' => '11111', 'statuscode' => 1), array('msgid' => '11112', 'statuscode' => 1));

        if (is_array($returnData)) {
            foreach ($returnData as $index => $d) {
                if (!empty($ids[$index])) {
                    // update log_sms
                    $updateWhere   = array('idx' => $ids[$index]);
                    $updateLogData = array(
                        'msg_id'        => $d['msgid'],
                        'response_code' => $d['statuscode'],
                    );
                    
                    $this->ci->log_sms_model->update($updateLogData, $updateWhere);
                }
                if (isset($this->response['Data'][$index]['StatusCode'])) {
                    $this->response['Data'][$index]['StatusCode'] = $d['statuscode'];
                }

            }
        }

        return $this->response;
    }

    public function longSms(array $data)
    {
        $this->param['dstaddr']    = $data['Mobile'];
        $this->param['encoding']   = $data['Encoding'];
        $this->param['smbody']     = $data['Content'];
        $this->param['CharsetURL'] = 'utf-8';
        $postData                  = http_build_query($this->param);

        var_dump($postData);

        // $return = _curl('GET', $this->ci->config->item('mitake_long_sms_url', 'param/sms'), $postData);
        // $returnData = _param2array($return['body']);
        // echo "<pre>";
        // var_dump($returnData);
    }

}
