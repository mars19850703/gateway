<?php

$line = array();

// URL
if (ENVIRONMENT == 'production') {
    $line['reserveUrl']     = 'https://api-pay.line.me/v2/payments/oneTimeKeys/pay';
    $line['statusCheckUrl'] = 'https://api-pay.line.me/v2/payments/orders/%s/check';
    $line['voidUrl']        = 'https://api-pay.line.me/v2/payments/orders/%s/void';
    $line['refundUrl']      = 'https://api-pay.line.me/v2/payments/orders/%s/refund';
    $line['queryUrl']       = 'https://api-pay.line.me/v2/payments/';
} else {
    $line['reserveUrl']     = 'https://sandbox-api-pay.line.me/v2/payments/oneTimeKeys/pay';
    $line['statusCheckUrl'] = 'https://sandbox-api-pay.line.me/v2/payments/orders/%s/check';
    $line['voidUrl']        = 'https://sandbox-api-pay.line.me/v2/payments/orders/%s/void';
    $line['refundUrl']      = 'https://sandbox-api-pay.line.me/v2/payments/orders/%s/refund';
    $line['queryUrl']       = 'https://sandbox-api-pay.line.me/v2/payments/';
}
