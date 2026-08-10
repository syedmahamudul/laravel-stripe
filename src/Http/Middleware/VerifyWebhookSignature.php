<?php

namespace Syedmahamudul\LaravelStripe\Http\Middleware;

use Closure;
use Syedmahamudul\LaravelStripe\Services\StripeService;

class VerifyWebhookSignature
{
    protected StripeService $stripe;

    public function __construct(StripeService $stripe)
    {
        $this->stripe = $stripe;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $signature = $request->header('Stripe-Signature');

        if (!$signature) {
            // Laravel 5.5+ uses abort, older versions use response
            if (function_exists('abort')) {
                abort(400, 'Missing Stripe signature');
            } else {
                return response('Missing Stripe signature', 400);
            }
        }

        $payload = $request->getContent();

        if (!$this->stripe->verifyWebhookSignature($payload, $signature)) {
            if (function_exists('abort')) {
                abort(401, 'Invalid webhook signature');
            } else {
                return response('Invalid webhook signature', 401);
            }
        }

        return $next($request);
    }
}