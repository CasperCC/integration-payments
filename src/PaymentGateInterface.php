<?php

namespace IntegrationPayments\PaymentsSdk;

interface PaymentGateInterface
{
    public function getAccessToken();

    public function createTransaction(float $amount, string $currencyCode);

    public function getTransactionDetails(string $transactionId);
}