<?php

class Sms_msg extends BaseCheck
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     *  sms資料檢查
     *
     *  @param $data array 資料
     *  @return boolean
     */
    public function sms(array $data)
    {
        if (empty($data["Mobile"])) {
            $this->ci->gateway_output->error("20000", "sms");
        }

        if (empty($data["Content"])) {
            $this->ci->gateway_output->error("20001", "sms");
        }

        $mobile = explode(',', $data['Mobile']);
        if (!$this->validMobile($mobile)) {
            $this->ci->gateway_output->error("20002", "sms");
        }        

        return $data;
    }


    

    public function multiSms(array $data)
    {
        $mobile = explode(',', $data['Mobile']);
        if (!$this->validMobile($mobile)) {
            $this->ci->gateway_output->error("20003");
        }

        if (!$this->validCreditNo($data["CardNo"])) {
            $this->ci->gateway_output->error("20002");
        }

        if (!$this->validCreditExpire($data["CardExpire"])) {
            $this->ci->gateway_output->error("30001");
        }

        if (!$this->validCreditCvv($data["Cvv2"])) {
            $this->ci->gateway_output->error("30002");
        }

        return $data;
    }

}
