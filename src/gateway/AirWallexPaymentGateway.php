<?php

namespace IntegrationPayments\PaymentsSdk\gateway;

use Exception;
use IntegrationPayments\PaymentsSdk\common\Constants;
use IntegrationPayments\PaymentsSdk\PaymentGateInterface;
use IntegrationPayments\PaymentsSdk\util\RequestUtil;
use Ramsey\Uuid\Uuid;

class AirWallexPaymentGateway implements PaymentGateInterface
{
    private string $domain;
    private string $clientId;
    private string $apiKey;
    private string $accessToken;


    public function __construct(string $env, string $clientId, string $apiKey, ?string $accessToken = '')
    {
        $this->domain    = $env === 'demo' ? Constants::ROUTE_AIRWALLEX_DEMO_DOMAIN : Constants::ROUTE_AIRWALLEX_DOMAIN;
        $this->clientId  = $clientId;
        $this->apiKey = $apiKey;
        $this->accessToken = $accessToken ?: $this->getAccessToken();
    }

    /**
     * Get AccessToken by Rest Api
     * @param bool $forceRefresh
     * @return  array|mixed|string
     * @throws Exception
     */
    public function getAccessToken(bool $forceRefresh = false)
    {
        $accessToken = getenv(Constants::ENV_KEY_AIRWALLEX_ACCESS_TOKEN, true);
        $expiresIn = getenv(Constants::ENV_KEY_AIRWALLEX_ACCESS_TOKEN_EXPIRES_IN, true);
        if ($accessToken && (time() < $expiresIn) && !$forceRefresh) {
            return $accessToken;
        }

        $data = [
            'headers' => [
                'Content-Type' => 'application/json',
                'x-client-id'  => $this->clientId,
                'x-api-key'    => $this->apiKey,
            ],
        ];
        $response = RequestUtil::send($this->domain . Constants::ROUTE_AIRWALLEX_TOKEN, 'POST', $data);
        $res = @json_decode($response, true);
        if (isset($res['error'])) {
            throw new Exception($res['error_description'], -1);
        }
        // save access_token
        $expiresTime = strtotime($res['expires_at']) - 10;
        putenv(Constants::ENV_KEY_AIRWALLEX_ACCESS_TOKEN_EXPIRES_IN . '=' . $expiresTime);
        putenv(Constants::ENV_KEY_AIRWALLEX_ACCESS_TOKEN . '=' . $res['token']);
        return $res['token'];
    }

    /**
     * Create Transaction
     * @param float $amount
     * @param string $currencyCode
     * @param array $metadata
     * @return  mixed
     * @throws Exception
     */
    public function createTransaction(float $amount, string $currencyCode = 'USD', array $metadata = [])
    {
        $accessToken = $this->accessToken ?: $this->getAccessToken();
        $data = [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer '. $accessToken,
            ],
            'json' => [
                'request_id'  => Uuid::uuid4()->toString(),
                'merchant_order_id' => $metadata['order_id'] ?? Uuid::uuid4()->toString(),
                'amount'      => $amount,
                'currency'    => $currencyCode,
            ]
        ];
        $response = RequestUtil::send($this->domain . Constants::ROUTE_AIRWALLEX_CREATE_PAYMENT_INTENTS, 'POST', $data);
        $res = @json_decode($response, true);
        if (!isset($res['id'])) {
            $errorMsg = 'debug_id: ' . $res['trace_id'] . ' details: ' . $res['message'];
            throw new Exception($errorMsg, -1);
        }
        return $res;
    }

    /**
     * Query transaction details by transaction id
     * @param string $transactionId
     * @return  mixed
     * @throws Exception
     */
    public function getTransactionDetails(string $transactionId)
    {
        $route = Constants::ROUTE_AIRWALLEX_RETRIEVE_PAYMENT_INTENTS . '/' . $transactionId;
        $accessToken = $this->accessToken ?: $this->getAccessToken();
        $data = [
            'headers' => [
                'Authorization' => 'Bearer '. $accessToken,
            ],
        ];
        $response = RequestUtil::send($this->domain . $route, 'GET', $data);
        return @json_decode($response, true);
    }

    /**
     * Capture Airwallex Payment Intents
     * @param string $transactionId
     * @return  array|null
     * @throws Exception
     */
    public function captureOrder(string $transactionId): ?array
    {
        $route = str_replace('{id}', $transactionId, Constants::ROUTE_AIRWALLEX_CAPTURE_PAYMENT_INTENTS);
        $accessToken = $this->accessToken ?: $this->getAccessToken();
        $data = [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer '. $accessToken,
            ],
            'json' => [
                'request_id' => Uuid::uuid4()->toString(),
            ]
        ];
        $response = RequestUtil::send($this->domain . $route, 'POST', $data);
        return @json_decode($response, true);
    }

}