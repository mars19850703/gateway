<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 *    目前開放的回傳類型
 */
$config["response_type"] = array(
    "json",
    // "xml"
);

/**
 *    目前接受的付款類型
 */
$config["payment_type"] = array(
    "Credit",
    "Atm",
);

$config["web_payment_type"] = array(
    "Credit"  => "WCredit",
    "Webatm"  => "WWebatm",
    "Vacc"    => "WVacc",
    "Cvs"     => "WCvs",
    "Barcode" => "WBarcode",
);
