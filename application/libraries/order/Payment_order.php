<?php

class Payment_order extends BaseLibrary
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     *  生成sms交易序號
     *
     *  @return string 交易序號
     */
    public function generateSmsTradeNo()
    {
        $tradeNo = date("ymdHi") . $this->generateRandomString();
        if ($this->ci->log_sms_model->CheckTradeUnique($tradeNo)) {
            return $tradeNo;
        } else {
            $this->generateSmsTradeNo();
        }
    }

    /**
     *  生成隨機數字字串
     *  --  隨機產生長度 6 - 10 的字串，在左邊補零至 10 位數
     *
     *  @return string 隨機字串
     */
    private function generateRandomString()
    {
        $length = rand(6, 10);
        $characters       = '0123456789';
        $charactersLength = strlen($characters);
        $randomString     = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        $randomString = str_pad($randomString, 10, "0", STR_PAD_LEFT);

        return $randomString;
    }

    /**
     *  產生調閱序號
     *
     *  @return string 交易序號
     */
    public function generateAccessNo()
    {
        $no = date("ymdHi") . $this->generateRandomString();
        $this->ci->load->model('access_model');
        if ($this->ci->access_model->CheckAccessNoUnique($no)) {
            return $no;
        } else {
            $this->generateAccessNo();
        }
    }
}
