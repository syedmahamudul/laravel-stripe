<?php

namespace Syedmahamudul\LaravelStripe\Compatibility;

class Versions
{
    /**
     * Check if running on Laravel
     */
    public static function isLaravel(): bool
    {
        return class_exists('\Illuminate\Foundation\Application');
    }

    /**
     * Get Laravel version
     */
    public static function getLaravelVersion(): string
    {
        if (!self::isLaravel()) {
            return '0.0.0';
        }

        if (class_exists('\Illuminate\Foundation\Application')) {
            if (method_exists('\Illuminate\Foundation\Application', 'version')) {
                return \Illuminate\Foundation\Application::VERSION ?? '0.0.0';
            }
        }

        return '0.0.0';
    }

    /**
     * Check Laravel version compatibility
     */
    public static function isLaravelVersion(string $version): bool
    {
        $current = self::getLaravelVersion();
        return version_compare($current, $version, '>=');
    }

    /**
     * Check PHP version
     */
    public static function getPhpVersion(): string
    {
        return PHP_VERSION;
    }

    /**
     * Check if PHP version meets requirement
     */
    public static function isPhpVersion(string $version): bool
    {
        return version_compare(PHP_VERSION, $version, '>=');
    }

    /**
     * Get Stripe SDK version
     */
    public static function getStripeVersion(): string
    {
        if (class_exists('\Stripe\Stripe')) {
            return \Stripe\Stripe::VERSION ?? '0.0.0';
        }
        return '0.0.0';
    }

    /**
     * Get the appropriate Stripe API version
     */
    public static function getStripeApiVersion(): string
    {
        if (self::isPhpVersion('8.0')) {
            return '2023-10-16';
        }
        if (self::isPhpVersion('7.4')) {
            return '2022-11-15';
        }
        if (self::isPhpVersion('7.3')) {
            return '2020-08-27';
        }
        return '2019-12-03';
    }

    /**
     * Check if Laravel 5.5 or below
     */
    public static function isLegacyLaravel(): bool
    {
        $version = self::getLaravelVersion();
        return version_compare($version, '5.6', '<');
    }

    /**
     * Check if Laravel 6.x or above
     */
    public static function isModernLaravel(): bool
    {
        $version = self::getLaravelVersion();
        return version_compare($version, '6.0', '>=');
    }

    /**
     * Get supported Laravel features
     */
    public static function getSupportedFeatures(): array
    {
        $version = self::getLaravelVersion();
        
        return [
            'loadRoutesFrom' => method_exists('\Illuminate\Support\ServiceProvider', 'loadRoutesFrom'),
            'loadViewsFrom' => method_exists('\Illuminate\Support\ServiceProvider', 'loadViewsFrom'),
            'loadTranslationsFrom' => method_exists('\Illuminate\Support\ServiceProvider', 'loadTranslationsFrom'),
            'publishes' => method_exists('\Illuminate\Support\ServiceProvider', 'publishes'),
            'mergeConfigFrom' => method_exists('\Illuminate\Support\ServiceProvider', 'mergeConfigFrom'),
            'aliasMiddleware' => version_compare($version, '5.5', '>='),
            'packageDiscovery' => version_compare($version, '5.5', '>='),
        ];
    }
}