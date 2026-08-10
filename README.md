# Stripe Payment Gateway Package for Laravel

[![Latest Stable Version](https://poser.pugx.org/syedmahamudul/laravel-stripe/v/stable)](https://packagist.org/packages/syedmahamudul/laravel-stripe)
[![Total Downloads](https://poser.pugx.org/syedmahamudul/laravel-stripe/downloads)](https://packagist.org/packages/syedmahamudul/laravel-stripe)
[![Latest Unstable Version](https://poser.pugx.org/syedmahamudul/laravel-stripe/v/unstable)](https://packagist.org/packages/syedmahamudul/laravel-stripe)
[![License](https://poser.pugx.org/syedmahamudul/laravel-stripe/license)](https://packagist.org/packages/syedmahamudul/laravel-stripe)
[![GitHub stars](https://img.shields.io/github/stars/syedmahamudul/laravel-stripe)](https://github.com/syedmahamudul/laravel-stripe)
[![Monthly Downloads](https://poser.pugx.org/syedmahamudul/laravel-stripe/d/monthly)](https://packagist.org/packages/syedmahamudul/laravel-stripe)
[![Daily Downloads](https://poser.pugx.org/syedmahamudul/laravel-stripe/d/daily)](https://packagist.org/packages/syedmahamudul/laravel-stripe)
[![composer.lock](https://poser.pugx.org/syedmahamudul/laravel-stripe/composerlock)](https://packagist.org/packages/syedmahamudul/laravel-stripe)
[![PHP Version](https://img.shields.io/packagist/php-v/syedmahamudul/laravel-stripe)](https://packagist.org/packages/syedmahamudul/laravel-stripe)

This package is built for [Stripe](https://www.stripe.com) online payment gateway in Bangladesh. It supports **Laravel 5.6+, 6.x, 7.x, 8.x, 9.x, 10.x, and 11.x, 12.x, 13.x** and works with **PHP 7.4 to 8.6+**.

## 🚀 Features

- ✅ **Easy Installation** - One command installation
- ✅ **Fluent API** - Chainable methods for building payment requests
- ✅ **Automatic Validation** - Built-in payment validation
- ✅ **IPN Support** - Instant Payment Notification handling
- ✅ **Refund Functionality** - Process refunds easily
- ✅ **Transaction Query** - Check transaction status
- ✅ **Sandbox/Live Mode** - Easy switching between test and production
- ✅ **All Laravel Versions** - Works with Laravel 5.6 to 13.x
- ✅ **PHP 7.4 to 8.6+** - Compatible with all PHP versions
- ✅ **No Version Conflicts** - Works with any Laravel project
- ✅ **Comprehensive Error Handling** - Detailed error messages
- ✅ **Event-driven Architecture** - Events for payment statuses
- ✅ **Comprehensive Logging** - Debug and track payments
- ✅ **EMI Support** - Easy EMI payment integration
- ✅ **Card BIN Restriction** - Restrict payments to specific card BINs
- ✅ **Custom Callback URLs** - Customize success/failure URLs
- ✅ **Multiple Products** - Support for multiple products in one transaction
- ✅ **Checkout Integration** - AJAX/JSON checkout mode support

## 📋 Table of Contents

- [Installation](#installation)
  - [Requirements](#requirements)
  - [Install via Composer](#install-via-composer)
  - [Publish Configuration](#publish-configuration)
  - [Setup Environment](#setup-environment)
  - [Create Routes](#create-routes)
  - [CSRF Exception](#csrf-exception)
  - [Clear Cache](#clear-cache)
- [Usage](#usage)
  - [Basic Payment](#basic-payment)
  - [Payment with Customer Details](#payment-with-customer-details)
  - [Payment with Shipping](#payment-with-shipping)
  - [Multiple Products](#multiple-products)
  - [Validate Payment](#validate-payment)
  - [Payment with Callback Methods](#payment-with-callback-methods)
  - [Refund Process](#refund-process)
  - [Transaction Query](#transaction-query)
  - [IPN Handling](#ipn-handling)
- [Available Methods](#available-methods)
  - [Required Methods](#required-methods)
  - [Optional Methods](#optional-methods)
  - [Response Methods](#response-methods)
- [Advanced Usage](#advanced-usage)
  - [Checkout Integration](#checkout-integration)
  - [Events](#events)
  - [Error Handling](#error-handling)
  - [Custom Callback URLs](#custom-callback-urls)
  - [Custom Currency](#custom-currency)
  - [EMI Payment](#emi-payment)
  - [Card BIN Restriction](#card-bin-restriction)
  - [Airline Ticket Profile](#airline-ticket-profile)
  - [Travel Vertical Profile](#travel-vertical-profile)
  - [Telecom Vertical Profile](#telecom-vertical-profile)
  - [Set Extras](#set-extras)
- [Security](#security)
- [Testing](#testing)
- [Troubleshooting](#troubleshooting)
  - [Common Issues](#common-issues)
  - [Debugging](#debugging)
- [Changelog](#changelog)
- [License](#license)

---

## Installation

### Requirements

- PHP 7.4 or higher
- Laravel 5.6 or higher
- Stripe Merchant Account (Sandbox or Live)

### Install via Composer

```bash
composer require syedmahamudul/laravel-stripe
```

## Publish Configuration

After installing the package, publish the configuration file.

### Option 1: Automatic Installation (Recommended)

Run the installer command:

```bash
php artisan stripe:install
```

This command will:

- Publish the configuration file
- Create the required configuration
- Display the next setup instructions

---

### Option 2: Manual Configuration

If you don't want to use the automatic installer, you can publish the configuration file manually.

Run the following command:

```bash
php artisan vendor:publish --tag=stripe-config
```

After running the command, Laravel will publish the package configuration file to your application.

You should see output similar to:

```text
INFO  Publishing [stripe-config] assets.

Copied File:
config/stripe.php
```

The published configuration file will be located at:

```text
config/stripe.php
```

You can now customize the package settings and configure your Stripe credentials using your `.env` file.

> **Note:** After publishing the configuration file, clear Laravel's configuration cache to ensure the new settings are loaded.

```bash
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
```

### Verify the Configuration

After publishing, ensure the configuration file exists:

```text
config/
└── stripe.php
```

### Setup Environment

Update your .env file with Stripe credentials:

### Sandbox/Test Mode:

Update your `.env` file with your sandbox/live credentials:

```env
STRIPE_API_KEY=Stripe_API_Key // Live or Sandbox API Key
STRIPE_API_SECRET=Stripe_API_Secret  // Live or Sandbox API Secret
STRIPE_WEBHOOK_SECRET=stripe_webhook_url
STRIPE_CURRENCY=usd
STRIPE_SUCCESS_URL=/payment/success
STRIPE_CANCEL_URL=/payment/cancel
STRIPE_WEBHOOK_URL=/stripe/webhook
```


### Important View File

You must create a view file for the folder payment and view file name form.blade.php, failed.blade.php, success.blade.php,  Or use your custom view file name    


### Create View Controller
```php
   /**
     * Show payment form
     */
    public function index()
    {
        return view('payment.index');
    }
```

### Create Routes

Create routes for Stripe callbacks in routes/web.php:

```php
<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

// Payment Routes
Route::get('/', [PaymentController::class, 'index'])->name('payment.index');
Route::post('/payment/process', [PaymentController::class, 'process'])->name('payment.process');
Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
Route::get('/payment/success/{payment_id}', [PaymentController::class, 'showSuccess'])->name('payment.success.show');
Route::get('/payment/cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');
Route::get('/payment/failed', [PaymentController::class, 'failed'])->name('payment.failed');
Route::post('/payment/refund/{paymentIntentId}', [PaymentController::class, 'refund'])->name('payment.refund');
Route::get('/payment/status/{paymentIntentId}', [PaymentController::class, 'status'])->name('payment.status');

// Subscription Routes
Route::get('/subscription/plans', [SubscriptionController::class, 'plans'])->name('subscription.plans');
Route::post('/subscription/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscription.subscribe');
Route::delete('/subscription/cancel/{subscriptionId}', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
Route::post('/subscription/resume/{subscriptionId}', [SubscriptionController::class, 'resume'])->name('subscription.resume');
Route::put('/subscription/update/{subscriptionId}', [SubscriptionController::class, 'update'])->name('subscription.update');
Route::get('/subscription/my', [SubscriptionController::class, 'mySubscriptions'])->name('subscription.my');
Route::get('/subscription/success', [SubscriptionController::class, 'success'])->name('subscription.success');
Route::get('/subscription/failed', [SubscriptionController::class, 'failed'])->name('subscription.failed');

// Webhook Route (No CSRF protection)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])
    ->name('stripe.webhook')
    ->withoutMiddleware(['web', 'csrf']);

```

### Process Payment

Create a controller to handle Stripe payment requests and callback responses.

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Syedmahamudul\LaravelStripe\Services\PaymentService;
use Syedmahamudul\LaravelStripe\Exceptions\PaymentFailedException;
use Syedmahamudul\LaravelStripe\Models\Payment;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{


     /**
     * Show payment form
     */
    public function index()
    {
        return view('payment.index');
    }

    public function process(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.5',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
        ]);

        try {
            $paymentData = [
                'amount' => $request->amount,
                'email' => $request->email,
                'name' => $request->name,
                'metadata' => [
                    'product_name' => $request->product_name ?? 'Payment',
                    'order_id' => $request->order_id ?? 'ORD-' . time(),
                ],
            ];

            $payment = $this->paymentService->processPayment($paymentData);

            return view('payment.confirm', [
                'clientSecret' => $payment['client_secret'],
                'paymentIntentId' => $payment['payment_intent_id'],
                'amount' => $request->amount,
                'currency' => config('stripe.currency', 'usd'),
            ]);

        } catch (PaymentFailedException $e) {
            return back()->with('error', 'Payment failed: ' . $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

}
```

### Success Payment

Create a controller to handle Stripe payment requests and callback responses.

```php
<?php
public function success(Request $request)
    {
        $paymentIntentId = $request->query('payment_intent');
        $redirectStatus = $request->query('redirect_status');
        
        Log::info('Payment success redirect', [
            'payment_intent' => $paymentIntentId,
            'redirect_status' => $redirectStatus,
            'all_params' => $request->all()
        ]);

        // If we have payment_intent from query string
        if ($paymentIntentId) {
            try {
                // First try to get payment from database
                $payment = Payment::where('payment_intent_id', $paymentIntentId)->first();
                
                if ($payment) {
                    // Update payment status if needed
                    if ($payment->status !== 'completed' && $redirectStatus === 'succeeded') {
                        $payment->update([
                            'status' => 'completed',
                            'paid_at' => now(),
                        ]);
                    }
                    
                    // Redirect to success page with payment details
                    return redirect()->route('payment.success.show', [
                        'payment_id' => $payment->id
                    ]);
                } else {
                    // Payment not found in database, create a record
                    try {
                        // Get payment from Stripe API
                        $stripePayment = $this->paymentService->getPayment($paymentIntentId);
                        
                        if (!$stripePayment) {
                            // Create a new payment record
                            $payment = Payment::create([
                                'payment_intent_id' => $paymentIntentId,
                                'amount' => 0,
                                'currency' => config('stripe.currency', 'usd'),
                                'status' => $redirectStatus === 'succeeded' ? 'completed' : 'pending',
                                'paid_at' => $redirectStatus === 'succeeded' ? now() : null,
                                'metadata' => ['source' => 'stripe_redirect'],
                            ]);
                            
                            return redirect()->route('payment.success.show', [
                                'payment_id' => $payment->id
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error creating payment record: ' . $e->getMessage());
                    }
                }
            } catch (\Exception $e) {
                Log::error('Payment success processing error: ' . $e->getMessage());
            }
        }
        
        // If no payment_intent, show generic success
        return view('payment.success_redirect', [
            'paymentIntentId' => $paymentIntentId,
            'status' => $redirectStatus ?? 'succeeded',
            'amount' => 0,
            'currency' => config('stripe.currency', 'usd'),
        ]);
    }
```

### Success Payment

Create a controller to handle Stripe payment requests and callback responses.

```php
<?php
public function success(Request $request)
    {
        $paymentIntentId = $request->query('payment_intent');
        $redirectStatus = $request->query('redirect_status');
        
        Log::info('Payment success redirect', [
            'payment_intent' => $paymentIntentId,
            'redirect_status' => $redirectStatus,
            'all_params' => $request->all()
        ]);

        // If we have payment_intent from query string
        if ($paymentIntentId) {
            try {
                // First try to get payment from database
                $payment = Payment::where('payment_intent_id', $paymentIntentId)->first();
                
                if ($payment) {
                    // Update payment status if needed
                    if ($payment->status !== 'completed' && $redirectStatus === 'succeeded') {
                        $payment->update([
                            'status' => 'completed',
                            'paid_at' => now(),
                        ]);
                    }
                    
                    // Redirect to success page with payment details
                    return redirect()->route('payment.success.show', [
                        'payment_id' => $payment->id
                    ]);
                } else {
                    // Payment not found in database, create a record
                    try {
                        // Get payment from Stripe API
                        $stripePayment = $this->paymentService->getPayment($paymentIntentId);
                        
                        if (!$stripePayment) {
                            // Create a new payment record
                            $payment = Payment::create([
                                'payment_intent_id' => $paymentIntentId,
                                'amount' => 0,
                                'currency' => config('stripe.currency', 'usd'),
                                'status' => $redirectStatus === 'succeeded' ? 'completed' : 'pending',
                                'paid_at' => $redirectStatus === 'succeeded' ? now() : null,
                                'metadata' => ['source' => 'stripe_redirect'],
                            ]);
                            
                            return redirect()->route('payment.success.show', [
                                'payment_id' => $payment->id
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error creating payment record: ' . $e->getMessage());
                    }
                }
            } catch (\Exception $e) {
                Log::error('Payment success processing error: ' . $e->getMessage());
            }
        }
        
        // If no payment_intent, show generic success
        return view('payment.success_redirect', [
            'paymentIntentId' => $paymentIntentId,
            'status' => $redirectStatus ?? 'succeeded',
            'amount' => 0,
            'currency' => config('stripe.currency', 'usd'),
        ]);
    }
```

### Cancel Payment

Create a controller to handle Stripe payment requests and callback responses.

```php
<?php
 /**
     * Cancel page
     */
    public function cancel(Request $request)
    {
        $paymentIntentId = $request->query('payment_intent');
        
        return view('payment.cancel', [
            'paymentIntentId' => $paymentIntentId,
            'message' => 'Payment was cancelled'
        ]);
    }
```



### Failed Payment

Create a controller to handle Stripe payment requests and callback responses.

```php
<?php
    /**
     * Failed page
     */
    public function failed(Request $request)
    {
        $paymentIntentId = $request->query('payment_intent');
        $errorMessage = session('error') ?? 'Payment failed. Please try again.';
        
        return view('payment.failed', [
            'paymentIntentId' => $paymentIntentId,
            'errorMessage' => $errorMessage
        ]);
    }
```


### Refund Payment

Create a controller to handle Stripe payment requests and callback responses.

```php
<?php
   
    /**
     * Refund payment
     */
    public function refund(Request $request, $paymentIntentId)
    {
        try {
            $payment = $this->paymentService->refundPayment(
                $paymentIntentId,
                $request->amount ?? null
            );

            return response()->json([
                'success' => true,
                'payment' => $payment,
                'message' => 'Payment refunded successfully'
            ]);

        } catch (PaymentFailedException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 400);
        }
    }
```


### Status Payment

Create a controller to handle Stripe payment requests and callback responses.

```php
<?php
    /**
     * Get payment status
     */
    public function status($paymentIntentId)
    {
        try {
            $payment = $this->paymentService->getPayment($paymentIntentId);

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'payment' => $payment,
                'status' => $payment->status
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
```

### Create Controller if You want after success than order data will save in database .

Create a controller to handle Stripe payment requests and callback responses.


### Store the Payment Session

Create a `Payment` model and use Eloquent to temporarily store the order data before redirecting the customer to the Stripe payment gateway.


```bash
php artisan make:model Payment -m
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('payment_intent_id')->unique();
            $table->string('customer_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('usd');
            $table->string('status')->default('pending');
            $table->json('metadata')->nullable();
            $table->json('stripe_data')->nullable();
            $table->string('refund_id')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('checkout_session_id')->nullable();
            $table->index('user_id');
            $table->index('status');
            $table->index('created_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
```




### Store the Subscription

Create a `Subscription` model and use Eloquent to temporarily store the order data before redirecting the customer to the Stripe payment gateway.


```bash
php artisan make:model Subscription -m
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('customer_id');
            $table->string('subscription_id')->unique();
            $table->string('price_id');
            $table->string('status');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->json('metadata')->nullable();
            $table->json('stripe_data')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
```

> **Note:** The order data is stored temporarily and should only be persisted to the `orders` table after the payment is completed successfully.


## Create the Payment Form

Create a payment form where customers can enter their order and billing information before initiating the payment.

Create a Blade view at:

```text
resources/views/payment/index.blade.php
```


```html
<!DOCTYPE html>
<html>
<head>
    <title>Make Payment</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Make Payment</h4>
                    </div>
                    <div class="card-body">
                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <form action="{{ route('payment.process') }}" method="POST" id="payment-form">
                            @csrf
                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount (USD)</label>
                                <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                       id="amount" name="amount" step="0.01" min="0.5" required>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="product_name" class="form-label">Product Name</label>
                                <input type="text" class="form-control" id="product_name" name="product_name">
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Pay Now</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
```

### Clear Cache

After configuration:

```bash
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
```


## Security

- ✅ Callback URLs are automatically excluded from CSRF verification.
- ✅ IPN requests are validated.
- ✅ Amount and Transaction ID are verified.
- ✅ No sensitive data is stored in logs.
- ✅ Sandbox mode supported.
- ✅ SSL/TLS encryption.
- ✅ Validation against Stripe API.

---

## Testing

Run the following command:

```bash
php artisan stripe:make-payment 100 --product="Test Product"
```

This command initiates a sandbox payment and displays the payment URL.

---

## Troubleshooting

## 1. Could not find a matching version

**Cause**

Package version not tagged.

**Solution**

Use `dev-main` or create a Git tag.

---

## Author

**Syed Mahamudul Hassan**
- GitHub: [syedmahamudul](https://github.com/syedmahamudul)
- Email: syedmahamudhassan@gmail.com


---

## License

This package is open-sourced software licensed under the **MIT License**.

See the **LICENSE** file for more information.

---

## Support

If this package helps you, please consider giving it a ⭐ on GitHub.

