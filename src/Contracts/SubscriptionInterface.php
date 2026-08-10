<?php

namespace Syedmahamudul\LaravelStripe\Contracts;

use Syedmahamudul\LaravelStripe\Models\Subscription;

interface SubscriptionInterface
{
    /**
     * Create a subscription
     *
     * @param array $data Subscription data including price_id, customer info, etc.
     * @return array Returns subscription details including client_secret if needed
     * @throws \Syedmahamudul\LaravelStripe\Exceptions\SubscriptionException
     */
    public function createSubscription(array $data): array;

    /**
     * Cancel a subscription
     *
     * @param string $subscriptionId The Stripe subscription ID
     * @param bool $atPeriodEnd Whether to cancel at period end or immediately
     * @return Subscription The cancelled subscription model
     * @throws \Syedmahamudul\LaravelStripe\Exceptions\SubscriptionException
     */
    public function cancelSubscription(string $subscriptionId, bool $atPeriodEnd = true): Subscription;

    /**
     * Resume a subscription
     *
     * @param string $subscriptionId The Stripe subscription ID
     * @return Subscription The resumed subscription model
     * @throws \Syedmahamudul\LaravelStripe\Exceptions\SubscriptionException
     */
    public function resumeSubscription(string $subscriptionId): Subscription;

    /**
     * Update a subscription
     *
     * @param string $subscriptionId The Stripe subscription ID
     * @param array $data Subscription data to update (price_id, metadata, etc.)
     * @return Subscription The updated subscription model
     * @throws \Syedmahamudul\LaravelStripe\Exceptions\SubscriptionException
     */
    public function updateSubscription(string $subscriptionId, array $data): Subscription;

    /**
     * Get subscription by ID
     *
     * @param string $subscriptionId The Stripe subscription ID
     * @return Subscription|null The subscription model or null if not found
     */
    public function getSubscription(string $subscriptionId): ?Subscription;

    /**
     * Get active subscriptions for user
     *
     * @param int|string $userId The user ID
     * @return \Illuminate\Database\Eloquent\Collection Collection of active subscriptions
     */
    public function getUserActiveSubscriptions($userId): \Illuminate\Database\Eloquent\Collection;

    /**
     * Get all subscriptions for user
     *
     * @param int|string $userId The user ID
     * @return \Illuminate\Database\Eloquent\Collection Collection of all subscriptions
     */
    public function getUserSubscriptions($userId): \Illuminate\Database\Eloquent\Collection;

    /**
     * Check if subscription is active
     *
     * @param Subscription $subscription The subscription model
     * @return bool True if subscription is active
     */
    public function isSubscriptionActive(Subscription $subscription): bool;

    /**
     * Check if subscription is on trial
     *
     * @param Subscription $subscription The subscription model
     * @return bool True if subscription is on trial
     */
    public function isSubscriptionOnTrial(Subscription $subscription): bool;

    /**
     * Check if subscription has ended
     *
     * @param Subscription $subscription The subscription model
     * @return bool True if subscription has ended
     */
    public function hasSubscriptionEnded(Subscription $subscription): bool;

    /**
     * Get subscription status
     *
     * @param string $subscriptionId The Stripe subscription ID
     * @return string The subscription status
     * @throws \Syedmahamudul\LaravelStripe\Exceptions\SubscriptionException
     */
    public function getSubscriptionStatus(string $subscriptionId): string;

    /**
     * Change subscription plan
     *
     * @param string $subscriptionId The Stripe subscription ID
     * @param string $newPriceId The new price ID
     * @param array $metadata Additional metadata
     * @return Subscription The updated subscription model
     * @throws \Syedmahamudul\LaravelStripe\Exceptions\SubscriptionException
     */
    public function changePlan(string $subscriptionId, string $newPriceId, array $metadata = []): Subscription;

    /**
     * Pause a subscription
     *
     * @param string $subscriptionId The Stripe subscription ID
     * @param array $settings Pause settings (pause_behavior, etc.)
     * @return Subscription The paused subscription model
     * @throws \Syedmahamudul\LaravelStripe\Exceptions\SubscriptionException
     */
    public function pauseSubscription(string $subscriptionId, array $settings = []): Subscription;

    /**
     * Resume a paused subscription
     *
     * @param string $subscriptionId The Stripe subscription ID
     * @return Subscription The resumed subscription model
     * @throws \Syedmahamudul\LaravelStripe\Exceptions\SubscriptionException
     */
    public function resumePausedSubscription(string $subscriptionId): Subscription;

    /**
     * Create a checkout session for subscription
     *
     * @param array $data Checkout data including price_id, customer info, etc.
     * @return array Returns checkout URL and session ID
     * @throws \Syedmahamudul\LaravelStripe\Exceptions\SubscriptionException
     */
    public function createSubscriptionCheckout(array $data): array;

    /**
     * Get upcoming invoice for subscription
     *
     * @param string $subscriptionId The Stripe subscription ID
     * @return object|null The upcoming invoice object or null if none
     * @throws \Syedmahamudul\LaravelStripe\Exceptions\SubscriptionException
     */
    public function getUpcomingInvoice(string $subscriptionId): ?object;

    /**
     * Get subscription invoices
     *
     * @param string $subscriptionId The Stripe subscription ID
     * @param int $limit Number of invoices to retrieve
     * @return array Collection of invoices
     * @throws \Syedmahamudul\LaravelStripe\Exceptions\SubscriptionException
     */
    public function getSubscriptionInvoices(string $subscriptionId, int $limit = 10): array;

    /**
     * Update subscription quantity
     *
     * @param string $subscriptionId The Stripe subscription ID
     * @param int $quantity New quantity
     * @return Subscription The updated subscription model
     * @throws \Syedmahamudul\LaravelStripe\Exceptions\SubscriptionException
     */
    public function updateSubscriptionQuantity(string $subscriptionId, int $quantity): Subscription;

    /**
     * Apply coupon to subscription
     *
     * @param string $subscriptionId The Stripe subscription ID
     * @param string $couponId The coupon ID
     * @return Subscription The updated subscription model
     * @throws \Syedmahamudul\LaravelStripe\Exceptions\SubscriptionException
     */
    public function applyCoupon(string $subscriptionId, string $couponId): Subscription;

    /**
     * Remove coupon from subscription
     *
     * @param string $subscriptionId The Stripe subscription ID
     * @return Subscription The updated subscription model
     * @throws \Syedmahamudul\LaravelStripe\Exceptions\SubscriptionException
     */
    public function removeCoupon(string $subscriptionId): Subscription;

    /**
     * List all subscriptions with pagination
     *
     * @param int $perPage Number of items per page
     * @param int $page Page number
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function listSubscriptions(int $perPage = 15, int $page = 1): \Illuminate\Contracts\Pagination\LengthAwarePaginator;

    /**
     * Get subscription statistics
     *
     * @param array $filters Optional filters (date range, status, etc.)
     * @return array Subscription statistics
     */
    public function getSubscriptionStatistics(array $filters = []): array;

    /**
     * Get subscription revenue
     *
     * @param array $filters Optional filters (date range, etc.)
     * @return float Total revenue from subscriptions
     */
    public function getSubscriptionRevenue(array $filters = []): float;

    /**
     * Export subscriptions to CSV
     *
     * @param array $filters Optional filters
     * @return string CSV content
     */
    public function exportSubscriptions(array $filters = []): string;

    /**
     * Sync subscription status with Stripe
     *
     * @param string $subscriptionId The Stripe subscription ID
     * @return Subscription The updated subscription model
     * @throws \Syedmahamudul\LaravelStripe\Exceptions\SubscriptionException
     */
    public function syncSubscriptionStatus(string $subscriptionId): Subscription;
}