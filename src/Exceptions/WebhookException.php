<?php

namespace Syedmahamudul\LaravelStripe\Exceptions;

class WebhookException extends StripePaymentException
{
    protected $webhookId;
    protected $eventType;

    public function __construct($message = '', $code = 0, $previous = null, $webhookId = null, $eventType = null)
    {
        parent::__construct($message, $code, $previous);
        $this->webhookId = $webhookId;
        $this->eventType = $eventType;
    }

    public function getWebhookId(): ?string
    {
        return $this->webhookId;
    }

    public function getEventType(): ?string
    {
        return $this->eventType;
    }

    public function setWebhookId(string $webhookId): self
    {
        $this->webhookId = $webhookId;
        return $this;
    }

    public function setEventType(string $eventType): self
    {
        $this->eventType = $eventType;
        return $this;
    }
}