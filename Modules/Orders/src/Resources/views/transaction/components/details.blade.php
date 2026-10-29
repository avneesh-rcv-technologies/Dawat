@php
    $order = $transaction->order;
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
    $formatAmount = fn ($amount, $code) => strtoupper($code) . ' ' . number_format((float) $amount, 2);
    $customer = $order?->customer;
    $vendor = $order?->vendor;
    $billing = $order?->billing_address ?? [];
    if (is_string($billing)) $billing = json_decode($billing, true) ?: ['address' => $billing];
    $billing = is_array($billing) ? $billing : [];
    $addressLines = array_filter([
        $billing['address_line_1'] ?? $billing['address_line1'] ?? $billing['address'] ?? null,
        $billing['address_line_2'] ?? $billing['address_line2'] ?? null,
        implode(', ', array_filter([$billing['city_name'] ?? $billing['city'] ?? null, $billing['state_name'] ?? $billing['state'] ?? null])),
        $billing['zip_code'] ?? $billing['zipcode'] ?? $billing['postal_code'] ?? null,
        $billing['country_name'] ?? $billing['country'] ?? null,
    ], fn ($line) => is_scalar($line) && trim((string) $line) !== '');
    $orderStatus = $order ? fn_get_order_status($order->status, 'O') : null;
    $paymentStatus = $order ? \Modules\Payments\Enums\PaymentStatus::tryFrom($order->payment_status ?? '') : null;
@endphp

<div class="space-y-6">
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
                    <h2 class="text-lg font-semibold text-gray-900">Order Items ({{ $order->items->count() }})</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium">Item</th>
                                <th class="px-4 py-3 text-right font-medium">Quantity</th>
                                <th class="px-4 py-3 text-right font-medium whitespace-nowrap">Unit Price</th>
                                <th class="px-6 py-3 text-right font-medium">Line Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($order->items as $index => $item)
                            <tr class="align-top">
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-gray-900">{{ $item->item_name }}</p>
                                    @if($item->sku)<p class="text-xs text-gray-500 mt-1">SKU: {{ $item->sku }}</p>@endif
                                    @foreach($optionsByItem[$index] ?? [] as $option)
                                    <p class="text-xs text-gray-600 mt-2">{{ $option['group_name'] }}: <span class="font-medium">{{ $option['name'] }}</span>@if($option['quantity'] > 1) × {{ $option['quantity'] }}@endif</p>
                                    @if($option['price'] !== null)
                                    <p class="text-xs text-gray-500">{{ $option['price_recorded'] ? 'Extra price' : 'Current extra price' }}: {{ $formatAmount($option['price'], $currency) }}</p>
                                    @endif
                                    @endforeach
                                    @if($item->special_instructions)<p class="text-xs text-gray-600 mt-2">Instructions: {{ $item->special_instructions }}</p>@endif
                                </td>
                                <td class="px-4 py-4 text-right text-gray-900">{{ $item->quantity }}</td>
                                <td class="px-4 py-4 text-right text-gray-900 whitespace-nowrap">{{ $formatAmount($item->price, $currency) }}</td>
                                <td class="px-6 py-4 text-right font-semibold text-gray-900 whitespace-nowrap">{{ $formatAmount($item->total_price ?? ($item->quantity * $item->price), $currency) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="px-6 py-6 text-center text-gray-500">No order items available</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-200 p-6">
                    <h3 class="font-semibold text-gray-900 mb-3">Order Summary</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-gray-600">Subtotal</dt><dd class="text-gray-900">{{ $formatAmount($order->subtotal, $currency) }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-600">Tax</dt><dd class="text-gray-900">{{ $formatAmount($order->tax, $currency) }}</dd></div>
                        @foreach(['delivery_fee' => 'Delivery Fee', 'service_charge' => 'Service Charge'] as $field => $label)
                        @if($order->{$field} > 0)
                        <div class="flex justify-between gap-4"><dt class="text-gray-600">{{ $label }}</dt><dd class="text-gray-900">{{ $formatAmount($order->{$field}, $currency) }}</dd></div>
                        @endif
                        @endforeach
                        @if($order->discount > 0)
                        <div class="flex justify-between gap-4"><dt class="text-gray-600">Discount</dt><dd class="text-green-700">−{{ $formatAmount($order->discount, $currency) }}</dd></div>
                        @endif
                        <div class="flex justify-between gap-4 border-t border-gray-200 pt-3 font-semibold text-gray-900"><dt>Order Total</dt><dd>{{ $formatAmount($order->total, $currency) }}</dd></div>
                    </dl>
                </div>
            </section>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <section class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">Customer</h2>
                    <p class="font-medium text-gray-900">{{ $customer?->name ?: 'Not recorded' }}</p>
                    <p class="text-sm text-gray-600 mt-2 break-all">{{ $customer?->email ?: 'Email not provided' }}</p>
                    <p class="text-sm text-gray-600 mt-2">{{ $customer?->phone ?: ($order?->phone_number ?: 'Phone not provided') }}</p>
                    <h3 class="text-sm font-semibold text-gray-900 mt-4 mb-2">Billing Address</h3>
                    @forelse($addressLines as $line)
                    <p class="text-sm text-gray-600">{{ $line }}</p>
                    @empty
                    <p class="text-sm text-gray-500">Not provided</p>
                    @endforelse
                </section>
                <section class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">Restaurant</h2>
                    <p class="font-medium text-gray-900">{{ $vendor?->name ?: 'Not recorded' }}</p>
                    @if($vendor?->email)<p class="text-sm text-gray-600 mt-2 break-all">{{ $vendor->email }}</p>@endif
                    <p class="text-sm text-gray-600 mt-2">{{ $vendor?->phone ?: 'Phone not provided' }}</p>
                    @if($vendor?->address_line1)<p class="text-sm text-gray-600 mt-2">{{ $vendor->address_line1 }} {{ $vendor->address_line2 }}</p>@endif
                </section>
            </div>
        </div>

        <aside class="space-y-6">
            @if($metadata)
            <section class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ $isCancellation ? 'Cancellation Details' : 'Transaction Details' }}</h2>
                <dl class="space-y-4 text-sm">
                    @foreach(\Illuminate\Support\Arr::dot($metadata) as $key => $value)
                    <div>
                        <dt class="text-gray-500">{{ $key === 'refund_amount' ? 'Recorded Refund Amount' : \Illuminate\Support\Str::headline(str_replace('.', ' / ', $key)) }}</dt>
                        <dd class="font-medium text-gray-900 mt-1 break-words">
                            @if($key === 'refund_amount' && is_numeric($value))
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
