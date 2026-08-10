<?php

namespace Syedmahamudul\LaravelStripe\Services;

use Syedmahamudul\LaravelStripe\Models\Subscription;
use Syedmahamudul\LaravelStripe\Exceptions\StripePaymentException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    protected StripeService $stripe;
    protected array $config;

    public function __construct(StripeService $stripe, array $config)
    {
        $this->stripe = $stripe;
        $this->config = $config;
    }

    /**
     * Create a subscription
     */
    public function createSubscription(array $data): array
    {
        try {
            DB::beginTransaction();

            $customer = $this->getOrCreateCustomer($data);

            $subscriptionData = [
                'customer' => $customer->id,
                'items' => [
                    ['price' => $data['price_id']],
                ],
                'payment_behavior' => 'default_incomplete',
                'expand' => ['latest_invoice.payment_intent'],
                'metadata' => $data['metadata'] ?? [],
            ];

            if (isset($data['trial_days'])) {
                $subscriptionData['trial_period_days'] = $data['trial_days'];
            } elseif ($this->config['subscription']['trial_days'] > 0) {
                $subscriptionData['trial_period_days'] = $this->config['subscription']['trial_days'];
            }

            $stripeSubscription = $this->stripe->createSubscription($subscriptionData);

            $subscription = Subscription::create([
                'user_id' => $data['user_id'] ?? null,
                'customer_id' => $customer->id,
                'subscription_id' => $stripeSubscription->id,
                'price_id' => $data['price_id'],
                'status' => $stripeSubscription->status,
                'trial_ends_at' => $stripeSubscription->trial_end ? 
                    date('Y-m-d H:i:s', $stripeSubscription->trial_end) : null,
                'ends_at' => $stripeSubscription->canceled_at ? 
                    date('Y-m-d H:i:s', $stripeSubscription->canceled_at) : null,
                'metadata' => $data['metadata'] ?? [],
                'stripe_data' => $stripeSubscription->toArray(),
            ]);

            DB::commit();

            return [
                'subscription' => $subscription,
                'client_secret' => $stripeSubscription->latest_invoice->payment_intent->client_secret ?? null,
                'subscription_id' => $stripeSubscription->id,
                'status' => $stripeSubscription->status,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Subscription creation failed: ' . $e->getMessage());
            throw new StripePaymentException('Subscription creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a subscription
     */
    public function cancelSubscription(string $subscriptionId, bool $atPeriodEnd = true): Subscription
    {
        try {
            $stripeSubscription = $this->stripe->getClient()->subscriptions->cancel($subscriptionId, [
                'at_period_end' => $atPeriodEnd,
            ]);

            $subscription = Subscription::where('subscription_id', $subscriptionId)->first();
            if ($subscription) {
                $subscription->update([
                    'status' => $atPeriodEnd ? 'pending_cancellation' : 'cancelled',
                    'ends_at' => $stripeSubscription->canceled_at ? 
                        date('Y-m-d H:i:s', $stripeSubscription->canceled_at) : null,
                    'stripe_data' => $stripeSubscription->toArray(),
                ]);
            }

            return $subscription;
        } catch (\Exception $e) {
            Log::error('Subscription cancellation failed: ' . $e->getMessage());
            throw new StripePaymentException('Subscription cancellation failed: ' . $e->getMessage());
        }
    }

    /**
     * Resume a subscription
     */
    public function resumeSubscription(string $subscriptionId): Subscription
    {
        try {
            $stripeSubscription = $this->stripe->getClient()->subscriptions->update($subscriptionId, [
                'cancel_at_period_end' => false,
            ]);

            $subscription = Subscription::where('subscription_id', $subscriptionId)->first();
            if ($subscription) {
                $subscription->update([
                    'status' => $stripeSubscription->status,
                    'ends_at' => null,
                    'stripe_data' => $stripeSubscription->toArray(),
                ]);
            }

            return $subscription;
        } catch (\Exception $e) {
            Log::error('Subscription resume failed: ' . $e->getMessage());
            throw new StripePaymentException('Subscription resume failed: ' . $e->getMessage());
        }
    }

    /**
     * Update subscription
     */
    public function updateSubscription(string $subscriptionId, array $data): Subscription
    {
        try {
            $updateData = [];

            if (isset($data['price_id'])) {
                $updateData['items'] = [
                    [
                        'id' => $this->getSubscriptionItemId($subscriptionId),
                        'price' => $data['price_id'],
                    ]
                ];
            }

            if (isset($data['metadata'])) {
                $updateData['metadata'] = $data['metadata'];
            }

            if (!empty($updateData)) {
                $stripeSubscription = $this->stripe->getClient()->subscriptions->update($subscriptionId, $updateData);

                $subscription = Subscription::where('subscription_id', $subscriptionId)->first();
                if ($subscription) {
                    $subscription->update([
                        'price_id' => $data['price_id'] ?? $subscription->price_id,
                        'status' => $stripeSubscription->status,
                        'stripe_data' => $stripeSubscription->toArray(),
                    ]);
                }
            }

            return $subscription;
        } catch (\Exception $e) {
            Log::error('Subscription update failed: ' . $e->getMessage());
            throw new StripePaymentException('Subscription update failed: ' . $e->getMessage());
        }
    }

    /**
     * Get subscription by ID
     */
    public function getSubscription(string $subscriptionId): ?Subscription
    {
        return Subscription::where('subscription_id', $subscriptionId)->first();
    }

    /**
     * Get active subscriptions for user
     */
    public function getUserActiveSubscriptions($userId): \Illuminate\Database\Eloquent\Collection
    {
        return Subscription::where('user_id', $userId)
            ->whereIn('status', ['active', 'trialing'])
            ->get();
    }

    /**
     * Get or create customer
     */
    protected function getOrCreateCustomer(array $data): object
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
     * Get subscription item ID
     */
    protected function getSubscriptionItemId(string $subscriptionId): string
    {
        $subscription = $this->stripe->retrieveSubscription($subscriptionId);
        return $subscription->items->data[0]->id ?? '';
    }
}