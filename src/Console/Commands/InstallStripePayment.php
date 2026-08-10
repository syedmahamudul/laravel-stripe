<?php

namespace Syedmahamudul\LaravelStripe\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class InstallStripePayment extends Command
{
    protected $signature = 'stripe:install';
    protected $description = 'Install and configure Stripe Payment package';

    public function handle()
    {
        $this->info('Installing Stripe Payment Package...');

        // Publish config
        $this->call('vendor:publish', [
            '--tag' => 'stripe-config',
            '--force' => true,
        ]);

        // Publish migrations
        $this->call('vendor:publish', [
            '--tag' => 'stripe-migrations',
            '--force' => true,
        ]);

        // Run migrations
        if ($this->confirm('Do you want to run migrations now?', true)) {
            $this->call('migrate');
        }

        // Check if helpers are loaded
        $this->checkHelpers();

        $this->info('✅ Stripe Payment Package installed successfully!');
        $this->newLine();
        $this->info('📝 Please add the following environment variables to your .env file:');
        $this->line('  STRIPE_API_KEY=your_stripe_publishable_key');
        $this->line('  STRIPE_API_SECRET=your_stripe_secret_key');
        $this->line('  STRIPE_WEBHOOK_SECRET=your_webhook_secret');
        $this->line('  STRIPE_CURRENCY=usd');
        $this->newLine();
        $this->info('📚 Available helper functions:');
        $this->line('  process_payment() - Process a payment');
        $this->line('  confirm_payment() - Confirm a payment');
        $this->line('  refund_payment() - Refund a payment');
        $this->line('  is_payment_successful() - Check if payment is successful');
        $this->line('  is_payment_refunded() - Check if payment is refunded');
        $this->line('  get_payment() - Get payment by intent ID');
        $this->line('  format_currency() - Format currency');
        $this->newLine();
        $this->info('📚 Documentation: https://github.com/syedmahamudul/laravel-stripe');
    }

    /**
     * Check if helper functions are loaded
     */
    protected function checkHelpers(): void
    {
        if (function_exists('process_payment')) {
            $this->info('✅ Helper functions loaded successfully');
        } else {
            $this->warn('⚠️ Helper functions not loaded. Running composer dump-autoload...');
            Artisan::call('composer dump-autoload');
            
            if (function_exists('process_payment')) {
                $this->info('✅ Helper functions now loaded');
            } else {
                $this->warn('⚠️ Please run: composer dump-autoload');
            }
        }
    }
}