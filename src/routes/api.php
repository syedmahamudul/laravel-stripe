<?php

use Illuminate\Support\Facades\Route;
use Syedmahamudul\LaravelStripe\Http\Controllers\PaymentController;
use Syedmahamudul\LaravelStripe\Http\Controllers\WebhookController;

Route::group(['prefix' => 'stripe', 'middleware' => ['api']], function () {
    // Payment routes
    Route::post('/create-payment', [PaymentController::class, 'createPayment']);
    Route::post('/confirm-payment/{paymentIntentId}', [PaymentController::class, 'confirmPayment']);
    Route::post('/create-checkout', [PaymentController::class, 'createCheckout']);
    Route::post('/refund/{paymentIntentId}', [PaymentController::class, 'refundPayment']);
    Route::get('/payment-status/{paymentIntentId}', [PaymentController::class, 'getPaymentStatus']);
    Route::get('/user-payments', [PaymentController::class, 'getUserPayments']);
});

// Webhook route (no CSRF protection)
Route::post('/stripe/webhook', [WebhookController::class, 'handle'])
    ->name('stripe.webhook')
    ->withoutMiddleware(['web', 'csrf'])
    ->middleware(['stripe.webhook']);