<?php

namespace Syedmahamudul\LaravelStripe\Adapters;

use Syedmahamudul\LaravelStripe\Contracts\OrderInterface;

class OrderAdapter implements OrderInterface
{
    protected $order;
    protected $config;

    public function __construct($order, array $config = [])
    {
        $this->order = $order;
        $this->config = $config;
    }

    public function getOrderId()
    {
        return $this->order->id ?? $this->order['id'] ?? null;
    }

    public function getTotal()
    {
        return $this->order->total ?? $this->order['total'] ?? 0;
    }

    public function getCurrency()
    {
        return $this->order->currency ?? $this->order['currency'] ?? 'usd';
    }

    public function getPaymentIntentId()
    {
        return $this->order->payment_intent_id ?? $this->order['payment_intent_id'] ?? null;
    }

    public function setPaymentIntentId(string $paymentIntentId)
    {
        if (is_object($this->order)) {
            $this->order->payment_intent_id = $paymentIntentId;
        } else {
            $this->order['payment_intent_id'] = $paymentIntentId;
        }
    }

    public function getStatus()
    {
        return $this->order->status ?? $this->order['status'] ?? 'pending';
    }

    public function setStatus(string $status)
    {
        if (is_object($this->order)) {
            $this->order->status = $status;
        } else {
            $this->order['status'] = $status;
        }
    }

    public function getCustomerId()
    {
        return $this->order->user_id ?? $this->order->customer_id ?? $this->order['user_id'] ?? $this->order['customer_id'] ?? null;
    }

    public function getCustomerEmail()
    {
        return $this->order->email ?? $this->order['email'] ?? null;
    }

    public function getCustomerName()
    {
        return $this->order->name ?? $this->order['name'] ?? null;
    }

    public function getMetadata()
    {
        $metadata = [];
        
        if (is_object($this->order)) {
            $metadata = $this->order->metadata ?? [];
        } elseif (is_array($this->order)) {
            $metadata = $this->order['metadata'] ?? [];
        }

        return array_merge($metadata, [
            'order_id' => $this->getOrderId(),
            'order_total' => $this->getTotal(),
            'order_currency' => $this->getCurrency(),
        ]);
    }

    public function getItems()
    {
        if (is_object($this->order)) {
            return $this->order->items ?? $this->order->order_items ?? [];
        }
        return $this->order['items'] ?? $this->order['order_items'] ?? [];
    }

    public function markAsPaid(string $paymentIntentId)
    {
        $this->setPaymentIntentId($paymentIntentId);
        $this->setStatus('paid');
        
        if (is_object($this->order) && method_exists($this->order, 'save')) {
            $this->order->save();
        }
    }

    public function markAsRefunded(float $amount)
    {
        $this->setStatus('refunded');
        
        if (is_object($this->order)) {
            $this->order->refund_amount = ($this->order->refund_amount ?? 0) + $amount;
            $this->order->refunded_at = now();
            if (method_exists($this->order, 'save')) {
                $this->order->save();
            }
        } else {
            $this->order['refund_amount'] = ($this->order['refund_amount'] ?? 0) + $amount;
            $this->order['refunded_at'] = now()->toDateTimeString();
        }
    }

    public function markAsFailed(string $reason)
    {
        $this->setStatus('failed');
        
        if (is_object($this->order)) {
            $this->order->failure_reason = $reason;
            if (method_exists($this->order, 'save')) {
                $this->order->save();
            }
        } else {
            $this->order['failure_reason'] = $reason;
        }
    }

    public function canBeRefunded(): bool
    {
        $status = $this->getStatus();
        return in_array($status, ['paid', 'completed', 'partial_refund']);
    }

    public function getRefundableAmount(): float
    {
        if (!$this->canBeRefunded()) {
            return 0;
        }

        $refunded = 0;
        if (is_object($this->order)) {
            $refunded = $this->order->refund_amount ?? 0;
        } else {
            $refunded = $this->order['refund_amount'] ?? 0;
        }

        return $this->getTotal() - $refunded;
    }

    /**
     * Get the original order object
     *
     * @return mixed
     */
    public function getOriginalOrder()
    {
        return $this->order;
    }
}