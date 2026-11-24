@php
    $order = $transaction->order;
    $payment = $payment ?? $transaction->payment;
    $statusEnum = \Modules\Orders\Enums\TransactionStatus::tryFrom($transaction->status ?? '');
    $typeEnum = \Modules\Orders\Enums\TransactionType::tryFrom($transaction->type ?? '');
    $statusLabel = $statusEnum?->label() ?? \Illuminate\Support\Str::headline($transaction->status ?? 'Unknown');
    $typeLabel = $typeEnum?->label() ?? \Illuminate\Support\Str::headline($transaction->type ?? 'Unknown');
    $statusStyle = $statusEnum?->class() ?? 'background-color: #6c757d; color: #ffffff;';
    $typeStyle = $typeEnum?->class() ?? 'background-color: #6c757d; color: #ffffff;';
    $metadata = $transaction->metadata ?? [];
    if (is_string($metadata)) $metadata = json_decode($metadata, true) ?: [];
    $metadata = is_array($metadata) ? $metadata : [];
    $isCancellation = ($transaction->type ?? '') === 'order_cancelled';
    $currency = $order?->currency ?? $transaction->currency ?? 'USD';
    $formatAmount = fn ($amount, $code) => fn_format_currency_amount((float) $amount, $code, true);
    $formatMoney = fn ($amount) => $formatAmount($amount, $currency);
    $paymentLink = $transaction->payment_url;
    $metadata = \Illuminate\Support\Arr::except($metadata, ['transaction_id', 'gateway_response', 'payment_url', 'client_secret', 'configuration', 'collected_by_name', 'collected_by_email']);
    $customer = $order?->customer;
    $vendor = $order?->vendor;
    $addressName = fn ($value) => is_array($value) ? ($value['name'] ?? null) : (is_scalar($value) ? $value : null);
    $delivery = $order?->delivery_address ?? [];
    if (is_string($delivery)) $delivery = json_decode($delivery, true) ?: [];
    $deliveryLines = is_array($delivery) ? array_filter([
        $delivery['address_line_1'] ?? $delivery['address_line1'] ?? null,
        $delivery['address_line_2'] ?? $delivery['address_line2'] ?? null,
        implode(', ', array_filter([$addressName($delivery['city_name'] ?? $delivery['city'] ?? null), $addressName($delivery['state_name'] ?? $delivery['state'] ?? null)])),
        $delivery['zip_code'] ?? $delivery['postal_code'] ?? null,
        $addressName($delivery['country_name'] ?? $delivery['country'] ?? null),
    ], fn ($line) => is_scalar($line) && trim((string) $line) !== '') : [];
    $orderStatus = $order ? fn_get_order_status($order->status, 'O') : null;
    $paymentStatus = $order ? \Modules\Payments\Enums\PaymentStatus::tryFrom($order->payment_status ?? '') : null;
@endphp

@include('orders::orders.components.receipt-styles')

<div class="order-receipt space-y-6">
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-sm text-gray-500 mb-1">Transaction Details</p>
                <h1 class="text-2xl font-bold text-gray-900 break-all">{{ $transaction->transaction_number }}</h1>
                <div class="flex flex-wrap items-center gap-2 mt-3">
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium" style="{{ $typeStyle }}">{{ $typeLabel }}</span>
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium" style="{{ $statusStyle }}">{{ $statusLabel }}</span>
                    @if($transaction->created_at)
                    <span class="text-sm text-gray-500">Created {{ $transaction->created_at->format('M d, Y h:i A') }}</span>
                    @endif
                </div>
            </div>
            <div class="md:text-right">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Transaction Amount</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $transaction->amount !== null ? $formatAmount($transaction->amount, $transaction->currency) : 'Not recorded' }}</p>
            </div>
        </div>
        @if($isCancellation)
        <p class="mt-4 rounded-lg bg-amber-50 border border-amber-200 p-3 text-sm text-amber-800">
            This entry records an order cancellation. The amount shown does not confirm a completed refund.
        </p>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <section class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Transaction Information</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5 text-sm">
                    <div>
                        <dt class="text-gray-500">Order Number</dt>
                        <dd class="font-medium mt-1">
                            @if($order)
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="text-blue-600 hover:underline">{{ $order->order_number }}</a>
                            @else
                            <span class="text-gray-900">Order unavailable</span>
                            @endif
                        </dd>
                    </div>
                    <div><dt class="text-gray-500">Payment Method</dt><dd class="font-medium text-gray-900 mt-1">{{ $paymentMethodName ?? 'Not recorded' }}</dd></div>
                    @if($order)
                    <div><dt class="text-gray-500">Order Type</dt><dd class="font-medium text-gray-900 mt-1">{{ match ($order->order_type) { 'delivery' => 'Delivery', 'takeaway' => 'Pickup', 'dine_in' => 'Dine-in', default => 'Not recorded' } }}</dd></div>
                    @endif
                    <div><dt class="text-gray-500">Gateway Reference</dt><dd class="font-medium text-gray-900 mt-1 break-all">{{ $transaction->gateway_reference ?: 'Not recorded' }}</dd></div>
                    <div><dt class="text-gray-500">Processed At</dt><dd class="font-medium text-gray-900 mt-1">{{ $transaction->processed_at?->format('M d, Y h:i A') ?? 'Not recorded' }}</dd></div>
                    @if($order)
                    <div><dt class="text-gray-500">Order Status</dt><dd class="font-medium text-gray-900 mt-1">{{ $orderStatus->name ?? \Illuminate\Support\Str::headline($order->status) }}</dd></div>
                    <div>
                        <dt class="text-gray-500">Order Payment Status</dt>
                        <dd class="mt-1"><span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium" style="{{ $paymentStatus?->class() ?? $statusStyle }}">{{ $paymentStatus?->label() ?? 'Not recorded' }}</span></dd>
                    </div>
                    @endif
                </dl>
            </section>

            @if($order)
            <section class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Order Items ({{ count($receiptItems ?? []) }})</h2>
                </div>
                <div class="p-6 space-y-4">
                    @forelse($receiptItems ?? [] as $index => $receiptItem)
                        @include('orders::orders.components.item-receipt', [
                            'item' => $receiptItem,
                            'displayOptions' => $optionsByItem[$index] ?? [],
                        ])
                    @empty
                        <p class="text-sm text-gray-500">No order items available.</p>
                    @endforelse
                </div>
            </section>
            @include('orders::orders.components.price-breakdown', [
                'order_summary' => app(\Modules\Orders\Services\OrderPresentationService::class)->pricing($order),
                'order_type' => $order->order_type,
                'coupon_code' => $order->coupon_code,
                'event_guest_count' => $order->event_guest_count,
                'delivery_distance_miles' => $order->delivery_distance_miles,
                'estimated_delivery_minutes' => $order->estimated_delivery_minutes,
            ])
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <section class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">Customer</h2>
                    <p class="font-medium text-gray-900">{{ $customer?->name ?: 'Not recorded' }}</p>
                    <p class="text-sm text-gray-600 mt-2 break-all">{{ $customer?->email ?: 'Email not provided' }}</p>
                    <p class="text-sm text-gray-600 mt-2">{{ $customer?->phone ?: ($order?->phone_number ?: 'Phone not provided') }}</p>
                    @if($order?->order_type === 'delivery')
                        <h3 class="text-sm font-semibold text-gray-900 mt-4 mb-2">Delivery Address</h3>
                        @forelse($deliveryLines as $line)
                            <p class="text-sm text-gray-600">{{ $line }}</p>
                        @empty
                            <p class="text-sm text-gray-500">Not provided</p>
                        @endforelse
                    @endif
                </section>
                <section class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">Restaurant</h2>
                    @if($vendor instanceof \Modules\Restaurant\Models\Restaurant)
                        <a href="{{ route('admin.restaurant.view', $vendor->id) }}" class="receipt-detail-link font-medium">{{ $vendor->name }}</a>
                    @else
                        <p class="font-medium text-gray-900">{{ $vendor?->name ?: 'Not recorded' }}</p>
                    @endif
                    @if($vendor?->email)<p class="text-sm text-gray-600 mt-2 break-all">{{ $vendor->email }}</p>@endif
                    <p class="text-sm text-gray-600 mt-2">{{ $vendor?->phone ?: 'Phone not provided' }}</p>
                    @if($vendor?->address_line1)<p class="text-sm text-gray-600 mt-2">{{ $vendor->address_line1 }} {{ $vendor->address_line2 }}</p>@endif
                    @if($order?->order_type === 'takeaway')
                        <p class="text-sm text-gray-500 mt-4">Customer pickup at the restaurant.</p>
                    @endif
                </section>
            </div>
        </div>

        <aside class="space-y-6">
            <section class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Details</h2>
                <dl class="receipt-rows">
                    <div><dt>Transaction amount</dt><dd>{{ $formatAmount($transaction->amount, $transaction->currency) }}</dd></div>
                    <div><dt>Status</dt><dd>{{ $statusLabel }}</dd></div>
                    @if($payment?->gateway_fee !== null)
                        <div><dt>Gateway fee</dt><dd>{{ $formatAmount($payment->gateway_fee, $payment->currency) }}</dd></div>
                    @endif
                    @if($payment && data_get($payment->details, 'card.last4'))
                        <div><dt>Card</dt><dd>{{ ucfirst(data_get($payment->details, 'card.brand', 'Card')) }} •••• {{ data_get($payment->details, 'card.last4') }}</dd></div>
                    @endif
                </dl>
                @if($paymentLink)
                    <a href="{{ $paymentLink }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center mt-4 px-4 py-3 rounded-lg bg-primary-100 text-white text-sm font-medium">Open Customer Payment Page</a>
                    <p class="text-xs text-gray-500 mt-3">This link continues the payment already created for this order.</p>
                @elseif($transaction->status === 'completed')
                    <p class="text-sm text-green-700 mt-4">This transaction is complete.</p>
                @else
                    <p class="text-sm text-gray-500 mt-4">No active customer payment link.</p>
                @endif
            </section>

            @if($metadata)
            <section class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ $isCancellation ? 'Cancellation Details' : 'Transaction Details' }}</h2>
                <dl class="space-y-4 text-sm">
                    @foreach(\Illuminate\Support\Arr::dot($metadata) as $key => $value)
                    <div>
                        <dt class="text-gray-500">{{ $key === 'refund_amount' ? 'Recorded Refund Amount' : \Illuminate\Support\Str::headline(str_replace('.', ' / ', $key)) }}</dt>
                        <dd class="font-medium text-gray-900 mt-1 break-words">
                            @if($key === 'collected_by')
                                {{ $transaction->collector_label }}
                            @elseif($key === 'refund_amount' && is_numeric($value))
                                {{ $formatAmount($value, $transaction->currency) }}
                            @elseif(is_bool($value))
                                {{ $value ? 'Yes' : 'No' }}
                            @elseif(is_scalar($value) && (string) $value !== '')
                                {{ $key === 'cancelled_by' ? \Illuminate\Support\Str::headline($value) : $value }}
                            @else
                                Not recorded
                            @endif
                        </dd>
                    </div>
                    @endforeach
                </dl>
            </section>
            @endif

            @if($transaction->display_notes)
            <section class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">Notes</h2>
                <p class="text-sm text-gray-700 whitespace-pre-line break-words">{{ $transaction->display_notes }}</p>
            </section>
            @endif

            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 space-y-3">
                @if($order)
                <a href="{{ route('admin.orders.show', $order->id) }}" class="flex items-center justify-center px-4 py-2.5 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700"><i class="fas fa-receipt mr-2"></i>View Order</a>
                @endif
                <a href="{{ route('admin.transactions.index') }}" class="flex items-center justify-center px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50"><i class="fas fa-arrow-left mr-2"></i>Back to Transactions</a>
            </div>
        </aside>
    </div>
</div>
