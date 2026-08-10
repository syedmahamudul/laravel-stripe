<?php

namespace Syedmahamudul\LaravelStripe\Exceptions;

class PaymentFailedException extends StripePaymentException
{
    protected $paymentIntentId;

    public function __construct($message = '', $code = 0, $previous = null, $paymentIntentId = null)
    {
        parent::__construct($message, $code, $previous);
        $this->paymentIntentId = $paymentIntentId;
    }

    public function getPaymentIntentId(): ?string
    {
        return $this->paymentIntentId;
    }

    public function setPaymentIntentId(string $paymentIntentId): self
    {
        $this->paymentIntentId = $paymentIntentId;
        return $this;
    }
}