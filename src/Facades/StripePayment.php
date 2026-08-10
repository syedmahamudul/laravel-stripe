<?php

namespace Syedmahamudul\LaravelStripe\Facades;

use Illuminate\Support\Facades\Facade;
use Syedmahamudul\LaravelStripe\Services\PaymentService;
use Syedmahamudul\LaravelStripe\Services\SubscriptionService;
use Syedmahamudul\LaravelStripe\Services\WebhookService;
use Syedmahamudul\LaravelStripe\Services\StripeService;
use Syedmahamudul\LaravelStripe\Models\Payment;

/**
 * StripePayment Facade
 * 
 * @method static \Syedmahamudul\LaravelStripe\Services\PaymentService payment()
 * @method static \Syedmahamudul\LaravelStripe\Services\SubscriptionService subscription()
 * @method static \Syedmahamudul\LaravelStripe\Services\WebhookService webhook()
 * @method static \Syedmahamudul\LaravelStripe\Services\StripeService stripe()
 * @method static \Stripe\StripeClient client()
 * 
 * @method static array processPayment(array $data)
 * @method static \Syedmahamudul\LaravelStripe\Models\Payment confirmPayment(string $paymentIntentId, array $data = [])
 * @method static \Syedmahamudul\LaravelStripe\Models\Payment refundPayment(string $paymentIntentId, float|null $amount = null, array $metadata = [])
 * @method static bool isPaymentSuccessful($payment)
 * @method static bool isPaymentRefunded($payment)
 * @method static \Syedmahamudul\LaravelStripe\Models\Payment|null getPayment(string $paymentIntentId)
 * @method static \Illuminate\Database\Eloquent\Collection getUserPayments($userId)
 * @method static string formatCurrency(float $amount, string $currency = 'USD')
 */
class StripePayment extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-stripe';
    }

    /**
     * Get the payment service
     */
    public static function payment()
    {
        return app(PaymentService::class);
    }

    /**
     * Get the subscription service
     */
    public static function subscription()
    {
        return app(SubscriptionService::class);
    }

    /**
     * Get the webhook service
     */
    public static function webhook()
    {
        return app(WebhookService::class);
    }

    /**
     * Get the stripe service
     */
    public static function stripe()
    {
        return app(StripeService::class);
    }

    /**
     * Process a payment
     */
    public static function processPayment(array $data)
    {
        return app(PaymentService::class)->processPayment($data);
    }

    /**
     * Confirm a payment
     */
    public static function confirmPayment($paymentIntentId, array $data = [])
    {
        return app(PaymentService::class)->confirmPayment($paymentIntentId, $data);
    }

    /**
     * Refund a payment
     */
    public static function refundPayment($paymentIntentId, $amount = null, array $metadata = [])
    {
        return app(PaymentService::class)->refundPayment($paymentIntentId, $amount, $metadata);
    }

    /**
     * Check if payment is successful
     */
    public static function isPaymentSuccessful($payment)
    {
        if (!$payment) {
            return false;
        }
        
        if (is_object($payment) && method_exists($payment, 'isSuccessful')) {
            return $payment->isSuccessful();
        }
        
        if (is_array($payment) && isset($payment['status'])) {
            return $payment['status'] === 'completed' || $payment['status'] === 'succeeded';
        }
        
        return false;
    }

    /**
     * Check if payment is refunded
     */
    public static function isPaymentRefunded($payment)
    {
        if (!$payment) {
            return false;
        }
        
        if (is_object($payment) && method_exists($payment, 'isRefunded')) {
            return $payment->isRefunded();
        }
        
        if (is_array($payment) && isset($payment['status'])) {
            return $payment['status'] === 'refunded';
        }
        
        return false;
    }

    /**
     * Get payment by intent ID
     */
    public static function getPayment($paymentIntentId)
    {
        return app(PaymentService::class)->getPayment($paymentIntentId);
    }

    /**
     * Get payments by user
     */
    public static function getUserPayments($userId)
    {
        return app(PaymentService::class)->getUserPayments($userId);
    }

    /**
     * Format currency
     */
    public static function formatCurrency($amount, $currency = 'USD')
    {
        return number_format((float) $amount, 2) . ' ' . strtoupper($currency);
    }

    /**
     * Convert amount to cents
     */
    public static function convertToCents($amount)
    {
        return (int) round((float) $amount * 100);
    }

    /**
     * Convert cents to amount
     */
    public static function convertFromCents($cents)
    {
        return (float) $cents / 100;
    }
}