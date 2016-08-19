<?php

class BaseCheck extends BaseLibrary
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     *  檢查資料是否存在，並不為空
     *
     *  @param $fields array 要檢查的欄位
     *         array(
     *              "欄位名稱" => true or false (是否要檢查)
     *         )
     *  @param $data array 要被檢查的資料
     *  @return boolean
     */
    public function check(array $fields, array $data)
    {
        // check data is empty
        foreach ($fields as $field => $check) {
            // 是否要檢查
            if ($check) {
                if (isset($data[$field])) {
                    if (is_array($data[$field])) {
                        foreach ($data[$field] as $d) {
                            if (strlen($d) == 0 || is_null($d)) {
                                return false;
                            }
                        }
                    } else {
                        if (!isset($data[$field]) || strlen($data[$field]) === 0 || is_null($data[$field])) {
                            return false;
                        }
                    }
                } else {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     *  檢查有無 $OrderID，並符合規則
     *
     *  @param $orderId string 商店自定義的訂單編號
     *  @return boolean
     */
    public function validOrderId($orderId)
    {
        $pattern = "/^([a-zA-Z0-9_]{1,16})$/";
        if (!$this->regex($pattern, $orderId)) {
            return false;
        }
        
        return true;
    }

    /**
     *  驗證金額是否為數字，並有包含至小數第二位
     *
     *  @param $amount int 此次交易金額
     *  @return boolean
     */
    public function validAmount($amount)
    {
        $amount_explode = explode(".", $amount);
        if (!is_numeric($amount)) {
            return false;
        }
        // if (count($amount_explode) !== 2) {
        //     return false;
        // }
        // if (strlen($amount_explode[1]) !== 2) {
        //     return false;
        // }
        if (count($amount_explode) > 2) {
            return false;
        }

        if (floatval($amount) <= 0) {
            return false;
        }
        
        return true;
    }

    /**
     *  檢查幣別是否正確
     *
     *  @param $amount int 此次交易金額
     *  @return boolean
     */
    public function validCurrency($currency)
    {
        $this->ci->load->model("currency_model");
        $currency = strtolower($currency);
        $curr = $this->ci->currency_model->getCurrencyByName($currency);
        if (is_null($curr)) {
            return false;
        }

        return true;
    }

    /**
     *  驗證信用卡卡用是正確的
     *  --  正式環境都需驗證
     *  --  測試環境只允許，這組卡號 4000221111111111
     *  --  本機環境全驗證，除了 4000221111111111
     *
     *  @param $creditNo string 信用卡卡號
     *  @return boolean
     */
    public function validCreditNo($creditNo)
    {
        if (is_numeric($creditNo)) {
            if (ENVIRONMENT === "production") {
                // 驗證信用卡卡號
                if (!$this->checkCreditCardNo($creditNo)) {
                    return false;
                }
            } else {
                // 開放一組測試卡號
                if (ENVIRONMENT === "development" || ENVIRONMENT === "beta" || ENVIRONMENT === "preview") {
                    if ($creditNo !== "4000221111111111") {
                        return false;
                    }
                } else {
                    if ($creditNo !== "4000221111111111") {
                        // 驗證信用卡卡號
                        if (!$this->checkCreditCardNo($creditNo)) {
                            return false;
                        }
                    }
                }
            }
        } else {
            return false;
        }

        return true;
    }

    /**
     *  驗證信用卡有效日期
     *
     *  @param $creditExpire string 信用卡有效日期
     *  @return boolean
     */
    public function validCreditExpire($creditExpire)
    {
        if (!is_numeric($creditExpire)) {
            return false;
        } else if (strlen($creditExpire) !== 4 && strlen($creditExpire) !== 6) {
            return false;
        }

        return true;
    }

    /**
     *  驗證信用卡後面三碼
     *
     *  @param $creditCvv2 string 信用卡後面三碼
     *  @return boolean
     */
    public function validCreditCvv($creditCvv)
    {
        // 驗證信用卡後面三碼
        if (!is_numeric($creditCvv)) {
            return false;
        } else if (strlen($creditCvv) !== 3) {
            return false;
        }

        return true;
    }

    /**
     *  驗證手機號碼
     *
     *  @param $mobile string|array 手機號碼
     *  @return boolean
     */
    public function validMobile($mobile)
    {
        // 驗證是否為手機號碼
        if (is_array($mobile)){
            foreach ($mobile as $m){
                if ( !preg_match('/^09\d{8}$/',trim($m))) {
                    return false;
                }
            }
        }else {
            if ( !preg_match('/^09\d{8}$/',trim($mobile))) {
                return false;
            }
        }

        return true;
    }

    /**
     *  Validate URL
     *
     *  @access public
     *  @param string
     *  @return string
     */
    public function validUrl($url)
    {
        $pattern = "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i";
        if (!$this->regex($pattern, $url)) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     *  Validate Email
     *
     *  @access public
     *  @param string
     *  @return string
     */
    public function validEmail($email)
    {
        $pattern = '/^(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))$/iD';
        if (!$this->regex($pattern, $email)) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     *  正規表示式
     *
     *  @param $pattern string 正規表示式的規則
     *  @param $string strgin 需要驗證的字串
     *  @return boolean
     */
    private function regex($pattern, $string)
    {
        if (preg_match($pattern, $string) === 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *  檢查信用卡卡號是否正確
     *
     *  @param $cardNo string 卡號
     *  @return boolean
     */
    private function checkCreditCardNo($cardNo)
    {
        $no = str_split($cardNo);
        $noLength = count($no);

        if ($noLength === 16) {
            return $this->validCard16No($no);
        } else if ($noLength === 15) {
            return $this->validCard15No($no);
        } else if ($noLength === 14) {
            // return $this->validCard14No($no);
            return true;
        } else {
            return false;
        }
    }

    /**
     *  驗證 16 碼信用卡卡號
     *
     *  @param $cardNo string 信用卡卡號
     *  @return boolean
     */
    private function validCard16No($cardNo)
    {
        $weight = 0;
        for ($i = 0; $i < 15; $i++) {
            if ($i % 2 === 0) {
                $weightNum = intval($cardNo[$i]) * 2;
            } else {
                $weightNum = intval($cardNo[$i]) * 1;
            }

            if ($weightNum > 9) {
                $weight += ($weightNum - 9);
            } else {
                $weight += $weightNum;
            }
        }

        $checkCode = (10 - ($weight % 10)) % 10;

        // MY_Controller::dumpData($checkCode, $cardNo["15"]);

        if ($checkCode == $cardNo["15"]) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *  驗證 15 碼信用卡卡號
     *
     *  @param $cardNo string 信用卡卡號
     *  @return boolean
     */
    private function validCard15No($cardNo)
    {
        $weight = 0;
        for ($i = 0; $i < 14; $i++) {
            if ($i % 2 === 0) {
                $weightNum = intval($cardNo[$i]) * 1;
            } else {
                $weightNum = intval($cardNo[$i]) * 2;
            }

            if ($weightNum > 9) {
                $weight += ($weightNum - 9);
            } else {
                $weight += $weightNum;
            }
        }

        $checkCode = (10 - ($weight % 10)) % 10;

        if ($checkCode == $cardNo["14"]) {
            return true;
        } else {
            return false;
        }
    }
}
