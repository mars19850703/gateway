<?php

class Invoice_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *  發票紀錄
     *
     *  @param {array} $type 開發票類型
     *  @param {int} $paymentLogIdx 這筆交易認證的 payment log idx
     *  @param {array} $merchant 商店資訊
     *  @param {array} $postData 傳入的交易資料
     *  @return insertId || false
     */
    public function insertInvoice($type, $paymentLogIdx, array $merchant, array $postData)
    {
        $insertData = array(
            'type'                    => $type,
            'log_payment_gateway_idx' => intval($paymentLogIdx),
            'gateway_version'         => $postData['Gateway'],
            'app_name'                => $postData['_Data']['AppName'],
            'terminal_code'           => $postData['TerminalID'],
            'member_idx'              => $merchant['member_idx'],
            'merchant_id'             => $postData['MerchantID'],
            'supplier_idx'            => intval($postData['service'][0]),
            'product_idx'             => intval($postData['service'][1]),
            'action_idx'              => intval($postData['service'][2]),
            'option_idx'              => intval($postData['service'][3]),
            'order_id'                => $postData['_Data']['OrderID'],
            'currency'                => $postData['_Data']['Currency'],
            'amount'                  => floatval($postData['_Data']['Amount']),
            'status'                  => intval($postData['_Data']['Status']),
            'category'                => $postData['_Data']['Category'],
            'buyer_name'              => $postData['_Data']['BuyerName'],
            'buyer_ubn'               => $postData['_Data']['BuyerUBN'],
            'buyer_email'             => $postData['_Data']['BuyerEmail'],
            'print_flag'              => $postData['_Data']['PrintFlag'],
            'tax_type'                => intval($postData['_Data']['TaxType']),
            'tax_rate'                => intval($postData['_Data']['TaxRate']),
            'tax_amount'              => floatval($postData['_Data']['TaxAmount']),
            'total_amount'            => floatval($postData['_Data']['TotalAmount']),
            'item_name'               => implode('|', $postData['_Data']['ItemName']),
            'item_count'              => implode('|', $postData['_Data']['ItemCount']),
            'item_unit'               => implode('|', $postData['_Data']['ItemUnit']),
            'item_price'              => implode('|', $postData['_Data']['ItemPrice']),
            'item_amount'             => implode('|', $postData['_Data']['ItemAmount']),
            'comment'                 => $postData['_Data']['Comment'],
            'create_time'             => date('Y-m-d H:i:s'),
            'ip'                      => $this->input->ip_address(),
            'issue_status'            => 0,
        );

        return $this->insert($insertData);
    }

    public function updateInvoice($invoiceIdx, $transactionResult, $status)
    {
        $where = array(
            'idx' => intval($invoiceIdx),
        );

        $updateData = array(
            'response_code' => $transactionResult['data']->Status,
            'response_msg'  => $transactionResult['data']->Message,
            'modify_time'   => date('Y-m-d H:i:s'),
        );

        if ($transactionResult['code'] === 'INVOICE_ISSUE_00000') {
            $updateData['issue_status']     = intval($status);
            $updateData['invoice_trade_no'] = $transactionResult['data']->Result->InvoiceTransNo;
            $updateData['invoice_number']   = $transactionResult['data']->Result->InvoiceNumber;
            $updateData['random_number']    = $transactionResult['data']->Result->RandomNum;
            $updateData['barcode']          = $transactionResult['data']->Result->BarCode;
            $updateData['qrcode_l']         = $transactionResult['data']->Result->QRcodeL;
            $updateData['qrcode_r']         = $transactionResult['data']->Result->QRcodeR;
        }

        return $this->update($updateData, $where);
    }

    public function updateInvoiceStatus($type, $invoiceIdx, $status, $param = null)
    {
        $where = array(
            'idx' => intval($invoiceIdx),
        );

        $updateData = array();
        switch ($type) {
            case 'lock':
                $updateData['lock_status'] = intval($status);
                break;
            case 'issue':
                $updateData['issue_status'] = intval($status);
                break;
            case 'touch':
                $updateData['touch_status'] = intval($status);
                break;
            case 'invalid':
                $updateData['invalid_status'] = intval($status);
                break;
            case 'allowance':
                $updateData['allowance_status'] = intval($status);
                $updateData['allowance_no']     = $param['allowance_no'];
                $updateData['allowance_amount'] = $param['allowance_amount'];
                break;
            default:
                break;
        }

        return $this->update($updateData, $where);
    }

    public function getInvoiceToTouch(array $postData)
    {
        $where = array(
            'status'       => 0,
            'issue_status' => 2,
            'lock_status'  => 1,
            'merchant_id'  => $postData['MerchantID'],
            'order_id'     => $postData['_Data']['OrderID'],
        );
        return $this->select(false, 'array', $where);
    }

    public function getInvoiceToInvalid(array $postData)
    {
        $where = array(
            'issue_status'     => 1,
            'allowance_status' => 0,
            'lock_status'      => 1,
            'merchant_id'      => $postData['MerchantID'],
            'invoice_number'   => $postData['_Data']['InvoiceNumber'],
        );

        return $this->select(false, 'array', $where);
    }

    public function getInvoiceByInvoiceNumber($merchantId, $invoiceNumber)
    {
        $where = array(
            'merchant_id'    => $merchantId,
            'invoice_number' => $invoiceNumber,
        );

        return $this->select(false, 'array', $where);
    }
}
