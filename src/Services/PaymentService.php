<?php

namespace Syedmahamudul\LaravelStripe\Services;

use Syedmahamudul\LaravelStripe\Models\Payment;
use Syedmahamudul\LaravelStripe\Exceptions\PaymentFailedException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    protected $stripe;
    protected $config;

    public function __construct(StripeService $stripe, array $config)
    {
        $this->stripe = $stripe;
        $this->config = $config;
    }

    /**
     * Process a one-time payment
     */
    public function processPayment(array $data): array
    {
        try {
            DB::beginTransaction();

            $this->validatePaymentData($data);
            $customer = $this->getOrCreateCustomer($data);

            $paymentIntentData = [
                'amount' => $this->convertToCents($data['amount']),
                'currency' => $data['currency'] ?? $this->config['currency'],
                'payment_method_types' => ['card'],
                'customer' => $customer->id,
                'metadata' => array_merge(
                    $this->config['metadata'] ?? [],
                    $data['metadata'] ?? []
                ),
                'confirm' => false,
            ];

            if (isset($data['save_payment_method']) && $data['save_payment_method'] === true) {
                $paymentIntentData['setup_future_usage'] = 'off_session';
            }

            $paymentIntent = $this->stripe->createPaymentIntent($paymentIntentData);

            $payment = Payment::create([
                'user_id' => $data['user_id'] ?? null,
                'payment_intent_id' => $paymentIntent->id,
                'customer_id' => $customer->id,
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? $this->config['currency'],
                'status' => 'pending',
                'metadata' => $data['metadata'] ?? [],
            ]);

            DB::commit();

            return [
                'payment' => $payment,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'customer_id' => $customer->id,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment processing failed: ' . $e->getMessage());
            throw new PaymentFailedException('Payment processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Confirm a payment
     */
    public function confirmPayment(string $paymentIntentId, array $data = []): Payment
    {
        try {
            $paymentIntent = $this->stripe->retrievePaymentIntent($paymentIntentId);
            
            if (in_array($paymentIntent->status, ['requires_confirmation', 'requires_payment_method'])) {
                $confirmData = [];
                if (isset($data['payment_method_id'])) {
                    $confirmData['payment_method'] = $data['payment_method_id'];
                }
                if (isset($data['return_url'])) {
                    $confirmData['return_url'] = $data['return_url'];
                }
                $paymentIntent->confirm($confirmData);
            }

            $payment = Payment::where('payment_intent_id', $paymentIntentId)->first();
            if ($payment) {
                $payment->update([
                    'status' => $this->mapStripeStatus($paymentIntent->status),
                    'stripe_data' => $paymentIntent->toArray(),
                    'paid_at' => $paymentIntent->status === 'succeeded' ? now() : null,
                ]);
            }

            return $payment;
        } catch (\Exception $e) {
            Log::error('Payment confirmation failed: ' . $e->getMessage());
            throw new PaymentFailedException('Payment confirmation failed: ' . $e->getMessage());
        }
    }

    /**
     * Refund a payment
     */
    public function refundPayment(string $paymentIntentId, $amount = null, array $metadata = []): Payment
    {
        try {
            $refundData = [
                'payment_intent' => $paymentIntentId,
                'metadata' => $metadata,
            ];

            if ($amount !== null && $amount > 0) {
                $refundData['amount'] = $this->convertToCents($amount);
            }

            $refund = $this->stripe->createRefund($refundData);

            $payment = Payment::where('payment_intent_id', $paymentIntentId)->first();
            if ($payment) {
                $payment->update([
                    'status' => 'refunded',
                    'refund_id' => $refund->id,
                    'refund_amount' => $amount ?? $payment->amount,
                    'refunded_at' => now(),
                    'stripe_data' => $refund->toArray(),
                ]);
            }

            return $payment;
        } catch (\Exception $e) {
            Log::error('Refund failed: ' . $e->getMessage());
            throw new PaymentFailedException('Refund failed: ' . $e->getMessage());
        }
    }

    /**
     * Get payment by intent ID
     */
    public function getPayment(string $paymentIntentId): ?Payment
    {
        return Payment::where('payment_intent_id', $paymentIntentId)->first();
    }

    /**
     * Get payments by user
     */
    public function getUserPayments($userId): \Illuminate\Database\Eloquent\Collection
    {
        return Payment::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Check if payment is successful
     */
    public function isPaymentSuccessful($payment): bool
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
    public function isPaymentRefunded($payment): bool
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
     * Validate payment data
     */
    protected function validatePaymentData(array $data): void
    {
        if (!isset($data['amount']) || $data['amount'] <= 0) {
            throw new PaymentFailedException('Invalid amount');
        }

        if (!isset($data['email']) && !isset($data['user_id'])) {
            throw new PaymentFailedException('Email or user_id is required');
        }
    }

    /**
     * Get or create Stripe customer
     */
    protected function getOrCreateCustomer(array $data)
    {
        if (isset($data['customer_id'])) {
            return $this->stripe->retrieveCustomer($data['customer_id']);
        }

        $customerData = [
            'email' => $data['email'] ?? null,
            'name' => $data['name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'metadata' => $data['metadata'] ?? [],
        ];

        $customerData = array_filter($customerData, function ($value) {
            return $value !== null;
        });

        return $this->stripe->createCustomer($customerData);
    }

    /**
     * Convert amount to cents
     */
    protected function convertToCents($amount): int
    {
        return (int) round((float) $amount * 100);
    }

    /**
     * Map Stripe status to local status
     */
    protected function mapStripeStatus(string $status): string
    {
        $map = [
            'succeeded' => 'completed',
            'requires_payment_method' => 'pending',
            'requires_confirmation' => 'pending',
            'canceled' => 'cancelled',
            'processing' => 'processing',
            'requires_action' => 'requires_action',
        ];

        return $map[$status] ?? $status;
    }
}