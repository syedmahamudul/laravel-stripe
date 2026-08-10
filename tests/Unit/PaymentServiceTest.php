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

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_create_payment_intent()
    {
        $params = [
            'amount' => 1000,
            'currency' => 'usd',
            'payment_method_types' => ['card'],
        ];

        // Create a mock for the Stripe client
        $mockPaymentIntents = Mockery::mock();
        $mockPaymentIntents->shouldReceive('create')
            ->once()
            ->with($params)
            ->andReturn((object) ['id' => 'pi_123456', 'client_secret' => 'secret_123']);

        $mockClient = Mockery::mock(StripeClient::class);
        $mockClient->paymentIntents = $mockPaymentIntents;

        // Inject the mock client using reflection
        $reflection = new \ReflectionClass($this->stripeService);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($this->stripeService, $mockClient);

        $result = $this->stripeService->createPaymentIntent($params);

        $this->assertIsObject($result);
        $this->assertEquals('pi_123456', $result->id);
        $this->assertEquals('secret_123', $result->client_secret);
    }

    /** @test */
    public function it_can_retrieve_payment_intent()
    {
        $paymentIntentId = 'pi_123456';

        $mockPaymentIntents = Mockery::mock();
        $mockPaymentIntents->shouldReceive('retrieve')
            ->once()
            ->with($paymentIntentId)
            ->andReturn((object) ['id' => $paymentIntentId, 'status' => 'succeeded']);

        $mockClient = Mockery::mock(StripeClient::class);
        $mockClient->paymentIntents = $mockPaymentIntents;

        $reflection = new \ReflectionClass($this->stripeService);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($this->stripeService, $mockClient);

        $result = $this->stripeService->retrievePaymentIntent($paymentIntentId);

        $this->assertIsObject($result);
        $this->assertEquals($paymentIntentId, $result->id);
        $this->assertEquals('succeeded', $result->status);
    }

    /** @test */
    public function it_can_create_customer()
    {
        $params = [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ];

        $mockCustomers = Mockery::mock();
        $mockCustomers->shouldReceive('create')
            ->once()
            ->with($params)
            ->andReturn((object) ['id' => 'cus_123456', 'email' => 'test@example.com']);

        $mockClient = Mockery::mock(StripeClient::class);
        $mockClient->customers = $mockCustomers;

        $reflection = new \ReflectionClass($this->stripeService);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($this->stripeService, $mockClient);

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

        $mockSubscriptions = Mockery::mock();
        $mockSubscriptions->shouldReceive('create')
            ->once()
            ->with($params)
            ->andReturn((object) ['id' => 'sub_123456', 'status' => 'active']);

        $mockClient = Mockery::mock(StripeClient::class);
        $mockClient->subscriptions = $mockSubscriptions;

        $reflection = new \ReflectionClass($this->stripeService);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($this->stripeService, $mockClient);

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

        $mockCheckout = Mockery::mock();
        $mockCheckout->shouldReceive('sessions->create')
            ->once()
            ->with($params)
            ->andReturn((object) ['id' => 'cs_123456', 'url' => 'https://checkout.stripe.com/123']);

        $mockClient = Mockery::mock(StripeClient::class);
        $mockClient->checkout = $mockCheckout;

        $reflection = new \ReflectionClass($this->stripeService);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($this->stripeService, $mockClient);

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

        $mockRefunds = Mockery::mock();
        $mockRefunds->shouldReceive('create')
            ->once()
            ->with($params)
            ->andReturn((object) ['id' => 're_123456', 'status' => 'succeeded']);

        $mockClient = Mockery::mock(StripeClient::class);
        $mockClient->refunds = $mockRefunds;

        $reflection = new \ReflectionClass($this->stripeService);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($this->stripeService, $mockClient);

        $result = $this->stripeService->createRefund($params);

        $this->assertIsObject($result);
        $this->assertEquals('re_123456', $result->id);
        $this->assertEquals('succeeded', $result->status);
    }

    /** @test */
    public function it_throws_exception_on_payment_intent_failure()
    {
        $this->expectException(StripePaymentException::class);

        $params = [
            'amount' => 1000,
            'currency' => 'usd',
        ];

        $mockPaymentIntents = Mockery::mock();
        $mockPaymentIntents->shouldReceive('create')
            ->once()
            ->with($params)
            ->andThrow(new \Exception('API Error'));

        $mockClient = Mockery::mock(StripeClient::class);
        $mockClient->paymentIntents = $mockPaymentIntents;

        $reflection = new \ReflectionClass($this->stripeService);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($this->stripeService, $mockClient);

        $this->stripeService->createPaymentIntent($params);
    }

    /** @test */
    public function it_can_verify_webhook_signature()
    {
        $payload = '{"id":"evt_123","type":"payment_intent.succeeded"}';
        $signature = 'test_signature';

        // We need to mock the static Webhook class
        $mockWebhook = Mockery::mock('alias:Stripe\Webhook');
        $mockWebhook->shouldReceive('constructEvent')
            ->once()
            ->with($payload, $signature, $this->config['webhook_secret'])
            ->andReturn((object) ['type' => 'payment_intent.succeeded']);

        $result = $this->stripeService->verifyWebhookSignature($payload, $signature);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_returns_false_on_invalid_webhook_signature()
    {
        $payload = '{"id":"evt_123","type":"payment_intent.succeeded"}';
        $signature = 'invalid_signature';

        $mockWebhook = Mockery::mock('alias:Stripe\Webhook');
        $mockWebhook->shouldReceive('constructEvent')
            ->once()
            ->andThrow(new \Exception('Invalid signature'));

        $result = $this->stripeService->verifyWebhookSignature($payload, $signature);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_can_handle_webhook_event()
    {
        $payload = '{"id":"evt_123","type":"payment_intent.succeeded"}';
        $signature = 'test_signature';

        $mockWebhook = Mockery::mock('alias:Stripe\Webhook');
        $mockWebhook->shouldReceive('constructEvent')
            ->once()
            ->with($payload, $signature, $this->config['webhook_secret'])
            ->andReturn((object) ['type' => 'payment_intent.succeeded']);

        $result = $this->stripeService->handleWebhookEvent($payload, $signature);

        $this->assertIsObject($result);
        $this->assertEquals('payment_intent.succeeded', $result->type);
    }

    /** @test */
    public function it_can_get_client()
    {
        $client = $this->stripeService->getClient();
        
        $this->assertInstanceOf(StripeClient::class, $client);
    }
}