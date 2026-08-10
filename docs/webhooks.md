
## 4. webhooks.md

```markdown
# Webhooks Guide

## What are Webhooks?

Webhooks are automated messages sent from Stripe to your application when events occur. They allow your application to respond to payment events in real-time.

## Setup Webhooks

### Step 1: Local Development with Stripe CLI

```bash
# Install Stripe CLI
brew install stripe/stripe-cli/stripe  # macOS
# or download from https://stripe.com/docs/stripe-cli

# Login
stripe login

# Listen to webhooks
stripe listen --forward-to http://localhost:8000/stripe/webhook

# Trigger test events
stripe trigger payment_intent.succeeded
stripe trigger payment_intent.payment_failed
stripe trigger customer.subscription.created