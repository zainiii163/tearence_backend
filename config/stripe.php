<?php

return [
    /*
    | Stripe card checkout (PaymentProcessor).
    | Keys: https://dashboard.stripe.com/apikeys
    */
    'enabled' => filter_var(env('STRIPE_ENABLED', true), FILTER_VALIDATE_BOOLEAN),

    'secret' => env('STRIPE_SECRET', ''),
    'publishable_key' => env('STRIPE_KEY', env('STRIPE_PUBLISHABLE_KEY', '')),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET', ''),

    'currency' => strtoupper((string) env('STRIPE_CURRENCY', 'USD')),

    /**
     * true  = always mock when no secret (or forced)
     * false = never mock
     * auto  = mock when secret missing / placeholder
     */
    'mock' => env('STRIPE_MOCK', 'auto'),

    'api_base' => 'https://api.stripe.com/v1',
];
