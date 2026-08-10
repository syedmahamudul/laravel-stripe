<?php

namespace Syedmahamudul\LaravelStripe\Providers;

use Illuminate\Support\ServiceProvider;
use Syedmahamudul\LaravelStripe\Services\StripeService;
use Syedmahamudul\LaravelStripe\Services\PaymentService;
use Syedmahamudul\LaravelStripe\Services\SubscriptionService;
use Syedmahamudul\LaravelStripe\Services\WebhookService;

class StripePaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 🔥 FORCE LOAD HELPERS - This is critical!
        $this->loadHelpers();

        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/stripe.php', 'stripe'
        );

        // Register services
        $this->app->singleton(StripeService::class, function ($app) {
            return new StripeService($app['config']->get('stripe', []));
        });

        $this->app->singleton(PaymentService::class, function ($app) {
            return new PaymentService(
                $app->make(StripeService::class),
                $app['config']->get('stripe', [])
            );
        });

        $this->app->singleton(SubscriptionService::class, function ($app) {
            return new SubscriptionService(
                $app->make(StripeService::class),
                $app['config']->get('stripe', [])
            );
        });

        $this->app->singleton(WebhookService::class, function ($app) {
            return new WebhookService(
                $app->make(StripeService::class),
                $app['config']->get('stripe', [])
            );
        });

        $this->app->alias(StripeService::class, 'laravel-stripe');
    }

    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/stripe.php' => config_path('stripe.php'),
        ], 'stripe-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'stripe-migrations');

        // Load routes
        if (method_exists($this, 'loadRoutesFrom')) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        }

        // Register middleware
        $this->registerMiddleware();
    }

    /**
     * Load helpers from package
     */
    protected function loadHelpers(): void
    {
        $helpersPath = __DIR__ . '/../helpers.php';
        
        if (file_exists($helpersPath)) {
            require_once $helpersPath;
        }
    }

    /**
     * Register middleware
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];
        $middleware = \Syedmahamudul\LaravelStripe\Http\Middleware\VerifyWebhookSignature::class;

        if (method_exists($router, 'aliasMiddleware')) {
            $router->aliasMiddleware('stripe.webhook', $middleware);
        } elseif (method_exists($router, 'middleware')) {
            $router->middleware('stripe.webhook', $middleware);
        }
    }
}