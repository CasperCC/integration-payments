<?php

namespace IntegrationPayments\PaymentsSdk;

use Exception;
use IntegrationPayments\PaymentsSdk\common\Constants;
use IntegrationPayments\PaymentsSdk\util\RequestUtil;

class PayPalPaymentGateway implements PaymentGateInterface
{
    private string $domain;
    private string $username;
    private string $password;
    private string $grandType;
    private string $accessToken;

    /**
     * @param string $username
     * @param string $password
     * @param string $env
     * @param string $grandType
     * @throws Exception
     */
    public function __construct(string $username, string $password, string $env = 'sandbox', string $grandType = 'client_credentials')
    {
        $this->domain    = $env === 'sandbox' ? Constants::ROUTE_PAYPAL_SANDBOX_DOMAIN : Constants::ROUTE_PAYPAL_DOMAIN;
        $this->username  = $username;
        $this->password  = $password;
        $this->grandType = $grandType;
        $this->accessToken = $this->getAccessToken();
    }

    /**
     * Get AccessToken by Rest Api
     * @param bool $forceRefresh
     * @return string
     * @throws Exception
     */
    public function getAccessToken(bool $forceRefresh = false): string
    {
        $accessToken = getenv(Constants::ENV_KEY_PAYPAL_ACCESS_TOKEN, true);
        $expiresIn = getenv(Constants::ENV_KEY_PAYPAL_ACCESS_TOKEN_EXPIRES_IN, true);
        if ($accessToken && (time() < $expiresIn) && !$forceRefresh) {
            return $accessToken;
        }

        $data = [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Basic '. base64_encode($this->username . ':' . $this->password),
            ],
            'form_params' => [
                'grant_type' => $this->grandType,
            ],
        ];
        $response = RequestUtil::send($this->domain . Constants::ROUTE_PAYPAL_TOKEN, 'POST', $data);
        $res = @json_decode($response, true);
        if (isset($res['error'])) {
            throw new Exception($res['error_description'], -1);
        }
        // save access_token
        $expiresTime = $res['expires_in'] + time();
        putenv(Constants::ENV_KEY_PAYPAL_ACCESS_TOKEN_EXPIRES_IN . '=' . $expiresTime);
        putenv(Constants::ENV_KEY_PAYPAL_ACCESS_TOKEN . '=' . $accessToken);
        return $res['access_token'];
    }

    /**
     * Create Transaction
     * @param float $amount
     * @param string $currencyCode
     * @return array
     * @throws Exception
     */
    public function createTransaction(float $amount, string $currencyCode = 'USD'): array
    {
        $accessToken = $this->accessToken ?: $this->getAccessToken();
        $data = [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '. $accessToken,
            ],
            'json' => [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'amount' => [
                            'currency_code' => $currencyCode,
                            'value' => $amount,
                        ],
                    ]
                ],
                'payment_source' => [
                    'paypal' => [
                        'experience_context' => [
                            'payment_method_preference' => 'IMMEDIATE_PAYMENT_REQUIRED',
                            'shipping_preference' => 'NO_SHIPPING',
                            'brand_name' => 'Mespery',
                            'user_action' => 'PAY_NOW',
                        ],
                    ],
                ],
            ],
        ];
        $response = RequestUtil::send($this->domain . Constants::ROUTE_PAYPAL_CHECKOUT_ORDER, 'POST', $data);
        $res = @json_decode($response, true);
        if (!isset($res['id'])) {
            $errorMsg = 'debug_id: ' . $res['debug_id'] . ' details: ' . $res['details']['description'];
            throw new Exception($errorMsg, -1);
        }
        return $res;
    }

    /**
     * Query transaction details by transaction id
     * @param string $transactionId
     * @return null|array
     * @throws Exception
     */
    public function getTransactionDetails(string $transactionId): ?array
    {
        $route = Constants::ROUTE_PAYPAL_CHECKOUT_ORDER . '/' . $transactionId;
        $accessToken = $this->accessToken ?: $this->getAccessToken();
        $data = [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '. $accessToken,
            ],
        ];
        $response = RequestUtil::send($this->domain . $route, 'GET', $data);
        return @json_decode($response, true);
    }
    
}