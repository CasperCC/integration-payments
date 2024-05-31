<?php

namespace IntegrationPayments\PaymentsSdk\common;

class Constants
{
    const ROUTE_PAYPAL_SANDBOX_DOMAIN = 'https://api-m.sandbox.paypal.com';
    const ROUTE_PAYPAL_DOMAIN = 'https://api-m.paypal.com';
    const ROUTE_AIRWALLEX_DEMO_DOMAIN = 'https://api-demo.airwallex.com/api';
    const ROUTE_AIRWALLEX_DOMAIN = 'https://api.airwallex.com/api';

    const ENV_KEY_PAYPAL_ACCESS_TOKEN = 'PAYPAL_ACCESS_TOKEN';
    const ENV_KEY_PAYPAL_ACCESS_TOKEN_EXPIRES_IN = 'PAYPAL_ACCESS_TOKEN_EXPIRES_IN';
    const ENV_KEY_AIRWALLEX_ACCESS_TOKEN = 'AIRWALLEX_ACCESS_TOKEN';
    const ENV_KEY_AIRWALLEX_ACCESS_TOKEN_EXPIRES_IN = 'AIRWALLEX_ACCESS_TOKEN_EXPIRES_IN';

    const ROUTE_PAYPAL_TOKEN = '/v1/oauth2/token';
    const ROUTE_PAYPAL_CHECKOUT_ORDER = '/v2/checkout/orders';
    const ROUTE_PAYPAL_CAPTURE_ORDER = '/v2/checkout/orders/{id}/capture';

    const ROUTE_AIRWALLEX_TOKEN = '/v1/authentication/login';
    const ROUTE_AIRWALLEX_CREATE_LINK = '/v1/pa/payment_links/create';
    const ROUTE_AIRWALLEX_CHECK_ORDER = '/v1/payments';
}