<?php

namespace Syedmahamudul\LaravelStripe\Services;

use Syedmahamudul\LaravelStripe\Models\Payment;
use Syedmahamudul\LaravelStripe\Models\Subscription;
use Syedmahamudul\LaravelStripe\Exceptions\WebhookException;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    protected StripeService $stripe;
    protected array $config;

    public function __construct(StripeService $stripe, array $config)
    {
        $this->stripe = $stripe;
        $this->config = $config;
    }

    /**
     * Handle webhook
     */
    public function handleWebhook(string $payload, string $signature): object
    {
        try {
            return $this->stripe->handleWebhookEvent($payload, $signature);
        } catch (\Exception $e) {
            Log::error('Webhook handling failed: ' . $e->getMessage());
            throw new WebhookException($e->getMessage());
        }
    }

    /**
     * Process webhook event
     */
    public function processEvent(object $event): void
    {
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->handlePaymentSucceeded($event->data->object);
                break;

            case 'payment_intent.payment_failed':
                $this->handlePaymentFailed($event->data->object);
                break;

            case 'charge.refunded':
                $this->handleRefundSucceeded($event->data->object);
                break;

            case 'customer.subscription.created':
                $this->handleSubscriptionCreated($event->data->object);
                break;

            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($event->data->object);
                break;

            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($event->data->object);
                break;

            case 'invoice.payment_succeeded':
                $this->handleInvoicePaymentSucceeded($event->data->object);
                break;

            case 'invoice.payment_failed':
                $this->handleInvoicePaymentFailed($event->data->object);
                break;

            default:
                Log::info('Unhandled webhook event: ' . $event->type);
                break;
        }
    }

    /**
     * Handle payment succeeded
     */
    protected function handlePaymentSucceeded(object $paymentIntent): void
    {
        $payment = Payment::where('payment_intent_id', $paymentIntent->id)->first();
        if ($payment) {
            $payment->update([
                'status' => 'completed',
                'paid_at' => now(),
                'stripe_data' => $paymentIntent->toArray(),
            ]);
            
            Log::info('Payment succeeded', ['payment_intent_id' => $paymentIntent->id]);
        }
    }

    /**
     * Handle payment failed
     */
    protected function handlePaymentFailed(object $paymentIntent): void
    {
        $payment = Payment::where('payment_intent_id', $paymentIntent->id)->first();
        if ($payment) {
            $payment->update([
                'status' => 'failed',
                'stripe_data' => $paymentIntent->toArray(),
            ]);
            
            Log::warning('Payment failed', ['payment_intent_id' => $paymentIntent->id]);
        }
    }

    /**
     * Handle refund succeeded
     */
    protected function handleRefundSucceeded(object $charge): void
    {
        $payment = Payment::where('payment_intent_id', $charge->payment_intent)->first();
        if ($payment) {
            $payment->update([
                'status' => 'refunded',
                'stripe_data' => $charge->toArray(),
            ]);
            
            Log::info('Refund succeeded', ['payment_intent_id' => $charge->payment_intent]);
        }
    }

    /**
     * Handle subscription created
     */
    protected function handleSubscriptionCreated(object $subscription): void
    {
        $subscriptionRecord = Subscription::updateOrCreate(
            ['subscription_id' => $subscription->id],
            [
                'customer_id' => $subscription->customer,
                'price_id' => $subscription->items->data[0]->price->id ?? null,
                'status' => $subscription->status,
                'trial_ends_at' => $subscription->trial_end ? date('Y-m-d H:i:s', $subscription->trial_end) : null,
                'stripe_data' => $subscription->toArray(),
            ]
        );
        
        Log::info('Subscription created', ['subscription_id' => $subscription->id]);
    }

    /**
     * Handle subscription updated
     */
    protected function handleSubscriptionUpdated(object $subscription): void
    {
        $subscriptionRecord = Subscription::where('subscription_id', $subscription->id)->first();
        if ($subscriptionRecord) {
            $subscriptionRecord->update([
                'status' => $subscription->status,
                'price_id' => $subscription->items->data[0]->price->id ?? $subscriptionRecord->price_id,
                'trial_ends_at' => $subscription->trial_end ? date('Y-m-d H:i:s', $subscription->trial_end) : null,
                'stripe_data' => $subscription->toArray(),
            ]);
            
            Log::info('Subscription updated', ['subscription_id' => $subscription->id]);
        }
    }

    /**
     * Handle subscription deleted
     */
    protected function handleSubscriptionDeleted(object $subscription): void
    {
        $subscriptionRecord = Subscription::where('subscription_id', $subscription->id)->first();
        if ($subscriptionRecord) {
            $subscriptionRecord->update([
                'status' => 'cancelled',
                'ends_at' => now(),
                'stripe_data' => $subscription->toArray(),
            ]);
            
            Log::info('Subscription deleted', ['subscription_id' => $subscription->id]);
        }
    }

    /**
     * Handle invoice payment succeeded
     */
    protected function handleInvoicePaymentSucceeded(object $invoice): void
    {
        Log::info('Invoice payment succeeded', ['invoice_id' => $invoice->id]);
    }

    /**
     * Handle invoice payment failed
     */
    protected function handleInvoicePaymentFailed(object $invoice): void
    {
        Log::warning('Invoice payment failed', ['invoice_id' => $invoice->id]);
    }
}