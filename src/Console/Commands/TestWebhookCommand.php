<?php

namespace Syedmahamudul\LaravelStripe\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class TestWebhookCommand extends Command
{
    protected $signature = 'stripe:test-webhook';
    protected $description = 'Test Stripe webhook configuration';

    public function handle()
    {
        $this->info('Testing Stripe Webhook Configuration...');

        $webhookUrl = Config::get('stripe.payment.webhook_url', '/stripe/webhook');
        $webhookSecret = Config::get('stripe.webhook_secret');

        $this->line('Webhook URL: ' . $webhookUrl);
        $this->line('Webhook Secret: ' . ($webhookSecret ? 'Set' : 'Not Set'));

        if (!$webhookSecret) {
            $this->error('Webhook secret is not configured!');
            $this->info('Please set STRIPE_WEBHOOK_SECRET in your .env file');
        } else {
            $this->info('Webhook configuration looks good!');
        }

        $this->info('To test webhooks, use Stripe CLI:');
        $this->line('stripe listen --forward-to ' . $this->getFullUrl($webhookUrl));
    }

    /**
     * Get full URL for webhook
     */
    protected function getFullUrl(string $path): string
    {
        if (function_exists('url')) {
            return url($path);
        }
        return $path;
    }
}