<?php

namespace Syedmahamudul\LaravelStripe\Exceptions;

use Exception;

class StripePaymentException extends Exception
{
    /**
     * @var array
     */
    protected $context = [];

    /**
     * Create a new Stripe payment exception instance.
     *
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     * @param array $context
     */
    public function __construct($message = '', $code = 0, $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Get the exception context.
     *
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Set the exception context.
     *
     * @param array $context
     * @return $this
     */
    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Render the exception as an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error' => $this->getMessage(),
                'code' => $this->getCode(),
                'context' => $this->context,
            ], $this->getCode() ?: 400);
        }

        return response($this->getMessage(), $this->getCode() ?: 400);
    }

    /**
     * Report the exception.
     *
     * @return void
     */
    public function report()
    {
        \Illuminate\Support\Facades\Log::error($this->getMessage(), [
            'exception' => $this,
            'context' => $this->context,
            'trace' => $this->getTraceAsString(),
        ]);
    }
}