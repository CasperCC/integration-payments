<?php

namespace IntegrationPayments\PaymentsSdk\gateway;

use Exception;
use IntegrationPayments\PaymentsSdk\common\Constants;
use IntegrationPayments\PaymentsSdk\PaymentGateInterface;
use IntegrationPayments\PaymentsSdk\util\RequestUtil;

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

    public function createTransaction(float $amount, string $currencyCode = 'USD', array $metadata = [])
    {
        $accessToken = $this->accessToken ?: $this->getAccessToken();
        $data = [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer '. $accessToken,
            ],
            'json' => [
                'amount'      => $amount,
                'currency'    => $currencyCode,
                'reusable'    => false,
                'title'       => $metadata['title'] ?? '',
                'description' => $metadata['description'] ?? null,
                'metadata'    => $metadata['metadata'] ?? null,
            ]
        ];
        $response = RequestUtil::send($this->domain . Constants::ROUTE_AIRWALLEX_CREATE_LINK, 'POST', $data);
        $res = @json_decode($response, true);
        if (!isset($res['id'])) {
            $errorMsg = 'debug_id: ' . $res['trace_id'] . ' details: ' . $res['message'];
            throw new Exception($errorMsg, -1);
        }
        return $res;
    }

    public function getTransactionDetails(string $transactionId)
    {
        $route = Constants::ROUTE_AIRWALLEX_CHECK_ORDER . '/' . $transactionId;
        $accessToken = $this->accessToken ?: $this->getAccessToken();
        $data = [
            'headers' => [
                'Authorization' => 'Bearer '. $accessToken,
            ],
        ];
        echo $this->domain . $route;die;
        $response = RequestUtil::send($this->domain . $route, 'GET', $data);
        var_dump($response);die;
        return @json_decode($response, true);
    }
}