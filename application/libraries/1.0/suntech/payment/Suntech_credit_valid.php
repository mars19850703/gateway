<?php

class Suntech_credit_valid extends BaseCheck
{
    public function __construct()
    {
        parent::__construct();
    }

    public function auth(array $postData)
    {
        $data = $postData['_Data'];

        // 檢查 OrderID 參數
        if (isset($data['OrderID']) && !empty($data['OrderID'])) {
            if (strlen($data['OrderID']) > 16 || strlen($data['OrderID']) === 0) {
                if (!$this->validOrderId($data['OrderID'])) {
                    return 'TRA_20003';
                }
            }
        } else {
            if (!empty($data['EDCID']) && !empty($data['EDCMac'])) {
                $data['OrderID'] = substr($postData['TerminalID'], 2) . time();
            } else {
                return 'TRA_20003';
            }
        }

        // 檢查幣別
        if (isset($data['Currency'])) {
            if (!$this->validCurrency($data['Currency'])) {
                return 'TRA_20007';
            }
        } else {
            return 'TRA_20007';
        }

        // 檢查金額
        if (isset($data['Amount'])) {
            if (!$this->validAmount($data['Amount'])) {
                return 'TRA_20002';
            }
        } else {
            return 'TRA_20002';
        }

        // 檢查信用卡卡號
        if (isset($data['CardNo'])) {
            if (!$this->validCreditNo($data['CardNo'])) {
                return 'CREDIT_30000';
            }
        } else {
            return 'CREDIT_30000';
        }

        // 檢查信用卡到期日
        if (isset($data['CardExpire'])) {
            if (!$this->validCreditExpire($data['CardExpire'])) {
                return 'CREDIT_30001';
            }
        } else {
            return 'CREDIT_30001';
        }

        if (isset($data['EDCID']) && isset($data['EDCMac'])) {
            if (!empty($data['EDCID']) && !empty($data['EDCMac'])) {
                if ($data['Cvv2'] !== '000') {
                    return 'CREDIT_30002';
                }
            } else {
                if (!$this->validCreditCvv($data['Cvv2'])) {
                    return 'CREDIT_30002';
                }
            }
        } else {
            return 'CREDIT_30002';
        }

        if (isset($data['Inst']) && !empty($data['Inst'])) {
            // 驗證分期付款
            if (!is_numeric($data['Inst'])) {
                return 'TRA_20008';
            }
        } else {
            $data['Inst'] = '0';
        }

        if (!isset($data['ProDesc'])) {
            $data['ProDesc'] = '';
        }

        return $data;
    }

    public function cancel(array $data)
    {
        if (isset($data['OrderID'])) {
            if (!$this->validOrderId($data['OrderID'])) {
                return 'TRA_20003';
            }
        } else {
            return 'TRA_20003';
        }

        // if (isset($data['Currency'])) {
        //     if (!$this->validCurrency($data['Currency'])) {
        //         return 'TRA_20007';
        //     }
        // } else {
        //     return 'TRA_20007';
        // }

        // if (isset($data['Amount'])) {
        //     if (!$this->validAmount($data['Amount'])) {
        //         return '20002';
        //     }
        // } else {
        //     return '20002';
        // }

        if (!isset($data['NotifyURL'])) {
            $data['NotifyURL'] = '';
        } else if (!$this->validUrl($data['NotifyURL'])) {
            return 'SYS_70009';
        }

        return $data;
    }

    public function request(array $data)
    {
        if (isset($data['OrderID'])) {
            if (!$this->validOrderId($data['OrderID'])) {
                return 'TRA_20003';
            }
        } else {
            return 'TRA_20003';
        }

        if (isset($data['Currency'])) {
            if (!$this->validCurrency($data['Currency'])) {
                return 'TRA_20007';
            }
        } else {
            return 'TRA_20007';
        }

        if (isset($data['Amount'])) {
            if (!$this->validAmount($data['Amount'])) {
                return 'TRA_20002';
            }
        } else {
            return 'TRA_20002';
        }

        if (!isset($data['NotifyURL'])) {
            $data['NotifyURL'] = '';
        } else if (!$this->validUrl($data['NotifyURL'])) {
            return 'SYS_70009';
        }

        return $data;
    }

    public function refund(array $data)
    {
        if (isset($data['OrderID'])) {
            if (!$this->validOrderId($data['OrderID'])) {
                return 'TRA_20003';
            }
        } else {
            return 'TRA_20003';
        }

        if (isset($data['Currency'])) {
            if (!$this->validCurrency($data['Currency'])) {
                return 'TRA_20007';
            }
        } else {
            return 'TRA_20007';
        }

        if (isset($data['Amount'])) {
            if (!$this->validAmount($data['Amount'])) {
                return 'TRA_20002';
            }
        } else {
            return 'TRA_20002';
        }

        if (!isset($data['NotifyURL'])) {
            $data['NotifyURL'] = '';
        } else if (!$this->validUrl($data['NotifyURL'])) {
            return 'SYS_70009';
        }

        return $data;
    }

    public function query(array $data)
    {
        if (isset($data['OrderID'])) {
            if (!$this->validOrderId($data['OrderID'])) {
                return 'TRA_20003';
            }
        }

        if (isset($data['CardNo'])) {
            if (!$this->validCreditNo($data['CardNo'])) {
                return 'CREDIT_30000';
            }
        }

        if (!isset($data['NotifyURL'])) {
            $data['NotifyURL'] = '';
        } else if (!$this->validUrl($data['NotifyURL'])) {
            return 'SYS_70009';
        }

        return $data;
    }

    public function queryToUpdate(array $postData)
    {
        $data = $postData['_Data'];
        
        if (isset($data['OrderID'])) {
            if (!$this->validOrderId($data['OrderID'])) {
                return 'TRA_20003';
            }
        }

        return $data;
    }
}
