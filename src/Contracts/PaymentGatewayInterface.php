<?php

namespace Syedmahamudul\LaravelStripe\Contracts;

use Syedmahamudul\LaravelStripe\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Payment Gateway Interface
 * 
 * Defines the contract for payment gateway implementations
 * 
 * @package Syedmahamudul\LaravelStripe\Contracts
 * @author Syed Mahamudul Hassan <syedmahamudhassan@gmail.com>
 * @version 1.0.0
 */
interface PaymentGatewayInterface
{
    /**
     * Process a one-time payment
     *
     * @param array $data {
     *     required: amount, email
     *     optional: currency, name, user_id, metadata, save_payment_method
     * }
     * @return array {
     *     payment: Payment,
     *     client_secret: string,
     *     payment_intent_id: string,
     *     customer_id: string
     * }
     * @throws \Syedmahamudul\LaravelStripe\Exceptions\PaymentFailedException
     */
    public function processPayment(array $data): array;

    /**
     * Confirm a payment
     *
     * @param string $paymentIntentId
     * @param array $data {
     *     optional: payment_method_id, return_url
     * }
     * @return Payment
     * @throws \Syedmahamudul\LaravelStripe\Exceptions\PaymentFailedException
     */
    public function confirmPayment(string $paymentIntentId, array $data = []): Payment;

    /**
     * Create a checkout session for one-time payment
     *
     * @param array $data {
     *     required: items
     *     optional: user_id, email, success_url, cancel_url, metadata, currency, total_amount
     * }
     * @return array {
     *     checkout_url: string,
     *     session_id: string
     * }
     * @throws \Syedmahamudul\LaravelStripe\Exceptions\PaymentFailedException
     */
    public function createCheckout(array $data): array;

    /**
     * Refund a payment
     *
     * @param string $paymentIntentId
     * @param float|null $amount (null for full refund)
     * @param array $metadata
     * @return Payment
     * @throws \Syedmahamudul\LaravelStripe\Exceptions\PaymentFailedException
     */
    public function refundPayment(string $paymentIntentId, ?float $amount = null, array $metadata = []): Payment;

    /**
     * Get payment by intent ID
     *
     * @param string $paymentIntentId
     * @return Payment|null
     */
    public function getPayment(string $paymentIntentId): ?Payment;

    /**
     * Get payments by user
     *
     * @param int|string $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserPayments($userId): \Illuminate\Database\Eloquent\Collection;

    /**
     * Get payment status
     *
     * @param string $paymentIntentId
     * @return string (pending|completed|failed|refunded|cancelled|processing|requires_action)
     * @throws \Syedmahamudul\LaravelStripe\Exceptions\PaymentFailedException
     */
    public function getPaymentStatus(string $paymentIntentId): string;

    /**
     * Check if payment is successful
     *
     * @param Payment $payment
     * @return bool
     */
    public function isPaymentSuccessful(Payment $payment): bool;

    /**
     * Check if payment is refunded
     *
     * @param Payment $payment
     * @return bool
     */
    public function isPaymentRefunded(Payment $payment): bool;

    /**
     * Create a payment intent
     *
     * @param array $data Payment intent parameters
     * @return array
     * @throws \Syedmahamudul\LaravelStripe\Exceptions\PaymentFailedException
     */
    public function createPaymentIntent(array $data): array;

    /**
     * Cancel a payment
     *
     * @param string $paymentIntentId
     * @return Payment
     * @throws \Syedmahamudul\LaravelStripe\Exceptions\PaymentFailedException
     */
    public function cancelPayment(string $paymentIntentId): Payment;

    /**
     * Get payment by order ID
     *
     * @param string $orderId
     * @return Payment|null
     */
    public function getPaymentByOrderId(string $orderId): ?Payment;

    /**
     * Create a customer
     *
     * @param array $data {
     *     email: string,
     *     name: string (optional),
     *     phone: string (optional),
     *     metadata: array (optional)
     * }
     * @return object Stripe Customer object
     * @throws \Syedmahamudul\LaravelStripe\Exceptions\StripePaymentException
     */
    public function createCustomer(array $data): object;

    /**
     * Get a customer by ID
     *
     * @param string $customerId
     * @return object|null
     */
    public function getCustomer(string $customerId): ?object;

    /**
     * Update a customer
     *
     * @param string $customerId
     * @param array $data
     * @return object
     * @throws \Syedmahamudul\LaravelStripe\Exceptions\StripePaymentException
     */
    public function updateCustomer(string $customerId, array $data): object;

    /**
     * Delete a customer
     *
     * @param string $customerId
     * @return bool
     * @throws \Syedmahamudul\LaravelStripe\Exceptions\StripePaymentException
     */
    public function deleteCustomer(string $customerId): bool;

    /**
     * Get all payments with pagination
     *
     * @param int $perPage
     * @param int $page
     * @return LengthAwarePaginator
     */
    public function listPayments(int $perPage = 15, int $page = 1): LengthAwarePaginator;

    /**
     * Get payment summary statistics
     *
     * @param array $filters {
     *     optional: status, start_date, end_date
     * }
     * @return array {
     *     total: int,
     *     completed: int,
     *     pending: int,
     *     failed: int,
     *     refunded: int,
     *     total_amount: float
     * }
     */
    public function getPaymentSummary(array $filters = []): array;

    /**
     * Export payments to CSV
     *
     * @param array $filters {
     *     optional: status, start_date, end_date
     * }
     * @return string CSV content
     */
    public function exportPayments(array $filters = []): string;
}