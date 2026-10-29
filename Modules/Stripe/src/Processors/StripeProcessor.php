<?php

namespace Modules\Stripe\Processors;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\Transaction;
use Modules\Orders\Services\OrderService;
use Modules\Payments\Contracts\PaymentProcessor;
use Modules\Payments\DTO\PaymentResponse;
use Modules\Payments\Enums\PaymentStatus;
use Modules\Payments\Models\PaymentConfiguration;
use Modules\Payments\Models\PaymentMethod;
use Modules\Payments\Models\Payments;
use Modules\Subscriptions\Services\SubscriptionService;
use Modules\Invoice\Models\InvoicePayment;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeProcessor implements PaymentProcessor
{
    protected array $configuration = [];


    public function __construct(array $config = [])
    {
        if (!empty($config)) {
            $this->configuration = $config;
            return;
        }

        $this->configuration = $this->resolveStoredConfiguration();
    }

    private function resolveStoredConfiguration(): array
    {
        try {
            if (
                !class_exists(PaymentMethod::class)
                || !class_exists(PaymentConfiguration::class)
            ) {
                return [];
            }

            $method = PaymentMethod::query()
                ->where('code', 'stripe')
                ->first();

            if (!$method) {
                return [];
            }

            $configuration = PaymentConfiguration::query()
                ->where('payment_method_id', $method->id)
                ->latest('id')
                ->first();

            if (!$configuration) {
                return [];
            }

            if (is_array($configuration->config)) {
                return $configuration->config;
            }
            // dd($configuration);
            return json_decode((string) $configuration->config, true) ?: [];
        } catch (\Throwable $e) {
            Log::warning('Unable to load Stripe payment configuration.', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }
    public function getName(): string
    {
        return 'Stripe';
    }
    public function getTemplate(): ?string
    {
        return 'stripe::settings.stripe_template';
    }
    public function getProcessorTemplate(): ?string
    {
        return 'stripe::processors.stripe_template';
    }

    // public function charge(array $payload): PaymentResponse
    // {
    //     $secretKey = $this->resolveSecretKey();

    //     if (empty($secretKey)) {
    //         throw new Exception('Stripe secret key is missing. Please configure Stripe payment settings.');
    //     }

    //     $amount = (float) ($payload['total'] ?? $payload['amount'] ?? 0);
    //     if ($amount <= 0) {
    //         throw new Exception('Invalid payment amount.');
    //     }

    //     $currency = strtolower((string) ($payload['currency'] ?? 'usd'));
    //     $orderNumber = trim((string) ($payload['order_number'] ?? ''));
    //     $orderId = (int) ($payload['order_id'] ?? 0);

    //     if ($orderNumber === '') {
    //         throw new Exception('Order number is required for Stripe checkout.');
    //     }

    //     Stripe::setApiKey($secretKey);

    //     $successRoute = route('checkout.success', [
    //         'orderNumber' => $orderNumber,
    //         'status' => 'complete',
    //     ]);

    //     $cancelRoute = route('checkout.failed', [
    //         'orderNumber' => $orderNumber,
    //     ]);

    //     $successUrl = $successRoute . (str_contains($successRoute, '?') ? '&' : '?') . 'session_id={CHECKOUT_SESSION_ID}';
    //     $cancelUrl = $cancelRoute;

    //     $productName = trim((string) ($payload['product_name'] ?? ("Order {$orderNumber}")));
    //     if ($productName === '') {
    //         $productName = "Order {$orderNumber}";
    //     }

    //     $metadata = $this->normalizeMetadata(array_merge(
    //         is_array($payload['metadata'] ?? null) ? $payload['metadata'] : [],
    //         [
    //             'order_number' => $orderNumber,
    //             'order_id' => (string) $orderId,
    //         ]
    //     ));

    //     $session = Session::create([
    //         'mode' => 'payment',
    //         'success_url' => $successUrl,
    //         'cancel_url' => $cancelUrl,
    //         'line_items' => [[
    //             'quantity' => 1,
    //             'price_data' => [
    //                 'currency' => $currency,
    //                 'product_data' => [
    //                     'name' => $productName,
    //                 ],
    //                 'unit_amount' => (int) round($amount * 100),
    //             ],
    //         ]],
    //         'metadata' => $metadata,
    //     ]);

    //     return new PaymentResponse([
    //         'gateway' => 'stripe',
    //         'gateway_payment_id' => $session->id ?? null,
    //         'status' => 'pending',
    //         'amount' => $amount,
    //         'currency' => strtoupper($currency),
    //         'order_id' => $orderId,
    //         'receipt_url' => null,
    //         'card' => [],
    //         'billing_address' => $payload['billing_address'] ?? [],
    //         'shipping_address' => $payload['shipping_address'] ?? [],
    //         'metadata' => $metadata,
    //         'configuration' => $this->configuration,
    //         'payment_method' => 'stripe',
    //         'redirect_url' => $session->url ?? '',
    //     ]);
    // }


    public function charge(array $payload): PaymentResponse
    {
        Log::info('Stripe charge called', ['payload' => $payload]);
        $secretKey = $this->resolveSecretKey();

        if (empty($secretKey)) {
            throw new Exception('Stripe secret key is missing.');
        }

        $amount = (float) ($payload['total'] ?? $payload['amount'] ?? 0);
        if ($amount <= 0) {
            throw new Exception('Invalid payment amount.');
        }

        $currency   = strtolower((string) ($payload['currency'] ?? 'usd'));
        $orderNumber = trim((string) ($payload['order_number'] ?? ''));
        // $orderId    = (int) ($payload['order_id'] ?? 0);
        $orderId = (int) ($payload['id'] ?? $payload['order_id'] ?? 0);

        Stripe::setApiKey($secretKey);

        // Checkout Session NAHI — PaymentIntent banao
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount'                    => (int) round($amount * 100),
            'currency'                  => $currency,
            'automatic_payment_methods' => ['enabled' => true],
            'metadata'                  => [
                'order_id'     => (string) $orderId,
                'order_number' => $orderNumber,
            ],
        ]);

        return new PaymentResponse([
            'gateway'            => 'stripe',
            'gateway_payment_id' => $paymentIntent->id,
            'status'             => 'pending',
            'amount'             => $amount,
            'currency'           => strtoupper($currency),
            'order_id'           => $orderId,
            'payment_method'     => 'stripe',
            'redirect_url'       => '',
            'client_secret'      => $paymentIntent->client_secret,
        ]);
    }

    public function refund(string $transactionId, float $amount): array
    {
        if (!is_finite($amount) || $amount <= 0) {
            throw new \InvalidArgumentException('Invalid refund amount.');
        }

        $stripe = $this->refundClient();
        if (str_starts_with($transactionId, 'pi_')) {
            $original = $stripe->paymentIntents->retrieve($transactionId, []);
            $reference = ['payment_intent' => $transactionId];
        } elseif (str_starts_with($transactionId, 'ch_')) {
            $original = $stripe->charges->retrieve($transactionId, []);
            $reference = ['charge' => $transactionId];
        } else {
            throw new \InvalidArgumentException('A Stripe PaymentIntent or charge reference is required for a refund.');
        }

        $currency = strtolower((string) $original->currency);
        $zeroDecimal = ['bif', 'clp', 'djf', 'gnf', 'jpy', 'kmf', 'krw', 'mga', 'pyg', 'rwf', 'vnd', 'vuv', 'xaf', 'xof', 'xpf'];
        $minorAmount = $amount * (in_array($currency, $zeroDecimal, true) ? 1 : 100);
        if (abs($minorAmount - round($minorAmount)) > 0.00001
            || (in_array($currency, ['isk', 'ugx'], true) && abs($amount - round($amount)) > 0.00001)) {
            throw new \InvalidArgumentException('The refund amount has invalid precision for the payment currency.');
        }
        $minorAmount = (int) round($minorAmount);
        if ($minorAmount <= 0) {
            throw new \InvalidArgumentException('Invalid refund amount.');
        }

        $refund = $stripe->refunds->create($reference + [
            'amount' => $minorAmount,
            'reason' => 'requested_by_customer',
        ], [
            // Retrying a cancelled order after a local rollback must reuse the refund.
            'idempotency_key' => 'order-cancellation-'.hash('sha256', $transactionId.':'.$minorAmount),
        ]);

        return ['id' => $refund->id, 'status' => $refund->status, 'amount' => $refund->amount, 'currency' => $refund->currency];
    }

    protected function refundClient(): \Stripe\StripeClient
    {
        $secretKey = $this->resolveSecretKey();
        if ($secretKey === '') {
            throw new \RuntimeException('Stripe secret key is missing.');
        }

        return new \Stripe\StripeClient($secretKey);
    }

    /**
     * Confirm a Stripe PaymentIntent from the client return flow.
     * Stripe is queried server-side; client supplied success values are never trusted.
     */
    public function confirmPayment(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|integer',
            'payment_intent_id' => 'required|string',
        ]);

        $customer = auth('customer')->user();
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $order = Order::query()
            ->whereKey($validated['order_id'])
            ->where('customer_type', \Modules\Acl\Models\Customer::class)
            ->where('customer_id', $customer->id)
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $payment = \Modules\Payments\Models\Payments::query()
            ->where('order_id', $order->id)
            ->where('gateway_reference', $validated['payment_intent_id'])
            ->first();

        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Payment does not match this order'], 404);
        }

        try {
            $secretKey = $this->resolveSecretKey();
            if ($secretKey === '') {
                throw new Exception('Stripe secret key is missing.');
            }

            Stripe::setApiKey($secretKey);
            $intent = \Stripe\PaymentIntent::retrieve($validated['payment_intent_id']);
        } catch (\Throwable $e) {
            Log::error('Unable to verify Stripe PaymentIntent', [
                'order_id' => $order->id,
                'payment_intent_id' => $validated['payment_intent_id'],
                'error' => $e->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => 'Unable to verify payment with Stripe'], 502);
        }

        if ((string) ($intent->metadata->order_id ?? '') !== (string) $order->id) {
            return response()->json(['success' => false, 'message' => 'Payment metadata does not match this order'], 403);
        }

        if ($intent->status !== 'succeeded') {
            return response()->json([
                'success' => true,
                'paid' => false,
                'payment_status' => $intent->status,
                'message' => 'Stripe has not confirmed the payment yet',
            ], 202);
        }

        try {
            DB::transaction(function () use ($order, $payment, $intent) {
                $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
                $lockedPayment = \Modules\Payments\Models\Payments::whereKey($payment->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (in_array($lockedOrder->status, [Order::STATUS_CANCELLED, Order::STATUS_FAILED, Order::STATUS_REFUNDED], true)) {
                    throw new \DomainException('This order is already cancelled or failed and cannot be marked paid.');
                }

                $lockedOrder->update([
                    'status' => $this->configuration['stripe_order_complete'] ?? Order::STATUS_NEW,
                    'payment_status' => PaymentStatus::PAID->value,
                ]);

                $lockedPayment->update([
                    'status' => PaymentStatus::COMPLETED->value,
                    'processed_at' => now(),
                ]);

                $transaction = Transaction::where('order_id', $lockedOrder->id)
                    ->where('type', 'payment')
                    ->where('gateway_reference', $intent->id)
                    ->lockForUpdate()
                    ->first();

                if ($transaction) {
                    $transaction->update([
                        'status' => \Modules\Orders\Enums\TransactionStatus::COMPLETED->value,
                        'processed_at' => now(),
                    ]);
                }

                Log::info('Stripe payment confirmed by client return flow', [
                    'order_id' => $lockedOrder->id,
                    'payment_intent_id' => $intent->id,
                ]);
            });
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 409);
        }

        return response()->json([
            'success' => true,
            'paid' => true,
            'order_id' => $order->id,
            'payment_status' => PaymentStatus::PAID->value,
            'message' => 'Payment confirmed and order updated',
        ]);
    }

    public function handleWebhook(?Request $request = null)
    {

        DB::beginTransaction();

        try {
            $payload = $request ? $request->getContent() : file_get_contents('php://input');
            $data = json_decode($payload, true);

            Log::info('Stripe webhook received', $data);

            $eventId = $data['id'] ?? null;
            $eventName = $data['name'] ?? $data['type'] ?? null;

            if (!$eventId || !$eventName) {
                return response()->json(['error' => 'Invalid webhook'], 400);
            }

            $eventData = $data['data']['object'] ?? [];

            if ($eventName === 'billing_checkout.completed' || $eventName === 'checkout.session.completed') {
                $orderId = $eventData['metadata']['order_id'] ?? null;

                if (!$orderId) {
                    Log::warning('Stripe webhook order_id missing in metadata', ['event_id' => $eventId]);
                    return response()->json(['error' => 'Order ID missing'], 400);
                }

                $order = Order::findOrFail($orderId);

                $payment = Payments::where('order_id', $order->id)->first();
                if ($payment) {
                    $paymentStatus = ($eventData['payment_status'] ?? null) === 'unpaid'
                        ? $payment->status
                        : PaymentStatus::COMPLETED->value;
                    $payment->update([
                        'status' => $paymentStatus,
                        'details' => $this->webhookPaymentDetails($eventData, $paymentStatus, $payment->details ?? []),
                    ]);
                }

                $transaction = Transaction::where('order_id', $order->id)
                    ->where('gateway_reference', $eventData['id'])
                    ->first();

                if ($transaction && $transaction->status === Transaction::STATUS_COMPLETED) {
                    Log::info('Stripe checkout already processed, skipping', [
                        'order_id' => $order->id,
                        'transaction_id' => $transaction->id,
                        'event_id' => $eventId,
                    ]);
                } else {
                    if ($transaction) {
                        $transaction->update(['status' => Transaction::STATUS_COMPLETED]);
                    }

                    $subscription = app(SubscriptionService::class)
                        ->createSubscriptionFromOrder($order, 'checkout', [
                            'source' => 'stripe_checkout_completed_webhook',
                            'payment_gateway' => 'stripe',
                            'gateway_reference' => $eventData['id'] ?? null,
                            'payment_status' => strtolower((string) ($eventData['payment_status'] ?? $eventData['status'] ?? 'completed')),
                            'payment_details' => is_array($eventData) ? $eventData : [],
                            'send_payment_confirmation' => true,
                        ]);

                    Log::info('Stripe subscription created from order', [
                        'subscription' => $subscription,
                        'subscription_id' => $subscription?->id,
                        'order_id' => $order->id,
                    ]);

                    $order->update([
                        'status' => $this->configuration['stripe_order_complete'] ?? Order::STATUS_NEW,
                        'payment_status' => PaymentStatus::PAID->value,
                    ]);
                }
            } elseif ($eventName === 'payment_intent.succeeded') {
                $orderId = $eventData['metadata']['order_id'] ?? null;

                if (!$orderId) {
                    return response()->json(['error' => 'Order ID missing'], 400);
                }

                $order = Order::findOrFail($orderId);

                $order->update([
                    'status'         => $this->configuration['stripe_order_complete'] ?? Order::STATUS_NEW,
                    'payment_status' => PaymentStatus::PAID->value,
                ]);

                // Update payment
                $payment = \Modules\Payments\Models\Payments::where('order_id', $order->id)->first();
                if ($payment) {
                    $payment->update([
                        'status' => PaymentStatus::COMPLETED->value,
                        'details' => $this->webhookPaymentDetails($eventData, PaymentStatus::COMPLETED->value, $payment->details ?? []),
                    ]);
                }

                // Update transaction
                $transaction = \Modules\Orders\Models\Transaction::where('order_id', $order->id)
                    ->where('type', 'payment')
                    ->first();
                if ($transaction) {
                    $transaction->update([
                        'status' => \Modules\Orders\Enums\TransactionStatus::COMPLETED->value,
                        'processed_at' => now(),
                    ]);
                }


                $order->load('customer');
                if ($order->customer && $order->customer->email) {
                    $notificationService = app(\Modules\Notifications\Services\NotificationService::class);

                    
                    $notificationService->trigger(
                        'Orders',
                        'OrderCreated',
                        $order->customer->email,
                        $order
                    );

               
                    if ($payment) {
                        $notificationService->trigger(
                            'Payments',
                            'PaymentSuccess',
                            $order->customer->email,
                            $payment
                        );
                    }
                }



                Log::info('Stripe PaymentIntent succeeded', ['order_id' => $order->id]);
            } elseif (in_array($eventName, ['payment_intent.payment_failed', 'checkout.session.expired', 'charge.failed', 'payment_intent.canceled', 'checkout.session.async_payment_failed'], true)) {
                $orderId = $eventData['metadata']['order_id'] ?? null;

                if (!$orderId && isset($eventData['payment_intent']) && is_array($eventData['payment_intent'])) {
                    $orderId = $eventData['payment_intent']['metadata']['order_id'] ?? null;
                }

                if (!$orderId) {
                    Log::warning('Stripe payment failure missing order_id', ['event_id' => $eventId, 'event_name' => $eventName]);
                    DB::commit();
                    return response()->json(['success' => true]);
                }

                $order = Order::find($orderId);
                if ($order) {
                    app(OrderService::class)->markOrderPaymentFailed($order, 'Stripe payment failed: ' . $eventName);
                }

                $payment = \Modules\Payments\Models\Payments::where('order_id', $orderId)->first();
                if ($payment) {
                    $payment->update([
                        'status' => PaymentStatus::FAILED->value,
                        'details' => $this->webhookPaymentDetails($eventData, PaymentStatus::FAILED->value, $payment->details ?? []),
                    ]);
                }
            } elseif ($eventName === 'invoice.payment.paid' || $eventName === 'invoice.payment_succeeded') {
                $transaction = Transaction::where('gateway_reference', $eventData['id'])->first();

                if (!$transaction) {
                    Log::info('Stripe webhook transaction not found for invoice payment', [
                        'gateway_reference' => $eventData['id'],
                        'event_id' => $eventId,
                    ]);
                    DB::commit();
                    return response()->json(['success' => true]);
                }

                $payment = Payments::where('order_id', $transaction->order_id)
                    ->where('gateway_reference', $eventData['id'])
                    ->first();
                if ($payment) {
                    $payment->update([
                        'status' => PaymentStatus::COMPLETED->value,
                        'details' => $this->webhookPaymentDetails($eventData, PaymentStatus::COMPLETED->value, $payment->details ?? []),
                    ]);
                }

                if ($transaction->status === Transaction::STATUS_COMPLETED) {
                    Log::info('Stripe invoice payment already processed renewal, skipping', [
                        'transaction_id' => $transaction->id,
                        'event_id' => $eventId,
                    ]);
                } else {
                    $transaction->update(['status' => Transaction::STATUS_COMPLETED]);

                    $order = Order::findOrFail($transaction->order_id);

                    $subscription = app(SubscriptionService::class)
                        ->createSubscriptionFromOrder($order, 'invoice', [
                            'source' => 'stripe_invoice_paid_webhook',
                            'payment_gateway' => 'stripe',
                            'gateway_reference' => $eventData['id'] ?? null,
                            'payment_status' => strtolower((string) ($eventData['payment_status'] ?? $eventData['status'] ?? 'paid')),
                            'payment_details' => is_array($eventData) ? $eventData : [],
                            'send_payment_confirmation' => true,
                        ]);

                    $order->update([
                        'status' => $this->configuration['stripe_order_complete'],
                        'payment_status' => PaymentStatus::PAID->value,
                    ]);

                    $invoicePayment = InvoicePayment::where('transaction_reference', $eventData['id'])
                        ->with('invoice')
                        ->first();

                    $invoice = $invoicePayment?->invoice;
                    if ($invoice) {
                        $invoice->update(['status' => strtolower($eventData['payment_status'] ?? $eventData['status'] ?? 'paid')]);
                        Log::info('Stripe invoice record updated', ['invoice_id' => $invoice->id]);
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            Log::warning('Stripe webhook referenced resource not found', [
                'message' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Resource not found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stripe webhook error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    protected function webhookPaymentDetails(array $eventData, string $status, array $existing = []): array
    {
        $intent = is_array($eventData['payment_intent'] ?? null)
            ? $eventData['payment_intent']
            : $eventData;
        if (is_string($eventData['payment_intent'] ?? null)) {
            $intent = $this->retrieveWebhookResource('paymentIntents', $eventData['payment_intent'], [
                'expand' => ['latest_charge', 'payment_method'],
            ]);
        }

        $charge = $intent['latest_charge'] ?? $intent['charges']['data'][0] ?? $eventData['charge'] ?? [];
        if (is_string($charge)) {
            $charge = $this->retrieveWebhookResource('charges', $charge);
        }

        $method = $intent['last_payment_error']['payment_method'] ?? $intent['payment_method'] ?? [];
        if (is_string($method) && empty($charge['payment_method_details'])) {
            $method = $this->retrieveWebhookResource('paymentMethods', $method);
        }
        $method = is_array($method) ? $method : [];

        $card = $charge['payment_method_details']['card']
            ?? $eventData['payment_method_details']['card']
            ?? $method['card']
            ?? [];
        $billing = $charge['billing_details']['address']
            ?? $eventData['billing_details']['address']
            ?? $method['billing_details']['address']
            ?? $eventData['customer_details']['address']
            ?? [];
        $shipping = $eventData['shipping_details']['address']
            ?? $eventData['collected_information']['shipping_details']['address']
            ?? $eventData['shipping']['address']
            ?? $intent['shipping']['address']
            ?? $charge['shipping']['address']
            ?? [];

        // Retain existing values when Stripe omits a field from a later event.
        $details = array_replace([
            'card' => [],
            'metadata' => [],
            'billing_address' => [],
            'shipping_address' => [],
        ], $existing);
        foreach ([
            'card' => array_intersect_key($card, array_flip(['brand', 'last4', 'exp_month', 'exp_year'])),
            'metadata' => array_replace($intent['metadata'] ?? [], $eventData['metadata'] ?? []),
            'billing_address' => $billing,
            'shipping_address' => $shipping,
        ] as $key => $value) {
            $value = array_filter($value, static fn ($item) => $item !== null && $item !== '');
            $details[$key] = array_replace(is_array($details[$key]) ? $details[$key] : [], $value);
        }
        $details['status'] = $status;

        return $details;
    }

    protected function retrieveWebhookResource(string $resource, string $id, array $params = []): array
    {
        $secretKey = $this->resolveSecretKey();
        if ($secretKey === '') {
            throw new Exception('Stripe secret key is missing. Cannot retrieve webhook payment details.');
        }

        $stripe = new \Stripe\StripeClient($secretKey);

        return $stripe->{$resource}->retrieve($id, $params)->toArray();
    }

    public function subscribe(array $payload): array
    {
        $secretKey = $this->resolveSecretKey();

        if (empty($secretKey)) {
            throw new Exception('Stripe secret key is missing. Please configure Stripe payment settings.');
        }

        Stripe::setApiKey($secretKey);

        $amount = (float) ($payload['amount'] ?? $payload['base_amount'] ?? 0);
        if ($amount <= 0) {
            throw new Exception('Invalid payment amount.');
        }

        $currency = strtolower((string) ($payload['currency'] ?? 'usd'));
        $successUrl = (string) ($payload['success_url'] ?? '');
        $cancelUrl = (string) ($payload['cancel_url'] ?? '');

        if ($successUrl === '') {
            throw new Exception('Stripe success URL is required.');
        }

        if ($cancelUrl === '') {
            $cancelUrl = route('frontend.index');
        }

        $productName = (string) ($payload['product_name'] ?? 'Subscription');
        $customerEmail = (string) data_get($payload, 'user.email', '');
        $metadata = $this->normalizeMetadata((array) ($payload['metadata'] ?? []));

        log::info('Creating Stripe checkout session', [
            'amount' => $amount,
            'currency' => $currency,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'product_name' => $productName,
            'customer_email' => $customerEmail,
            'metadata' => $metadata,
        ]);
        $session = Session::create([
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'customer_email' => $customerEmail !== '' ? $customerEmail : null,
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => [
                        'name' => $productName,
                    ],
                    'unit_amount' => (int) round($amount * 100),
                ],
            ]],
            'metadata' => $metadata,
        ]);
        // dd($session);
        return [
            'checkout_url' => $session->url ?? null,
            'session_id' => $session->id ?? null,
            'status' => 'pending',
        ];
    }

    public function retrieveCheckoutSession(string $sessionId): array
    {
        $sessionId = trim($sessionId);
        if ($sessionId === '') {
            throw new Exception('Stripe checkout session id is required.');
        }

        $secretKey = $this->resolveSecretKey();
        if ($secretKey === '') {
            throw new Exception('Stripe secret key is missing. Please configure Stripe payment settings.');
        }

        Stripe::setApiKey($secretKey);

        $session = Session::retrieve($sessionId);

        if (is_object($session) && method_exists($session, 'toArray')) {
            return $session->toArray();
        }

        return is_array($session) ? $session : [];
    }

    public function generateInvoice(array $order, array $request): array
    {
        Log::info('Stripe generateInvoice called', ['order' => $order, 'request' => $request]);
        try {
            $secretKey = $this->resolveSecretKey();
            if (empty($secretKey)) {
                throw new Exception('Stripe secret key is missing.');
            }

            Stripe::setApiKey($secretKey);

            $amount = (float) ($order['total'] ?? $order['amount'] ?? 0);
            if ($amount <= 0) {
                throw new Exception('Invalid invoice amount.');
            }

            $currency = strtolower((string) ($order['currency'] ?? 'usd'));
            $customerEmail = (string) ($request['customer_email'] ?? $order['customer_email'] ?? '');
            $invoiceDescription = (string) ($request['description'] ?? "Invoice for Order");

            $metadata = $this->normalizeMetadata([
                'order_id' => (string) ($order['id'] ?? ''),
                'order_number' => (string) ($order['order_number'] ?? ''),
            ]);

            $invoiceData = [
                'customer_email' => $customerEmail ?: null,
                'description' => $invoiceDescription,
                'metadata' => $metadata,
            ];

            if (isset($request['due_date'])) {
                $invoiceData['due_date'] = strtotime($request['due_date']);
            }

            $invoice = \Stripe\Invoice::create($invoiceData);

            $invoiceItemData = [
                'invoice' => $invoice->id,
                'description' => $invoiceDescription,
                'amount' => (int) round($amount * 100),
                'currency' => $currency,
                'quantity' => 1,
            ];

            \Stripe\InvoiceItem::create($invoiceItemData);

            $finalInvoice = $invoice->finalizeInvoice();

            return [
                'success' => true,
                'method' => 'stripe',
                'transaction_id' => $finalInvoice->id,
                'amount' => $amount,
                'currency' => strtoupper($currency),
                'payment_status' => $finalInvoice->status,
                'public_url' => $finalInvoice->hosted_invoice_url,
                'dueDate' => $request['due_date'] ?? now()->addDays(7)->format('Y-m-d'),
                'errors' => []
            ];
        } catch (Exception $e) {
            Log::error('Stripe invoice generation failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'transaction_id' => null,
                'amount' => $order['total'] ?? 0,
                'currency' => strtoupper($order['currency'] ?? 'usd'),
                'payment_status' => 'failed',
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function cancel()
    {
        return redirect()->route('frontend.index');
    }

    private function resolveSecretKey(): string
    {
        return trim((string) (
            $this->configuration['stripe_secret_key']
            ?? config('services.stripe.secret')
            ?? env('STRIPE_SECRET')
            ?? ''
        ));
    }

    private function normalizeMetadata(array $metadata): array
    {
        $normalized = [];
        foreach ($metadata as $key => $value) {
            if (!is_scalar($value) && $value !== null) {
                continue;
            }
            $normalized[(string) $key] = (string) ($value ?? '');
        }

        return $normalized;
    }

    public function refresh(array $data = [])
    {
        return true;
    }
}
