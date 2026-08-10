<?php

namespace Syedmahamudul\LaravelStripe\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Syedmahamudul\LaravelStripe\Providers\StripePaymentServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Load migrations
        $this->loadMigrations();
    }

    /**
     * Get package providers.
     *a
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            StripePaymentServiceProvider::class,
        ];
    }

    /**
     * Get package aliases.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'StripePayment' => \Syedmahamudul\LaravelStripe\Facades\StripePayment::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite in memory
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Setup Stripe config
        $app['config']->set('stripe.api_key', 'pk_test_123456');
        $app['config']->set('stripe.api_secret', 'sk_test_123456');
        $app['config']->set('stripe.webhook_secret', 'whsec_test_123456');
        $app['config']->set('stripe.currency', 'usd');
        $app['config']->set('stripe.payment.success_url', '/payment/success');
        $app['config']->set('stripe.payment.cancel_url', '/payment/cancel');
        $app['config']->set('stripe.metadata', ['platform' => 'laravel']);
    }

    /**
     * Load migrations for testing.
     */
    protected function loadMigrations(): void
    {
        $this->loadMigrationsFrom([
            '--database' => 'testbench',
            '--realpath' => realpath(__DIR__ . '/../database/migrations'),
        ]);
    }

    /**
     * Create a mock Stripe service.
     *
     * @return \Mockery\MockInterface
     */
    protected function mockStripeService()
    {
        $mock = \Mockery::mock('Syedmahamudul\LaravelStripe\Services\StripeService');
        
        $this->app->instance(
            'Syedmahamudul\LaravelStripe\Services\StripeService',
            $mock
        );

        return $mock;
    }

    /**
     * Create a mock payment service.
     *
     * @return \Mockery\MockInterface
     */
    protected function mockPaymentService()
    {
        $mock = \Mockery::mock('Syedmahamudul\LaravelStripe\Services\PaymentService');
        
        $this->app->instance(
            'Syedmahamudul\LaravelStripe\Services\PaymentService',
            $mock
        );

        return $mock;
    }

    /**
     * Create a mock subscription service.
     *
     * @return \Mockery\MockInterface
     */
    protected function mockSubscriptionService()
    {
        $mock = \Mockery::mock('Syedmahamudul\LaravelStripe\Services\SubscriptionService');
        
        $this->app->instance(
            'Syedmahamudul\LaravelStripe\Services\SubscriptionService',
            $mock
        );

        return $mock;
    }

    /**
     * Create a mock webhook service.
     *
     * @return \Mockery\MockInterface
     */
    protected function mockWebhookService()
    {
        $mock = \Mockery::mock('Syedmahamudul\LaravelStripe\Services\WebhookService');
        
        $this->app->instance(
            'Syedmahamudul\LaravelStripe\Services\WebhookService',
            $mock
        );

        return $mock;
    }

    /**
     * Create webhook payload.
     *
     * @param string $eventType
     * @param array $data
     * @return array
     */
    protected function createWebhookPayload(string $eventType, array $data): array
    {
        return [
            'id' => 'evt_' . uniqid(),
            'type' => $eventType,
            'data' => [
                'object' => $data,
            ],
        ];
    }

    /**
     * Create a Stripe payment intent mock.
     *
     * @param string $id
     * @param string $status
     * @return \stdClass
     */
    protected function createPaymentIntentMock(string $id, string $status = 'requires_confirmation')
    {
        return (object) [
            'id' => $id,
            'status' => $status,
            'client_secret' => 'secret_' . $id,
            'amount' => 10000,
            'currency' => 'usd',
            'confirm' => function() {},
        ];
    }

    /**
     * Create a Stripe customer mock.
     *
     * @param string $id
     * @return \stdClass
     */
    protected function createCustomerMock(string $id = 'cus_123456')
    {
        return (object) [
            'id' => $id,
            'email' => 'test@example.com',
            'name' => 'Test User',
        ];
    }

    /**
     * Create a Stripe subscription mock.
     *
     * @param string $id
     * @param string $status
     * @return \stdClass
     */
    protected function createSubscriptionMock(string $id, string $status = 'active')
    {
        return (object) [
            'id' => $id,
            'status' => $status,
            'customer' => 'cus_123456',
            'items' => (object) [
                'data' => [
                    (object) [
                        'price' => (object) [
                            'id' => 'price_123456',
                        ],
                    ],
                ],
            ],
            'trial_end' => time() + 86400 * 14,
            'canceled_at' => null,
        ];
    }

    /**
     * Create a Stripe refund mock.
     *
     * @param string $id
     * @return \stdClass
     */
    protected function createRefundMock(string $id = 're_123456')
    {
        return (object) [
            'id' => $id,
            'status' => 'succeeded',
            'amount' => 5000,
            'currency' => 'usd',
        ];
    }
}