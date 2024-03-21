<?php

namespace IntegrationPayments\PaymentsSdk\util;

use Exception;
use GuzzleHttp\Client;

class RequestUtil
{
    /**
     * 发出请求
     * @param string $url
     * @param string $method
     * @param array $data
     * @param float $timeout
     * @param int $retry
     * @return string|null
     * @throws Exception
     */
    public static function send(string $url, string $method = 'GET', array $data = [], float $timeout = 5.0, int $retry = 3): ?string
    {
        $requestClient = new Client([
            'timeout' => $timeout,
            'verify' => true,
        ]);
        $requestData = $data;
        if (isset($data['body']) && is_array($data['body'])) {
            $data['body'] = json_encode($data['body']);
        }
        $responseData = null;
        for ($i = 0; $i < $retry; $i++) {
            $logString = "URL: {$url}, METHOD: {$method}, DATA: " . json_encode($requestData, JSON_UNESCAPED_UNICODE) . ", TIMEOUT: {$timeout}, RETRY: " . ($i + 1);

            try {
                $response = $requestClient->request($method, $url, $data);
            } catch (\Throwable) {
                continue;
            }

            $responseStatusCode = $response->getStatusCode();
            if ($responseStatusCode == 200 || $responseStatusCode == 201 || $responseStatusCode == 202) {
                $responseData = $response->getBody()->getContents();
                break;
            } else {
                throw new Exception($logString, $responseStatusCode);
            }
        }
        return $responseData;
    }
}
