<?php

namespace Syedmahamudul\LaravelStripe\Services;

use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;
use Syedmahamudul\LaravelStripe\Exceptions\StripePaymentException;
use Illuminate\Support\Facades\Log;
use Syedmahamudul\LaravelStripe\Exceptions\WebhookException;

class StripeService
{
    protected $client;
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        
        // Version compatibility
        $stripeVersion = $this->getStripeVersion();
        $this->client = new StripeClient([
            'api_key' => $config['api_secret'],
            'stripe_version' => $stripeVersion,
        ]);
    }

    /**
     * Get appropriate Stripe version based on PHP version
     */
    protected function getStripeVersion(): string
    {
        if (version_compare(PHP_VERSION, '8.0', '>=')) {
            return '2023-10-16';
        }
        return '2020-08-27';
    }

    /**
     * Create a payment intent
     */
    public function createPaymentIntent(array $params)
    {
        try {
            return $this->client->paymentIntents->create($params);
        } catch (ApiErrorException $e) {
            Log::error('Stripe Payment Intent Error: ' . $e->getMessage());
            throw new StripePaymentException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Retrieve a payment intent
     */
    public function retrievePaymentIntent($id)
    {
        try {
            return $this->client->paymentIntents->retrieve($id);
        } catch (ApiErrorException $e) {
            Log::error('Stripe Retrieve Payment Intent Error: ' . $e->getMessage());
            throw new StripePaymentException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Create a customer
     */
    public function createCustomer(array $params)
    {
        try {
            return $this->client->customers->create($params);
        } catch (ApiErrorException $e) {
            Log::error('Stripe Create Customer Error: ' . $e->getMessage());
            throw new StripePaymentException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Retrieve a customer
     */
    public function retrieveCustomer($id)
    {
        try {
            return $this->client->customers->retrieve($id);
        } catch (ApiErrorException $e) {
            Log::error('Stripe Retrieve Customer Error: ' . $e->getMessage());
            throw new StripePaymentException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Create a subscription
     */
    public function createSubscription(array $params)
    {
        try {
            return $this->client->subscriptions->create($params);
        } catch (ApiErrorException $e) {
            Log::error('Stripe Create Subscription Error: ' . $e->getMessage());
            throw new StripePaymentException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Retrieve a subscription
     */
    public function retrieveSubscription($id)
    {
        try {
            return $this->client->subscriptions->retrieve($id);
        } catch (ApiErrorException $e) {
            Log::error('Stripe Retrieve Subscription Error: ' . $e->getMessage());
            throw new StripePaymentException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Create a checkout session
     */
    public function createCheckoutSession(array $params)
    {
        try {
            return $this->client->checkout->sessions->create($params);
        } catch (ApiErrorException $e) {
            Log::error('Stripe Checkout Session Error: ' . $e->getMessage());
            throw new StripePaymentException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Create a refund
     */
    public function createRefund(array $params)
    {
        try {
            return $this->client->refunds->create($params);
        } catch (ApiErrorException $e) {
            Log::error('Stripe Refund Error: ' . $e->getMessage());
            throw new StripePaymentException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature($payload, $signature)
    {
        try {
            \Stripe\Webhook::constructEvent(
                $payload,
                $signature,
                $this->config['webhook_secret']
            );
            return true;
        } catch (\Exception $e) {
            Log::error('Webhook signature verification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Handle webhook event
     */
    public function handleWebhookEvent($payload, $signature)
    {
        if (!$this->verifyWebhookSignature($payload, $signature)) {
            throw new WebhookException('Invalid webhook signature');
        }

        try {
            return \Stripe\Webhook::constructEvent(
                $payload,
                $signature,
                $this->config['webhook_secret']
            );
        } catch (\Exception $e) {
            Log::error('Webhook processing error: ' . $e->getMessage());
            throw new WebhookException($e->getMessage());
        }
    }

    /**
     * Get Stripe client instance
     */
    public function getClient()
    {
        return $this->client;
    }
}