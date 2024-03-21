<?php

namespace IntegrationPayments\PaymentsSdk\common;

class Constants
{
    const ROUTE_PAYPAL_SANDBOX_DOMAIN = 'https://api-m.sandbox.paypal.com';
    const ROUTE_PAYPAL_DOMAIN = 'https://api-m.paypal.com';

    const ENV_KEY_PAYPAL_ACCESS_TOKEN = 'PAYPAL_ACCESS_TOKEN';
    const ENV_KEY_PAYPAL_ACCESS_TOKEN_EXPIRES_IN = 'PAYPAL_ACCESS_TOKEN_EXPIRES_IN';

    const ROUTE_PAYPAL_TOKEN = '/v1/oauth2/token';
    const ROUTE_PAYPAL_CHECKOUT_ORDER = '/v2/checkout/orders';
}