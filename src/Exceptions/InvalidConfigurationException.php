<?php

namespace Syedmahamudul\LaravelStripe\Exceptions;

class InvalidConfigurationException extends StripePaymentException
{
    protected $configKey;

    public function __construct($message = '', $code = 0, $previous = null, $configKey = null)
    {
        parent::__construct($message, $code, $previous);
        $this->configKey = $configKey;
    }

    public function getConfigKey(): ?string
    {
        return $this->configKey;
    }

    public function setConfigKey(string $configKey): self
    {
        $this->configKey = $configKey;
        return $this;
    }

    public static function missingApiKey(): self
    {
        return new static('Stripe API key is not configured. Please set STRIPE_API_KEY in your .env file.');
    }

    public static function missingApiSecret(): self
    {
        return new static('Stripe API secret is not configured. Please set STRIPE_API_SECRET in your .env file.');
    }

    public static function missingWebhookSecret(): self
    {
        return new static('Stripe webhook secret is not configured. Please set STRIPE_WEBHOOK_SECRET in your .env file.');
    }
}