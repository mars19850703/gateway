<?php

$suntech = array();

if (ENVIRONMENT == 'production') {
	$suntech['credit_auth'] = 'https://www.esafe.com.tw/Service/Ws_CardPayAuthorise.asmx?wsdl';
} else {
	$suntech['credit_auth'] = 'https://test.esafe.com.tw/Service/Ws_CardPayAuthorise.asmx?wsdl';
}