<?php

namespace Syedmahamudul\LaravelStripe\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    protected $table = 'payments';

    protected $fillable = [
        'user_id',
        'payment_intent_id',
        'customer_id',
        'amount',
        'currency',
        'status',
        'metadata',
        'stripe_data',
        'refund_id',
        'refund_amount',
        'refunded_at',
        'paid_at',
        'checkout_session_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'metadata' => 'array',
        'stripe_data' => 'array',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    /**
     * Get the parent payment model
     */
    public function payable()
    {
        return $this->morphTo();
    }

    /**
     * Scope for successful payments
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for pending payments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Check if payment is successful
     */
    public function isSuccessful()
    {
        return $this->status === 'completed';
    }

    /**
     * Check if payment is refunded
     */
    public function isRefunded()
    {
        return $this->status === 'refunded';
    }

    /**
     * Check if payment is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }
}