<?php

namespace Syedmahamudul\LaravelStripe\Services;

use Syedmahamudul\LaravelStripe\Adapters\OrderAdapter;
use Syedmahamudul\LaravelStripe\Exceptions\PaymentFailedException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OrderService
{
    protected PaymentService $paymentService;
    protected array $config;

    public function __construct(PaymentService $paymentService, array $config)
    {
        $this->paymentService = $paymentService;
        $this->config = $config;
    }

    /**
     * Process payment for an order
     *
     * @param mixed $order Your order model/array
     * @param array $options Additional options
     * @return array
     * @throws \Exception
     */
    public function processOrderPayment($order, array $options = []): array
    {
        try {
            // Create adapter for the order
            $orderAdapter = new OrderAdapter($order, $this->config);

            // Process payment
            $payment = $this->paymentService->processPayment([
                'amount' => $orderAdapter->getTotal(),
                'currency' => $orderAdapter->getCurrency(),
                'email' => $orderAdapter->getCustomerEmail() ?? $options['email'] ?? null,
                'name' => $orderAdapter->getCustomerName() ?? $options['name'] ?? null,
                'user_id' => $orderAdapter->getCustomerId() ?? $options['user_id'] ?? null,
                'metadata' => array_merge(
                    $orderAdapter->getMetadata(),
                    $options['metadata'] ?? []
                ),
                'save_payment_method' => $options['save_payment_method'] ?? false,
            ]);

            // Update order with payment intent ID
            $orderAdapter->setPaymentIntentId($payment['payment_intent_id']);
            $orderAdapter->setStatus('pending');

            // Save the order if it's an Eloquent model
            if (is_object($order) && method_exists($order, 'save')) {
                $order->save();
            }

            return [
                'success' => true,
                'order' => $order,
                'payment' => $payment,
                'client_secret' => $payment['client_secret'],
                'payment_intent_id' => $payment['payment_intent_id'],
            ];

        } catch (\Exception $e) {
            Log::error('Order payment processing failed: ' . $e->getMessage());
            throw new \Exception('Order payment processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Confirm payment for an order
     *
     * @param mixed $order Your order model/array
     * @param string $paymentIntentId
     * @return array
     * @throws \Exception
     */
    public function confirmOrderPayment($order, string $paymentIntentId): array
    {
        try {
            $orderAdapter = new OrderAdapter($order, $this->config);

            // Confirm payment
            $payment = $this->paymentService->confirmPayment($paymentIntentId);

            if ($payment->isSuccessful()) {
                $orderAdapter->markAsPaid($paymentIntentId);
                $orderAdapter->setStatus('completed');
            } else {
                $orderAdapter->setStatus('failed');
            }

            // Save the order if it's an Eloquent model
            if (is_object($order) && method_exists($order, 'save')) {
                $order->save();
            }

            return [
                'success' => true,
                'order' => $order,
                'payment' => $payment,
                'status' => $payment->status,
            ];

        } catch (\Exception $e) {
            Log::error('Order payment confirmation failed: ' . $e->getMessage());
            throw new \Exception('Order payment confirmation failed: ' . $e->getMessage());
        }
    }

    /**
     * Refund an order
     *
     * @param mixed $order Your order model/array
     * @param float|null $amount Amount to refund (null for full refund)
     * @param array $metadata Additional metadata
     * @return array
     * @throws \Exception
     */
    public function refundOrder($order, ?float $amount = null, array $metadata = []): array
    {
        try {
            DB::beginTransaction();

            $orderAdapter = new OrderAdapter($order, $this->config);

            if (!$orderAdapter->canBeRefunded()) {
                throw new \Exception('Order cannot be refunded. Status: ' . $orderAdapter->getStatus());
            }

            $refundAmount = $amount ?? $orderAdapter->getRefundableAmount();

            if ($refundAmount <= 0) {
                throw new \Exception('No amount available for refund');
            }

            $paymentIntentId = $orderAdapter->getPaymentIntentId();
            if (!$paymentIntentId) {
                throw new \Exception('No payment found for this order');
            }

            // Process refund
            $refund = $this->paymentService->refundPayment(
                $paymentIntentId,
                $refundAmount,
                array_merge(
                    $metadata,
                    [
                        'order_id' => $orderAdapter->getOrderId(),
                        'order_total' => $orderAdapter->getTotal(),
                    ]
                )
            );

            // Update order
            $orderAdapter->markAsRefunded($refundAmount);

            // Save the order if it's an Eloquent model
            if (is_object($order) && method_exists($order, 'save')) {
                $order->save();
            }

            DB::commit();

            return [
                'success' => true,
                'order' => $order,
                'refund' => $refund,
                'refund_amount' => $refundAmount,
                'is_full_refund' => $refundAmount >= $orderAdapter->getTotal(),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order refund failed: ' . $e->getMessage());
            throw new \Exception('Order refund failed: ' . $e->getMessage());
        }
    }

    /**
     * Cancel an order
     *
     * @param mixed $order Your order model/array
     * @param string $reason Cancellation reason
     * @return array
     * @throws \Exception
     */
    public function cancelOrder($order, string $reason = 'Customer requested'): array
    {
        try {
            DB::beginTransaction();

            $orderAdapter = new OrderAdapter($order, $this->config);

            // If order is paid, refund it
            if (in_array($orderAdapter->getStatus(), ['paid', 'completed', 'partial_refund'])) {
                $result = $this->refundOrder($order, null, ['reason' => $reason]);
                return $result;
            }

            // Otherwise just mark as cancelled
            $orderAdapter->setStatus('cancelled');

            if (is_object($order) && method_exists($order, 'save')) {
                $order->save();
            }

            DB::commit();

            return [
                'success' => true,
                'order' => $order,
                'message' => 'Order cancelled successfully',
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order cancellation failed: ' . $e->getMessage());
            throw new \Exception('Order cancellation failed: ' . $e->getMessage());
        }
    }

    /**
     * Get order status
     *
     * @param mixed $order Your order model/array
     * @return array
     */
    public function getOrderStatus($order): array
    {
        $orderAdapter = new OrderAdapter($order, $this->config);
        
        $paymentIntentId = $orderAdapter->getPaymentIntentId();
        $paymentStatus = null;
        
        if ($paymentIntentId) {
            try {
                $paymentStatus = $this->paymentService->getPaymentStatus($paymentIntentId);
            } catch (\Exception $e) {
                // Ignore
            }
        }

        return [
            'order_status' => $orderAdapter->getStatus(),
            'payment_status' => $paymentStatus,
            'refundable_amount' => $orderAdapter->getRefundableAmount(),
            'can_be_refunded' => $orderAdapter->canBeRefunded(),
        ];
    }
}