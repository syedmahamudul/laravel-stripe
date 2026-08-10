<?php

namespace Syedmahamudul\LaravelStripe\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Syedmahamudul\LaravelStripe\Services\OrderService;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Process payment for an order
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processPayment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'order_id' => 'required|string',
                'order_type' => 'required|string', // 'model' or 'array'
                'amount' => 'required|numeric|min:0.5',
                'currency' => 'nullable|string|size:3',
                'email' => 'required|email',
                'name' => 'nullable|string|max:255',
                'user_id' => 'nullable|integer',
                'metadata' => 'nullable|array',
                'save_payment_method' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create order data from request
            $orderData = [
                'id' => $request->order_id,
                'total' => $request->amount,
                'currency' => $request->currency ?? 'usd',
                'email' => $request->email,
                'name' => $request->name,
                'user_id' => $request->user_id,
                'metadata' => $request->metadata ?? [],
                'items' => $request->items ?? [],
            ];

            $result = $this->orderService->processOrderPayment(
                $orderData,
                [
                    'save_payment_method' => $request->save_payment_method ?? false,
                ]
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Confirm payment for an order
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmPayment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'order_id' => 'required|string',
                'payment_intent_id' => 'required|string',
                'order_type' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create order data
            $orderData = [
                'id' => $request->order_id,
                'payment_intent_id' => $request->payment_intent_id,
            ];

            $result = $this->orderService->confirmOrderPayment(
                $orderData,
                $request->payment_intent_id
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Refund an order
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refundOrder(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'order_id' => 'required|string',
                'amount' => 'nullable|numeric|min:0.5',
                'metadata' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create order data
            $orderData = [
                'id' => $request->order_id,
                'total' => $request->total ?? 0,
                'status' => $request->status ?? 'paid',
                'payment_intent_id' => $request->payment_intent_id,
                'refund_amount' => $request->refund_amount ?? 0,
            ];

            $result = $this->orderService->refundOrder(
                $orderData,
                $request->amount ?? null,
                $request->metadata ?? []
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Cancel an order
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelOrder(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'order_id' => 'required|string',
                'reason' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $orderData = [
                'id' => $request->order_id,
                'status' => $request->status ?? 'pending',
                'payment_intent_id' => $request->payment_intent_id,
                'total' => $request->total ?? 0,
                'refund_amount' => $request->refund_amount ?? 0,
            ];

            $result = $this->orderService->cancelOrder(
                $orderData,
                $request->reason ?? 'Customer requested'
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}