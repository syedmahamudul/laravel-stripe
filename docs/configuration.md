
## 2. configuration.md

```markdown
# Configuration Guide

## Environment Variables

| Variable | Description | Required | Default |
|----------|-------------|----------|---------|
| `STRIPE_API_KEY` | Stripe Publishable Key | Yes | - |
| `STRIPE_API_SECRET` | Stripe Secret Key | Yes | - |
| `STRIPE_WEBHOOK_SECRET` | Stripe Webhook Secret | Yes | - |
| `STRIPE_CURRENCY` | Default Currency | No | usd |
| `STRIPE_SUCCESS_URL` | Success Redirect URL | No | /payment/success |
| `STRIPE_CANCEL_URL` | Cancel Redirect URL | No | /payment/cancel |
| `STRIPE_WEBHOOK_URL` | Webhook Endpoint URL | No | /stripe/webhook |
| `STRIPE_TRIAL_DAYS` | Default Trial Days | No | 14 |
| `STRIPE_DEFAULT_PLAN` | Default Plan ID | No | - |
| `STRIPE_LOG_CHANNEL` | Log Channel | No | stack |
| `STRIPE_LOG_LEVEL` | Log Level | No | info |
| `STRIPE_RETRY_ATTEMPTS` | Retry Attempts | No | 3 |
| `STRIPE_RETRY_DELAY` | Retry Delay (ms) | No | 5000 |

## Configuration File

After publishing, you can find the configuration at `config/stripe.php`:

```php
<?php

return [
    // API Keys
    'api_key' => env('STRIPE_API_KEY'),
    'api_secret' => env('STRIPE_API_SECRET'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),

    // Currency Settings
    'currency' => env('STRIPE_CURRENCY', 'usd'),
    
    // Payment Settings
    'payment' => [
        'success_url' => env('STRIPE_SUCCESS_URL', '/payment/success'),
        'cancel_url' => env('STRIPE_CANCEL_URL', '/payment/cancel'),
        'webhook_url' => env('STRIPE_WEBHOOK_URL', '/stripe/webhook'),
    ],

    // Subscription Settings
    'subscription' => [
        'trial_days' => env('STRIPE_TRIAL_DAYS', 14),
        'default_plan' => env('STRIPE_DEFAULT_PLAN', 'monthly'),
    ],

    // Logging
    'log' => [
        'channel' => env('STRIPE_LOG_CHANNEL', 'stack'),
        'level' => env('STRIPE_LOG_LEVEL', 'info'),
    ],

    // Retry Settings
    'retry' => [
        'max_attempts' => env('STRIPE_RETRY_ATTEMPTS', 3),
        'delay' => env('STRIPE_RETRY_DELAY', 5000),
    ],
];