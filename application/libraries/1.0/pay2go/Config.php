<?php

$pay2go = array();

// URL
if (ENVIRONMENT == 'production') {
    $pay2go['invoice_issue_url']     = 'https://inv.pay2go.com/API/invoice_issue';
    $pay2go['invoice_touch_url']     = 'https://inv.pay2go.com/API/invoice_touch_issue';
    $pay2go['invoice_invaild_url']   = 'https://inv.pay2go.com/API/invoice_invalid';
    $pay2go['invoice_allowance_url'] = 'https://inv.pay2go.com/API/allowance_issue';
    $pay2go['invoice_search_url']    = 'https://inv.pay2go.com/API/invoice_search';
} else {
    $pay2go['invoice_issue_url']     = 'https://cinv.pay2go.com/API/invoice_issue';
    $pay2go['invoice_touch_url']     = 'https://cinv.pay2go.com/API/invoice_touch_issue';
    $pay2go['invoice_invaild_url']   = 'https://cinv.pay2go.com/API/invoice_invalid';
    $pay2go['invoice_allowance_url'] = 'https://cinv.pay2go.com/API/allowance_issue';
    $pay2go['invoice_search_url']    = 'https://cinv.pay2go.com/API/invoice_search';
}
