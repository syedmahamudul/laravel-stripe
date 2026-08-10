<?php

namespace Syedmahamudul\LaravelStripe\Tests\Feature;

use Syedmahamudul\LaravelStripe\Tests\TestCase;
use Syedmahamudul\LaravelStripe\Facades\StripePayment;
use Syedmahamudul\LaravelStripe\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Load helpers if not already loaded
        if (!function_exists('process_payment')) {
            require_once __DIR__ . '/../../src/helpers.php';
        }
    }

    /** @test */
    public function it_can_process_payment_using_helper()
    {
        $paymentData = [
            'amount' => 100.00,
            'email' => 'test@example.com',
            'name' => 'Test User',
            'user_id' => 1,
            'metadata' => ['order_id' => 'ORD-123'],
        ];

        // Mock the Stripe service
        $this->mockStripeService();

        $payment = process_payment($paymentData);

        $this->assertArrayHasKey('payment', $payment);
        $this->assertArrayHasKey('client_secret', $payment);
        $this->assertArrayHasKey('payment_intent_id', $payment);
        $this->assertEquals('pi_123456', $payment['payment_intent_id']);
        $this->assertDatabaseHas('stripe_payments', [
            'payment_intent_id' => 'pi_123456',
            'amount' => 100.00,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function it_can_process_payment_using_facade()
    {
        $paymentData = [
            'amount' => 100.00,
            'email' => 'test@example.com',
            'name' => 'Test User',
            'user_id' => 1,
        ];

        $this->mockStripeService();

        $payment = StripePayment::processPayment($paymentData);

        $this->assertArrayHasKey('payment', $payment);
        $this->assertArrayHasKey('client_secret', $payment);
        $this->assertEquals('pi_123456', $payment['payment_intent_id']);
    }

    /** @test */
    public function it_can_confirm_payment()
    {
        $paymentIntentId = 'pi_123456';

        // Create a pending payment
        $payment = Payment::create([
            'payment_intent_id' => $paymentIntentId,
            'amount' => 100.00,
            'currency' => 'usd',
            'status' => 'pending',
            'customer_id' => 'cus_123456',
        ]);

        $this->mockStripeServiceForConfirmation();

        $confirmedPayment = confirm_payment($paymentIntentId);

        $this->assertEquals('completed', $confirmedPayment->status);
        $this->assertDatabaseHas('stripe_payments', [
            'payment_intent_id' => $paymentIntentId,
            'status' => 'completed',
        ]);
    }

    /** @test */
    public function it_can_create_checkout_session()
    {
        $checkoutData = [
            'items' => [
                [
                    'name' => 'Product 1',
                    'amount' => 50.00,
                    'quantity' => 2,
                ],
            ],
            'email' => 'test@example.com',
        ];

        $this->mockStripeServiceForCheckout();

        $result = create_checkout($checkoutData);

        $this->assertArrayHasKey('checkout_url', $result);
        $this->assertArrayHasKey('session_id', $result);
        $this->assertEquals('cs_123456', $result['session_id']);
        $this->assertStringContainsString('stripe.com', $result['checkout_url']);
    }

    /** @test */
    public function it_can_refund_payment()
    {
        $paymentIntentId = 'pi_123456';

        // Create a completed payment
        $payment = Payment::create([
            'payment_intent_id' => $paymentIntentId,
            'amount' => 100.00,
            'currency' => 'usd',
            'status' => 'completed',
            'customer_id' => 'cus_123456',
            'paid_at' => now(),
        ]);

        $this->mockStripeServiceForRefund();

        $refund = refund_payment($paymentIntentId, 50.00);

        $this->assertEquals('refunded', $refund->status);
        $this->assertEquals(50.00, $refund->refund_amount);
        $this->assertDatabaseHas('stripe_payments', [
            'payment_intent_id' => $paymentIntentId,
            'status' => 'refunded',
            'refund_amount' => 50.00,
        ]);
    }

    /** @test */
    public function it_can_check_payment_successful()
    {
        $paymentIntentId = 'pi_123456';

        $payment = Payment::create([
            'payment_intent_id' => $paymentIntentId,
            'amount' => 100.00,
            'currency' => 'usd',
            'status' => 'completed',
            'customer_id' => 'cus_123456',
            'paid_at' => now(),
        ]);

        $isSuccessful = is_payment_successful($payment);

        $this->assertTrue($isSuccessful);
    }

    /** @test */
    public function it_can_check_payment_refunded()
    {
        $paymentIntentId = 'pi_123456';

        $payment = Payment::create([
            'payment_intent_id' => $paymentIntentId,
            'amount' => 100.00,
            'currency' => 'usd',
            'status' => 'refunded',
            'customer_id' => 'cus_123456',
            'refunded_at' => now(),
        ]);

        $isRefunded = is_payment_refunded($payment);

        $this->assertTrue($isRefunded);
    }

    /** @test */
    public function it_can_get_payment_by_intent_id()
    {
        $paymentIntentId = 'pi_123456';

        Payment::create([
            'payment_intent_id' => $paymentIntentId,
            'amount' => 100.00,
            'currency' => 'usd',
            'status' => 'completed',
            'customer_id' => 'cus_123456',
        ]);

        $payment = get_payment($paymentIntentId);

        $this->assertNotNull($payment);
        $this->assertEquals($paymentIntentId, $payment->payment_intent_id);
    }

    /** @test */
    public function it_can_get_user_payments()
    {
        $userId = 1;

        Payment::create([
            'user_id' => $userId,
            'payment_intent_id' => 'pi_123456',
            'amount' => 100.00,
            'currency' => 'usd',
            'status' => 'completed',
            'customer_id' => 'cus_123456',
        ]);

        $payments = get_user_payments($userId);

        $this->assertCount(1, $payments);
        $this->assertEquals($userId, $payments->first()->user_id);
    }

    // Mock helper methods
    protected function mockStripeService()
    {
        // Create a mock for the StripeService
        $mock = Mockery::mock('alias:Syedmahamudul\LaravelStripe\Services\StripeService');
        
        $mock->shouldReceive('createCustomer')
            ->andReturn((object) ['id' => 'cus_123456']);
        
        $mock->shouldReceive('createPaymentIntent')
            ->andReturn((object) [
                'id' => 'pi_123456',
                'client_secret' => 'secret_123',
                'status' => 'requires_confirmation',
            ]);
        
        // Bind the mock to the container
        $this->app->instance('Syedmahamudul\LaravelStripe\Services\StripeService', $mock);
    }

    protected function mockStripeServiceForConfirmation()
    {
        $mock = Mockery::mock('alias:Syedmahamudul\LaravelStripe\Services\StripeService');
        
        $mock->shouldReceive('retrievePaymentIntent')
            ->andReturn((object) [
                'id' => 'pi_123456',
                'status' => 'succeeded',
            ]);
        
        $this->app->instance('Syedmahamudul\LaravelStripe\Services\StripeService', $mock);
    }

    protected function mockStripeServiceForCheckout()
    {
        $mock = Mockery::mock('alias:Syedmahamudul\LaravelStripe\Services\StripeService');
        
        $mock->shouldReceive('createCheckoutSession')
            ->andReturn((object) [
                'id' => 'cs_123456',
                'url' => 'https://checkout.stripe.com/123',
            ]);
        
        $this->app->instance('Syedmahamudul\LaravelStripe\Services\StripeService', $mock);
    }

    protected function mockStripeServiceForRefund()
    {
        $mock = Mockery::mock('alias:Syedmahamudul\LaravelStripe\Services\StripeService');
        
        $mock->shouldReceive('createRefund')
            ->andReturn((object) [
                'id' => 're_123456',
                'status' => 'succeeded',
            ]);
        
        $this->app->instance('Syedmahamudul\LaravelStripe\Services\StripeService', $mock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}