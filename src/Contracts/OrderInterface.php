<?php

namespace Syedmahamudul\LaravelStripe\Contracts;

interface OrderInterface
{
    /**
     * Get the order ID
     *
     * @return string|int
     */
    public function getOrderId();

    /**
     * Get the order total amount
     *
     * @return float
     */
    public function getTotal();

    /**
     * Get the order currency
     *
     * @return string
     */
    public function getCurrency();

    /**
     * Get the payment intent ID
     *
     * @return string|null
     */
    public function getPaymentIntentId();

    /**
     * Set the payment intent ID
     *
     * @param string $paymentIntentId
     * @return void
     */
    public function setPaymentIntentId(string $paymentIntentId);

    /**
     * Get the order status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Set the order status
     *
     * @param string $status
     * @return void
     */
    public function setStatus(string $status);

    /**
     * Get the customer ID (user ID)
     *
     * @return string|int|null
     */
    public function getCustomerId();

    /**
     * Get the customer email
     *
     * @return string|null
     */
    public function getCustomerEmail();

    /**
     * Get the customer name
     *
     * @return string|null
     */
    public function getCustomerName();

    /**
     * Get order metadata
     *
     * @return array
     */
    public function getMetadata();

    /**
     * Get order items
     *
     * @return array
     */
    public function getItems();

    /**
     * Mark order as paid
     *
     * @param string $paymentIntentId
     * @return void
     */
    public function markAsPaid(string $paymentIntentId);

    /**
     * Mark order as refunded
     *
     * @param float $amount
     * @return void
     */
    public function markAsRefunded(float $amount);

    /**
     * Mark order as failed
     *
     * @param string $reason
     * @return void
     */
    public function markAsFailed(string $reason);

    /**
     * Check if order can be refunded
     *
     * @return bool
     */
    public function canBeRefunded(): bool;

    /**
     * Get the refundable amount
     *
     * @return float
     */
    public function getRefundableAmount(): float;
}