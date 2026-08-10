<?php

namespace Syedmahamudul\LaravelStripe\Exceptions;

class InvalidPaymentMethodException extends PaymentFailedException
{
    protected $paymentMethodId;

    public function __construct($message = '', $code = 0, $previous = null, $paymentMethodId = null)
    {
        parent::__construct($message, $code, $previous);
        $this->paymentMethodId = $paymentMethodId;
    }

    public function getPaymentMethodId(): ?string
    {
        return $this->paymentMethodId;
    }

    public function setPaymentMethodId(string $paymentMethodId): self
    {
        $this->paymentMethodId = $paymentMethodId;
        return $this;
    }
}