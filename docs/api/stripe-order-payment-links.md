# Stripe order payment links

`POST /api/checkout/place-order` returns `data.order.payment_url` for Stripe orders, alongside the existing `client_secret`. Cash on delivery returns a null link.

```json
{
  "payment_url": "https://your-domain/stripe/order-payment/123/pi_example?signature=..."
}
```

The URL opens a customer payment page using the PaymentIntent created during order placement. Opening or refreshing the page does not create another PaymentIntent.

The same URL is saved in `payments.details.payment_url` and `transactions.metadata.payment_url`. No database migration is required. The admin Transactions table and transaction details show the link for pending payments. Completed, processing, refunded, and cancelled payments do not offer another payment action.

Links are signed bearer links: share only with the intended payer. The URL does not include a client secret. The payment page retrieves the saved intent using the payment's configuration, validates the order ID, amount, and currency, and confirms success from Stripe's server response. Stripe return query parameters are not payment proof.

The configured `stripe_public_key` is required to show Stripe's Payment Element. Existing mobile clients can continue using `client_secret`. Keep the Stripe webhook configured for asynchronous payment completion.

Implementation references: [Stripe PaymentIntent reuse](https://docs.stripe.com/payments/payment-intents), [Stripe Payment Element](https://docs.stripe.com/payments/payment-element).
