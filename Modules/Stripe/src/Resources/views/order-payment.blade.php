<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <title>Payment for {{ $order->order_number }}</title>
    <style>
        * { box-sizing: border-box; } body { margin: 0; padding: 40px 16px; background: #faf8f6; color: #172033; font-family: system-ui, sans-serif; }
        main { max-width: 520px; margin: 0 auto; background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 32px; box-shadow: 0 8px 32px #17203308; }
        .eyebrow { color: #b8430a; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; }
        h1 { font-size: 24px; margin: 10px 0; } p { color: #64748b; font-size: 14px; line-height: 1.6; overflow-wrap: anywhere; }
        .amount { font-size: 30px; font-weight: 700; margin: 24px 0; padding: 20px; background: #fff4ee; border-radius: 10px; }
        .amount small { display: block; color: #876959; font-size: 12px; font-weight: 400; margin-bottom: 8px; }
        button { display: flex; width: 100%; justify-content: center; margin-top: 24px; padding: 14px; border: 0; border-radius: 8px; background: #ff6210; color: white; font-weight: 600; font-size: 15px; cursor: pointer; }
        button:disabled { opacity: .6; cursor: wait; } button:focus-visible { outline: 2px solid #b8430a; outline-offset: 4px; }
        #payment-message { color: #b91c1c; } .success { color: #15803d; } .note { font-size: 12px; text-align: center; }
        @media(max-width: 480px) { main { padding: 24px; } }
    </style>
</head>
<body>
<main>
    <span class="eyebrow">Order payment</span>
    <h1>{{ match($state) { 'paid' => 'Payment received', 'processing' => 'Payment is processing', 'pay' => 'Complete your payment', default => 'Payment unavailable' } }}</h1>
    <p>Order #{{ $order->order_number }}</p>
    <div class="amount"><small>{{ $state === 'paid' ? 'Amount paid' : 'Payment amount' }} · {{ strtoupper($payment->currency) }}</small>{{ number_format((float) $payment->amount, 2) }}</div>
    @if($state === 'pay')
        <p>Pay securely using Stripe. This payment includes the order’s applicable tax, discounts, and charges.</p>
        <form id="payment-form">
            <div id="payment-element"></div>
            <p id="payment-message" role="alert" aria-live="polite"></p>
            <button id="pay-button" type="submit" disabled>Loading payment form…</button>
        </form>
        <p class="note">Your payment details are handled by Stripe.</p>
    @elseif($state === 'paid')
        <p class="success">Your payment has been confirmed. You can close this page.</p>
    @elseif($state === 'processing')
        <p>Your payment is being processed. Please wait for confirmation before trying again.</p>
    @else
        <p>This order cannot accept payment through this link right now. Please contact the restaurant for assistance.</p>
    @endif
</main>
@if($state === 'pay')
<script src="https://js.stripe.com/v3/"></script>
<script>
    // Keep Stripe's return parameters, including the client secret, out of the address bar.
    const returnUrl = @json($returnUrl);
    window.history.replaceState({}, '', returnUrl);
    const button = document.getElementById('pay-button');
    const message = document.getElementById('payment-message');
    try {
        const stripe = Stripe(@json($publicKey));
        const elements = stripe.elements({clientSecret: @json($clientSecret), appearance: {theme: 'stripe', variables: {colorPrimary: '#ff6210', borderRadius: '8px'}}});
        const paymentElement = elements.create('payment');
        paymentElement.mount('#payment-element');
        paymentElement.on('ready', () => { button.disabled = false; button.textContent = 'Pay now'; });
        paymentElement.on('loaderror', () => { message.textContent = 'Unable to load the payment form. Please refresh and try again.'; });
        document.getElementById('payment-form').addEventListener('submit', async (event) => {
            event.preventDefault(); button.disabled = true; button.textContent = 'Processing…'; message.textContent = '';
            try {
                const {error} = await stripe.confirmPayment({elements, confirmParams: {return_url: returnUrl}, redirect: 'if_required'});
                if (error) { message.textContent = error.message || 'Payment could not be completed. Please try again.'; button.disabled = false; button.textContent = 'Pay now'; }
                else { window.location.replace(returnUrl); }
            } catch (error) {
                message.textContent = 'Unable to confirm payment. Please refresh to check its status.';
                button.disabled = false; button.textContent = 'Pay now';
            }
        });
    } catch (error) {
        message.textContent = 'Unable to load payment. Please refresh and try again.';
    }
</script>
@else
<script>window.history.replaceState({}, '', @json($returnUrl));</script>
@endif
</body>
</html>
