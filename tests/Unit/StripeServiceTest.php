<?php

namespace Syedmahamudul\LaravelStripe\Tests\Unit;

use Syedmahamudul\LaravelStripe\Tests\TestCase;
use Syedmahamudul\LaravelStripe\Services\StripeService;
use Syedmahamudul\LaravelStripe\Exceptions\StripePaymentException;
use Mockery;
use Stripe\StripeClient;

class StripeServiceTest extends TestCase
{
    protected $stripeService;
    protected $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = [
            'api_key' => 'pk_test_123456',
            'api_secret' => 'sk_test_123456',
            'webhook_secret' => 'whsec_123456',
            'currency' => 'usd',
        ];

        $this->stripeService = new StripeService($this->config);
    }

    /** @test */
    public function it_can_create_payment_intent()
    {
        $params = [
            'amount' => 1000,
            'currency' => 'usd',
            'payment_method_types' => ['card'],
        ];

        // Mock the Stripe client
        $mockClient = Mockery::mock(StripeClient::class);
        $mockClient->shouldReceive('paymentIntents->create')
            ->once()
            ->with($params)
            ->andReturn((object) ['id' => 'pi_123456', 'client_secret' => 'secret_123']);

        // Replace the client with mock
        $this->stripeService->setClient($mockClient);

        $result = $this->stripeService->createPaymentIntent($params);

        $this->assertIsObject($result);
        $this->assertEquals('pi_123456', $result->id);
    }

    /** @test */
    public function it_can_retrieve_payment_intent()
    {
        $paymentIntentId = 'pi_123456';

        $mockClient = Mockery::mock(StripeClient::class);
        $mockClient->shouldReceive('paymentIntents->retrieve')
            ->once()
            ->with($paymentIntentId)
            ->andReturn((object) ['id' => $paymentIntentId, 'status' => 'succeeded']);

        $this->stripeService->setClient($mockClient);

        $result = $this->stripeService->retrievePaymentIntent($paymentIntentId);

        $this->assertIsObject($result);
        $this->assertEquals($paymentIntentId, $result->id);
    }

    /** @test */
    public function it_can_create_customer()
    {
        $params = [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ];

        $mockClient = Mockery::mock(StripeClient::class);
        $mockClient->shouldReceive('customers->create')
            ->once()
            ->with($params)
            ->andReturn((object) ['id' => 'cus_123456', 'email' => 'test@example.com']);

        $this->stripeService->setClient($mockClient);

        $result = $this->stripeService->createCustomer($params);

        $this->assertIsObject($result);
        $this->assertEquals('cus_123456', $result->id);
        $this->assertEquals('test@example.com', $result->email);
    }

    /** @test */
    public function it_can_create_subscription()
    {
        $params = [
            'customer' => 'cus_123456',
            'items' => [['price' => 'price_123456']],
        ];

        $mockClient = Mockery::mock(StripeClient::class);
        $mockClient->shouldReceive('subscriptions->create')
            ->once()
            ->with($params)
            ->andReturn((object) ['id' => 'sub_123456', 'status' => 'active']);

        $this->stripeService->setClient($mockClient);

        $result = $this->stripeService->createSubscription($params);

        $this->assertIsObject($result);
        $this->assertEquals('sub_123456', $result->id);
        $this->assertEquals('active', $result->status);
    }

    /** @test */
    public function it_can_create_checkout_session()
    {
        $params = [
            'success_url' => 'https://example.com/success',
            'cancel_url' => 'https://example.com/cancel',
            'mode' => 'payment',
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => ['name' => 'Test Product'],
                        'unit_amount' => 1000,
                    ],
                    'quantity' => 1,
                ]
            ],
        ];

        $mockClient = Mockery::mock(StripeClient::class);
        $mockClient->shouldReceive('checkout->sessions->create')
            ->once()
            ->with($params)
            ->andReturn((object) ['id' => 'cs_123456', 'url' => 'https://checkout.stripe.com/123']);

        $this->stripeService->setClient($mockClient);

        $result = $this->stripeService->createCheckoutSession($params);

        $this->assertIsObject($result);
        $this->assertEquals('cs_123456', $result->id);
        $this->assertStringContainsString('stripe.com', $result->url);
    }

    /** @test */
    public function it_can_create_refund()
    {
        $params = [
            'payment_intent' => 'pi_123456',
            'amount' => 500,
        ];

        $mockClient = Mockery::mock(StripeClient::class);
        $mockClient->shouldReceive('refunds->create')
            ->once()
            ->with($params)
            ->andReturn((object) ['id' => 're_123456', 'status' => 'succeeded']);

        $this->stripeService->setClient($mockClient);

        $result = $this->stripeService->createRefund($params);

        $this->assertIsObject($result);
        $this->assertEquals('re_123456', $result->id);
    }

    /** @test */
    public function it_throws_exception_on_payment_intent_failure()
    {
        $this->expectException(StripePaymentException::class);

        $params = [
            'amount' => 1000,
            'currency' => 'usd',
        ];

        // Mock the Stripe client to throw exception
        $mockClient = Mockery::mock(StripeClient::class);
        $mockClient->shouldReceive('paymentIntents->create')
            ->once()
            ->andThrow(new \Exception('API Error'));

        $this->stripeService->setClient($mockClient);

        $this->stripeService->createPaymentIntent($params);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}