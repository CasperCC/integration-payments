<?php

namespace IntegrationPayments\PaymentsSdk;

interface PaymentGateInterface
{
    public function getAccessToken(bool $forceRefresh = false);

    public function createTransaction(float $amount, string $currencyCode, array $metadata = []);

    public function getTransactionDetails(string $transactionId);
}