<?php

namespace Syedmahamudul\LaravelStripe\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Syedmahamudul\LaravelStripe\Services\WebhookService;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected WebhookService $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    /**
     * Handle Stripe webhook
     */
    public function handle(Request $request)
    {
        try {
            $event = $this->webhookService->handleWebhook(
                $request->getContent(),
                $request->header('Stripe-Signature')
            );

            $this->webhookService->processEvent($event);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Webhook Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}