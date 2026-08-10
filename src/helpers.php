<?php

/**
 * STRIPE PAYMENT HELPER FUNCTIONS
 * These functions are automatically loaded by Composer
 * Users can use them directly in their controllers
 */

// ==================== PAYMENT HELPERS ====================

if (!function_exists('process_payment')) {
    /**
     * Process a one-time payment
     *
     * @param array $data Payment data
     * @return array
     */
    function process_payment(array $data)
    {
        return app(\Syedmahamudul\LaravelStripe\Services\PaymentService::class)
            ->processPayment($data);
    }
}

if (!function_exists('confirm_payment')) {
    /**
     * Confirm a payment
     *
     * @param string $paymentIntentId
     * @param array $data
     * @return \Syedmahamudul\LaravelStripe\Models\Payment
     */
    function confirm_payment($paymentIntentId, array $data = [])
    {
        return app(\Syedmahamudul\LaravelStripe\Services\PaymentService::class)
            ->confirmPayment($paymentIntentId, $data);
    }
}

if (!function_exists('refund_payment')) {
    /**
     * Refund a payment
     *
     * @param string $paymentIntentId
     * @param float|null $amount
     * @param array $metadata
     * @return \Syedmahamudul\LaravelStripe\Models\Payment
     */
    function refund_payment($paymentIntentId, $amount = null, array $metadata = [])
    {
        return app(\Syedmahamudul\LaravelStripe\Services\PaymentService::class)
            ->refundPayment($paymentIntentId, $amount, $metadata);
    }
}

if (!function_exists('is_payment_successful')) {
    /**
     * Check if payment is successful
     *
     * @param mixed $payment
     * @return bool
     */
    function is_payment_successful($payment)
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
}

if (!function_exists('is_payment_refunded')) {
    /**
     * Check if payment is refunded
     *
     * @param mixed $payment
     * @return bool
     */
    function is_payment_refunded($payment)
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
}

if (!function_exists('get_payment')) {
    /**
     * Get payment by intent ID
     *
     * @param string $paymentIntentId
     * @return \Syedmahamudul\LaravelStripe\Models\Payment|null
     */
    function get_payment($paymentIntentId)
    {
        return app(\Syedmahamudul\LaravelStripe\Services\PaymentService::class)
            ->getPayment($paymentIntentId);
    }
}

if (!function_exists('get_user_payments')) {
    /**
     * Get payments by user
     *
     * @param mixed $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function get_user_payments($userId)
    {
        return app(\Syedmahamudul\LaravelStripe\Services\PaymentService::class)
            ->getUserPayments($userId);
    }
}

// ==================== SUBSCRIPTION HELPERS ====================

if (!function_exists('create_subscription')) {
    /**
     * Create a subscription
     *
     * @param array $data {
     *     required: price_id, email
     *     optional: user_id, name, trial_days, metadata
     * }
     * @return array
     */
    function create_subscription(array $data)
    {
        return app(\Syedmahamudul\LaravelStripe\Services\SubscriptionService::class)
            ->createSubscription($data);
    }
}

if (!function_exists('cancel_subscription')) {
    /**
     * Cancel a subscription
     *
     * @param string $subscriptionId
     * @param bool $atPeriodEnd (true: end of billing period, false: immediately)
     * @return \Syedmahamudul\LaravelStripe\Models\Subscription
     */
    function cancel_subscription($subscriptionId, $atPeriodEnd = true)
    {
        return app(\Syedmahamudul\LaravelStripe\Services\SubscriptionService::class)
            ->cancelSubscription($subscriptionId, $atPeriodEnd);
    }
}

if (!function_exists('resume_subscription')) {
    /**
     * Resume a subscription
     *
     * @param string $subscriptionId
     * @return \Syedmahamudul\LaravelStripe\Models\Subscription
     */
    function resume_subscription($subscriptionId)
    {
        return app(\Syedmahamudul\LaravelStripe\Services\SubscriptionService::class)
            ->resumeSubscription($subscriptionId);
    }
}

if (!function_exists('update_subscription')) {
    /**
     * Update a subscription
     *
     * @param string $subscriptionId
     * @param array $data {
     *     optional: price_id, metadata, quantity
     * }
     * @return \Syedmahamudul\LaravelStripe\Models\Subscription
     */
    function update_subscription($subscriptionId, array $data)
    {
        return app(\Syedmahamudul\LaravelStripe\Services\SubscriptionService::class)
            ->updateSubscription($subscriptionId, $data);
    }
}

if (!function_exists('get_subscription')) {
    /**
     * Get subscription by ID
     *
     * @param string $subscriptionId
     * @return \Syedmahamudul\LaravelStripe\Models\Subscription|null
     */
    function get_subscription($subscriptionId)
    {
        return app(\Syedmahamudul\LaravelStripe\Services\SubscriptionService::class)
            ->getSubscription($subscriptionId);
    }
}

if (!function_exists('get_user_subscriptions')) {
    /**
     * Get active subscriptions for user
     *
     * @param mixed $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function get_user_subscriptions($userId)
    {
        return app(\Syedmahamudul\LaravelStripe\Services\SubscriptionService::class)
            ->getUserActiveSubscriptions($userId);
    }
}

if (!function_exists('is_subscription_active')) {
    /**
     * Check if subscription is active
     *
     * @param \Syedmahamudul\LaravelStripe\Models\Subscription $subscription
     * @return bool
     */
    function is_subscription_active($subscription)
    {
        if (!$subscription) {
            return false;
        }
        return $subscription->isActive();
    }
}

if (!function_exists('is_subscription_on_trial')) {
    /**
     * Check if subscription is on trial
     *
     * @param \Syedmahamudul\LaravelStripe\Models\Subscription $subscription
     * @return bool
     */
    function is_subscription_on_trial($subscription)
    {
        if (!$subscription) {
            return false;
        }
        return $subscription->onTrial();
    }
}

if (!function_exists('has_subscription_ended')) {
    /**
     * Check if subscription has ended
     *
     * @param \Syedmahamudul\LaravelStripe\Models\Subscription $subscription
     * @return bool
     */
    function has_subscription_ended($subscription)
    {
        if (!$subscription) {
            return true;
        }
        return $subscription->hasEnded();
    }
}

if (!function_exists('change_plan')) {
    /**
     * Change subscription plan
     *
     * @param string $subscriptionId
     * @param string $newPriceId
     * @param array $metadata
     * @return \Syedmahamudul\LaravelStripe\Models\Subscription
     */
    function change_plan($subscriptionId, $newPriceId, array $metadata = [])
    {
        return app(\Syedmahamudul\LaravelStripe\Services\SubscriptionService::class)
            ->changePlan($subscriptionId, $newPriceId, $metadata);
    }
}

// ==================== GENERAL HELPERS ====================

if (!function_exists('format_currency')) {
    /**
     * Format amount to currency
     *
     * @param float|int $amount
     * @param string $currency
     * @return string
     */
    function format_currency($amount, $currency = 'USD')
    {
        return number_format((float) $amount, 2) . ' ' . strtoupper($currency);
    }
}

if (!function_exists('convert_to_cents')) {
    /**
     * Convert amount to cents
     *
     * @param float|int $amount
     * @return int
     */
    function convert_to_cents($amount)
    {
        return (int) round((float) $amount * 100);
    }
}

if (!function_exists('convert_from_cents')) {
    /**
     * Convert cents to amount
     *
     * @param int $cents
     * @return float
     */
    function convert_from_cents($cents)
    {
        return (float) $cents / 100;
    }
}

if (!function_exists('get_stripe_config')) {
    /**
     * Get Stripe configuration value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function get_stripe_config($key, $default = null)
    {
        return config('stripe.' . $key, $default);
    }
}

if (!function_exists('get_stripe_currency')) {
    /**
     * Get Stripe currency
     *
     * @return string
     */
    function get_stripe_currency()
    {
        return config('stripe.currency', 'usd');
    }
}

if (!function_exists('stripe_payment')) {
    /**
     * Get the Stripe Payment instance
     *
     * @return \Syedmahamudul\LaravelStripe\Services\StripeService
     */
    function stripe_payment()
    {
        return app('laravel-stripe');
    }
}

if (!function_exists('stripe_client')) {
    /**
     * Get Stripe client instance
     *
     * @return \Stripe\StripeClient
     */
    function stripe_client()
    {
        return app(\Syedmahamudul\LaravelStripe\Services\StripeService::class)
            ->getClient();
    }
}