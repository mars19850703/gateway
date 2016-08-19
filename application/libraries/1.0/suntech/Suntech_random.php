<?php

class Suntech_random extends BaseLibrary
{
    public function __construct()
    {
        parent::__construct();
        $this->ci->load->model("auth_model");
    }

    /**
     *  生成交易序號
     *
     *  @return string 交易序號
     */
    public function generateTransactionNo()
    {
        $transactionNo = date("ymdHi") . $this->generateRandomString();
        if ($this->ci->auth_model->CheckTransactionUnique($transactionNo)) {
            return $transactionNo;
        } else {
            $this->generateTransactionNo();
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
        $length           = rand(6, 10);
        $characters       = '0123456789';
        $charactersLength = strlen($characters);
        $randomString     = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        $randomString = str_pad($randomString, 10, "0", STR_PAD_LEFT);

        return $randomString;
    }
}
