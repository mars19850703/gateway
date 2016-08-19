<?php

interface CreditPayment
{
    public function auth(array $postData, array $responseData);
    public function cancel(array $postData, array $responseData);
    public function request(array $postData, array $responseData);
    public function refund(array $postData, array $responseData);
    public function query(array $postData, array $responseData);
}
