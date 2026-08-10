<?php

namespace Syedmahamudul\LaravelStripe\Exceptions;

class SubscriptionException extends StripePaymentException
{
    protected $subscriptionId;

    public function __construct($message = '', $code = 0, $previous = null, $subscriptionId = null)
    {
        parent::__construct($message, $code, $previous);
        $this->subscriptionId = $subscriptionId;
    }

    public function getSubscriptionId(): ?string
    {
        return $this->subscriptionId;
    }

    public function setSubscriptionId(string $subscriptionId): self
    {
        $this->subscriptionId = $subscriptionId;
        return $this;
    }

    public static function inactiveSubscription(string $subscriptionId): self
    {
        return new static("Subscription {$subscriptionId} is not active.", 0, null, $subscriptionId);
    }

    public static function subscriptionNotFound(string $subscriptionId): self
    {
        return new static("Subscription {$subscriptionId} not found.", 0, null, $subscriptionId);
    }
}