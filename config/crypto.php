<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Crypto payments (site-wide checkout + optional affiliate payouts)
    |--------------------------------------------------------------------------
    | Provider: nowpayments (recommended) | mock
    | Mock is used when CRYPTO_MOCK=true/auto and API key is missing (sandbox-like).
    */
    'enabled' => env('CRYPTO_PAYMENTS_ENABLED', true),

    'provider' => env('CRYPTO_PROVIDER', 'nowpayments'),

    'currency' => env('CRYPTO_FIAT_CURRENCY', 'USD'),

    /** Preferred pay currencies shown at checkout */
    'pay_currencies' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('CRYPTO_PAY_CURRENCIES', 'usdttrc20,usdterc20,usdcmatic'))
    ))),

    /** Default stablecoin for auto-convert settlement intent */
    'settle_currency' => env('CRYPTO_SETTLE_CURRENCY', 'usdttrc20'),

    'nowpayments' => [
        'api_key' => env('NOWPAYMENTS_API_KEY', ''),
        'ipn_secret' => env('NOWPAYMENTS_IPN_SECRET', ''),
        'email' => env('NOWPAYMENTS_EMAIL', ''),
        'password' => env('NOWPAYMENTS_PASSWORD', ''),
        'payout_2fa' => env('NOWPAYMENTS_PAYOUT_2FA', ''),
        'api_url' => env('NOWPAYMENTS_API_URL', 'https://api.nowpayments.io/v1'),
        'sandbox_api_url' => env('NOWPAYMENTS_SANDBOX_API_URL', 'https://api-sandbox.nowpayments.io/v1'),
        'use_sandbox' => filter_var(env('NOWPAYMENTS_SANDBOX', false), FILTER_VALIDATE_BOOLEAN),
    ],

    /**
     * Mock mode: true | false | auto
     * auto = mock when API key missing (local / pre-prod QA).
     */
    'mock' => env('CRYPTO_MOCK', 'auto'),

    'invoice_ttl_minutes' => (int) env('CRYPTO_INVOICE_TTL_MINUTES', 60),
];
