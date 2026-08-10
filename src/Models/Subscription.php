<?php

namespace Syedmahamudul\LaravelStripe\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Subscription extends Model
{
    protected $table = 'subscriptions';

    protected $fillable = [
        'user_id',
        'customer_id',
        'subscription_id',
        'price_id',
        'status',
        'trial_ends_at',
        'ends_at',
        'metadata',
        'stripe_data',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'ends_at' => 'datetime',
        'metadata' => 'array',
        'stripe_data' => 'array',
    ];

    /**
     * Get the parent subscription model
     */
    public function subscribable()
    {
        return $this->morphTo();
    }

    /**
     * Check if subscription is active
     */
    public function isActive()
    {
        return in_array($this->status, ['active', 'trialing']);
    }

    /**
     * Check if subscription is on trial
     */
    public function onTrial()
    {
        return $this->status === 'trialing';
    }

    /**
     * Check if subscription has ended
     */
    public function hasEnded()
    {
        return in_array($this->status, ['canceled', 'ended']);
    }

    /**
     * Check if subscription is pending cancellation
     */
    public function isPendingCancellation()
    {
        return $this->status === 'pending_cancellation';
    }

    /**
     * Scope for active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'trialing']);
    }
}