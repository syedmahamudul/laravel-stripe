<?php

namespace Syedmahamudul\LaravelStripe\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Syedmahamudul\LaravelStripe\Services\PaymentService;
use Syedmahamudul\LaravelStripe\Services\SubscriptionService;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;
    protected SubscriptionService $subscriptionService;

    public function __construct(
        PaymentService $paymentService,
        SubscriptionService $subscriptionService
    ) {
        $this->paymentService = $paymentService;
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Create a payment intent
     */
    public function createPayment(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.5',
            'currency' => 'nullable|string|size:3',
            'user_id' => 'nullable|integer',
            'email' => 'required|email',
            'name' => 'nullable|string',
            'phone' => 'nullable|string',
            'metadata' => 'nullable|array',
            'save_payment_method' => 'nullable|boolean',
        ]);

        $result = $this->paymentService->processPayment($validated);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Confirm a payment
     */
    public function confirmPayment(Request $request, string $paymentIntentId)
    {
        $payment = $this->paymentService->confirmPayment($paymentIntentId);

        return response()->json([
            'success' => true,
            'payment' => $payment,
        ]);
    }

    /**
     * Create checkout session
     */
    public function createCheckout(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.amount' => 'required|numeric|min:0.5',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'nullable|integer|min:1',
            'total_amount' => 'nullable|numeric|min:0',
            'user_id' => 'nullable|integer',
            'email' => 'nullable|email',
            'metadata' => 'nullable|array',
        ]);

        $result = $this->paymentService->createCheckout($validated);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Refund a payment
     */
    public function refundPayment(Request $request, string $paymentIntentId)
    {
        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:0.5',
            'metadata' => 'nullable|array',
        ]);

        $payment = $this->paymentService->refundPayment(
            $paymentIntentId,
            $validated['amount'] ?? null,
            $validated['metadata'] ?? []
        );

        return response()->json([
            'success' => true,
            'payment' => $payment,
        ]);
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $paymentIntentId)
    {
        $payment = $this->paymentService->getPayment($paymentIntentId);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'payment' => $payment,
        ]);
    }

    /**
     * Get user payments
     */
    public function getUserPayments(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
        ]);

        $payments = $this->paymentService->getUserPayments($validated['user_id']);

        return response()->json([
            'success' => true,
            'payments' => $payments,
        ]);
    }
}