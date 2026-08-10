
## 3. usage.md

```markdown
# Usage Guide

## Helper Functions (Recommended)

### Payment Helpers

#### Process a Payment

```php
use Illuminate\Http\Request;

public function createPayment(Request $request)
{
    $payment = process_payment([
        'amount' => $request->amount,
        'email' => $request->email,
        'name' => $request->name,
        'user_id' => auth()->id(),
        'currency' => 'usd',
        'metadata' => [
            'order_id' => 'ORD-' . time(),
            'product' => $request->product_name,
        ],
    ]);

    return view('payment.confirm', [
        'clientSecret' => $payment['client_secret'],
        'paymentIntentId' => $payment['payment_intent_id'],
    ]);
}