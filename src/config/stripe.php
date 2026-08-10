<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Stripe API Keys
    |--------------------------------------------------------------------------
    */
    'api_key' => env('STRIPE_API_KEY'),
    'api_secret' => env('STRIPE_API_SECRET'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Currency Settings
    |--------------------------------------------------------------------------
    */
    'currency' => env('STRIPE_CURRENCY', 'usd'),
    
    /*
    |--------------------------------------------------------------------------
    | Payment Settings
    |--------------------------------------------------------------------------
    */
    'payment' => [
        'success_url' => env('STRIPE_SUCCESS_URL', '/payment/success'),
        'cancel_url' => env('STRIPE_CANCEL_URL', '/payment/cancel'),
        'webhook_url' => env('STRIPE_WEBHOOK_URL', '/webhook/stripe'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription Settings
    |--------------------------------------------------------------------------
    */
    'subscription' => [
        'trial_days' => env('STRIPE_TRIAL_DAYS', 14),
        'default_plan' => env('STRIPE_DEFAULT_PLAN', 'monthly'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Metadata
    |--------------------------------------------------------------------------
    */
    'metadata' => [
        'platform' => 'Laravel',
        'version' => '1.0.0',
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */
    'log' => [
        'channel' => env('STRIPE_LOG_CHANNEL', 'stack'),
        'level' => env('STRIPE_LOG_LEVEL', 'info'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Settings
    |--------------------------------------------------------------------------
    */
    'retry' => [
        'max_attempts' => env('STRIPE_RETRY_ATTEMPTS', 3),
        'delay' => env('STRIPE_RETRY_DELAY', 5000), // milliseconds
    ],
];