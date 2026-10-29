@php
$statusInfo = fn_get_order_status($status ?? 'W', 'O');
$statusColor = $statusInfo->color ?? '#6B7280';
$isStoppedOrder = in_array($status ?? 'W', ['C', 'F', 'R'], true);
$isFinishedOrder = $isStoppedOrder || ($status ?? 'W') === 'D';

// Get payment status info
$paymentStatusInfo = \Modules\Payments\Enums\PaymentStatus::tryFrom($payment_status ?? \Modules\Payments\Enums\PaymentStatus::PENDING->value);
$paymentStatusLabel = $paymentStatusInfo?->label() ?? \Illuminate\Support\Str::headline($payment_status ?? 'Unknown');
$paymentStatusStyle = $paymentStatusInfo?->class() ?? 'background-color: #6B7280; color: #ffffff;';
$optionsByItem = app(\Modules\Orders\Services\OrderItemOptionsService::class)->forItems($order_items ?? []);
@endphp

<div class="mx-auto">
    <!-- Order Header -->
    <div class="bg-white rounded-lg shadow-sm mb-6 p-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-2xl font-bold text-gray-900">Order #{{ $order_number ?? 'New Order' }}</h1>
                    <span class="px-3 py-1 rounded-full text-xs font-medium"
                        style="background-color: {{ $statusColor }}20; color: {{ $statusColor }}; border: 1px solid {{ $statusColor }};">
                        {{ $statusInfo->name ?? 'Pending' }}
                    </span>
                </div>
                <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                    @if(isset($created_at))
                    <span class="flex items-center">
                        <i class="fas fa-calendar mr-1"></i>
                        {{ \Carbon\Carbon::parse($created_at)->format('M d, Y h:i A') }}
                    </span>
                    @endif
                    @if(!empty($order_type))
                    <span class="flex items-center">
                        <i class="fas fa-truck mr-1"></i>
                        {{ ucfirst(str_replace('_', ' ', $order_type)) }}
                    </span>
                    @endif
                    @if(!empty($payment_status))
                    <span class="flex items-center">
                        <i class="fas fa-credit-card mr-1"></i>
                        Payment:
                        <span class="ml-1 px-2 py-0.5 rounded-full text-xs font-medium"
                            style="{{ $paymentStatusStyle }}">
                            {{ $paymentStatusLabel }}
                        </span>
                    </span>
                    @endif
                </div>
            </div>
            @if($mode === 'create' || $mode === 'edit')
            <x-modal buttonText='<i class="fas fa-plus"></i> Add Item' type='link' modalTitle="Add To Cart"
                id="add_to_cart" ajaxUrl="{{route('api.cart.item.view', $order_id ?? 0)}}"
                buttonClass="px-4 py-2 bg-primary-100 text-white rounded-lg text-sm hover:bg-primary-200 flex items-center gap-2 transition-colors"
                modalSize="3xl" />
            @endif
        </div>
    </div>

    @if($mode === 'create' || $mode === 'edit')
    <form id="order_idForm" class="form-ajax" method="POST"
        action="@if(!empty($order_id) && $mode != 'create') {{ route('admin.orders.update', $order_id) }} @else {{ route('admin.orders.store') }} @endif"
        enctype="multipart/form-data">
        @csrf
        @if(!empty($order_id) && $mode != 'create') @method('PUT') @endif
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content Area -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Order Items Section -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-shopping-cart mr-2"></i>
                            Order Items ({{ count($order_items ?? []) }})
                        </h2>
                    </div>
                    <div class="p-6">
                        @if(!empty($order_items) && count($order_items) > 0)
                        <div class="space-y-4">
                            @foreach($order_items as $itemIndex => $item)
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                <div class="flex gap-4">
                                    <!-- Item Image -->
                                    <div class="flex-shrink-0">
                                        @if(!empty($item['item_image']) || !empty($item['food_item_image']))
                                        <img src="{{ $item['item_image'] ?? $item['food_item_image'] }}"
                                            alt="{{ $item['item_name'] ?? $item['food_item_name'] }}"
                                            class="w-20 h-20 object-cover rounded-lg">
                                        @else
                                        <div class="w-20 h-20 bg-gray-200 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-utensils text-gray-400 text-2xl"></i>
                                        </div>
                                        @endif
                                    </div>

                                    <!-- Item Details -->
                                    <div class="flex-1">
                                        <div class="flex justify-between items-start mb-2">
                                            <div>
                                                <h3 class="font-semibold text-gray-900">
                                                    {{ $item['item_name'] ?? $item['food_item_name'] }}
                                                </h3>
                                                @if(!empty($item['sku']))
                                                <p class="text-xs text-gray-500">SKU: {{ $item['sku'] }}</p>
                                                @endif
                                            </div>
                                            <div class="text-right">
                                                <p class="font-semibold text-gray-900">
                                                    {{ fn_convert_currency($item['total_price'] ?? ($item['price'] *
                                                    $item['quantity']), $currency ?? 'USD') }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Price and Quantity -->
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-4 text-sm mb-2">
                                            <div>
                                                <span class="text-gray-600">Unit Price:</span>
                                                <span class="font-medium text-gray-900 ml-1">
                                                    {{ fn_convert_currency($item['price'], $currency ?? 'USD') }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="text-gray-600">Quantity:</span>
                                                <span class="font-medium text-gray-900 ml-1">{{ $item['quantity']
                                                    }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-600">Tax Rate:</span>
                                                <span class="font-medium text-gray-900 ml-1">{{ $item['tax_rate'] ?? 0
                                                    }}%</span>
                                            </div>
                                        </div>

                                        @include('orders::orders.components.selected_options', ['displayOptions' => $optionsByItem[$itemIndex] ?? []])

                                        <!-- Special Instructions -->
                                        @if(!empty($item['special_instructions']))
                                        <div class="mt-2 p-2 bg-yellow-50 rounded border border-yellow-200">
                                            <p class="text-xs text-gray-600">
                                                <i class="fas fa-sticky-note mr-1"></i>
                                                <span class="font-medium">Instructions:</span> {{
                                                $item['special_instructions'] }}
                                            </p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-shopping-cart text-4xl mb-2"></i>
                            <p>No items in this order</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-medium text-gray-900">{{ fn_convert_currency($order_summary['subtotal'] ??
                                $subtotal ?? 0, $currency ?? 'USD') }}</span>
                        </div>
                        @if((!empty($order_summary['discount']) && $order_summary['discount'] > 0) || (!empty($discount)
                        && $discount > 0))
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Discount:</span>
                            <span class="font-medium text-green-600">-{{ fn_convert_currency($order_summary['discount']
                                ?? $discount, $currency ?? 'USD') }}</span>
                        </div>
                        @if(!empty($coupon_code))
                        <div class="text-xs text-gray-500 ml-4">
                            Coupon: <span class="font-medium">{{ $coupon_code }}</span>
                        </div>
                        @endif
                        @endif
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Tax:</span>
                            <span class="font-medium text-gray-900">{{ fn_convert_currency($order_summary['tax'] ??
                                $tax ?? 0, $currency ?? 'USD') }}</span>
                        </div>
                        @if((!empty($order_summary['delivery_fee']) && $order_summary['delivery_fee'] > 0) ||
                        (!empty($delivery_fee) && $delivery_fee > 0))
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Delivery Fee:</span>
                            <span class="font-medium text-gray-900">{{
                                fn_convert_currency($order_summary['delivery_fee'] ?? $delivery_fee, $currency ?? 'USD')
                                }}</span>
                        </div>
                        @endif
                        @if((!empty($order_summary['service_charge']) && $order_summary['service_charge'] > 0) ||
                        (!empty($service_charge) && $service_charge > 0))
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Service Charge:</span>
                            <span class="font-medium text-gray-900">{{
                                fn_convert_currency($order_summary['service_charge'] ?? $service_charge, $currency ??
                                'USD') }}</span>
                        </div>
                        @endif
                        <div class="border-t border-gray-200 pt-2 mt-2">
                            <div class="flex justify-between">
                                <span class="text-base font-semibold text-gray-900">Total:</span>
                                <span class="text-lg font-bold text-gray-900">{{
                                    fn_convert_currency($order_summary['total'] ?? $total ?? 0, $currency ?? 'USD')
                                    }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                @include('orders::orders.components.timeline')

                <!-- Transactions Section -->
                @if($mode === 'view' && !empty($transactions) && count($transactions) > 0)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-exchange-alt mr-2 text-primary-100"></i>
                        Transactions ({{ count($transactions) }})
                    </h3>
                    <div class="space-y-3">
                        @foreach($transactions as $transaction)
                        @php
                        $transStatusInfo = fn_get_order_status($transaction['status'] ?? 'pending', 'T');
                        $transStatusColor = $transStatusInfo->color ?? '#6B7280';

                        // Type colors
                        $typeColors = [
                        'payment' => '#10B981',
                        'refund' => '#EF4444',
                        'adjustment' => '#F59E0B',
                        ];
                        $typeColor = $typeColors[$transaction['type']] ?? '#6B7280';
                        @endphp
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-semibold text-gray-900">
                                            {{ $transaction['transaction_number'] ?? 'TXN-' . $transaction['id'] }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                                            style="background-color: {{ $typeColor }}20; color: {{ $typeColor }}; border: 1px solid {{ $typeColor }};">
                                            {{ ucfirst($transaction['type']) }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                                            style="background-color: {{ $transStatusColor }}20; color: {{ $transStatusColor }}; border: 1px solid {{ $transStatusColor }};">
                                            {{ $transStatusInfo->name ?? ucfirst($transaction['status']) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        <i class="fas fa-calendar mr-1"></i>
                                        {{ \Carbon\Carbon::parse($transaction['created_at'])->format('M d, Y h:i A') }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold" style="color: {{ $typeColor }};">
                                        {{ $transaction['type'] === 'refund' ? '-' : '' }}{{
                                        fn_convert_currency($transaction['amount'], $transaction['currency'] ??
                                        $currency ?? 'USD') }}
                                    </p>
                                </div>
                            </div>

                            @if(!empty($transaction['notes']))
                            <div class="mt-2 p-2 bg-gray-50 rounded text-xs text-gray-600">
                                <i class="fas fa-sticky-note mr-1"></i>
                                {{ $transaction['notes'] }}
                            </div>
                            @endif

                            @if(!empty($transaction['gateway_reference']))
                            <div class="mt-2 text-xs text-gray-500">
                                <span class="font-medium">Gateway Ref:</span> {{ $transaction['gateway_reference'] }}
                            </div>
                            @endif

                            @if(!empty($transaction['processed_at']))
                            <div class="mt-1 text-xs text-gray-500">
                                <i class="fas fa-check-circle mr-1 text-green-500"></i>
                                Processed: {{ \Carbon\Carbon::parse($transaction['processed_at'])->format('M d, Y h:i
                                A') }}
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Right Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Order Status Card -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-primary-100"></i>
                        Order Status
                    </h3>
                    @if($mode === 'view')
                    @php
                        $currentStatusCode = $status ?? 'W';
                        $allowedStatusCodes = app(\Modules\Orders\Services\OrderService::class)
                            ->getAllowedStatusesForOrder($currentStatusCode, $order_type ?? 'delivery');
                        $availableStatuses = fn_get_order_statuses('O')
                            ->whereIn('status_code', $allowedStatusCodes)
                            ->sortBy(fn ($orderStatus) => array_search($orderStatus->status_code, $allowedStatusCodes, true));
                        $canUpdateStatus = $availableStatuses->contains(fn ($orderStatus) => $orderStatus->status_code !== $currentStatusCode);
                    @endphp

                    @if($canUpdateStatus)
                    <form action="{{ route('admin.orders.update-status', $order_id ?? 0) }}" method="POST"
                        id="status-update-form" class="form-ajax">

                        @csrf
                        <div class="relative mb-3">
                            <select name="status" id="order_status"
                                @disabled(!$canUpdateStatus)
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-100 focus:border-primary-100 bg-white">
                                @foreach($availableStatuses as $orderStatus)
                                <option value="{{ $orderStatus->status_code }}" @selected($currentStatusCode === $orderStatus->status_code)>
                                    {{ $orderStatus->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit"
                            @disabled(!$canUpdateStatus)
                            class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-save mr-2"></i>Update Status
                        </button>
                    </form>
                    <p class="text-xs text-gray-500 mt-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Select an available status, then click Update.
                    </p>
                    @else
                    <div class="px-3 py-2 rounded-lg text-sm font-medium"
                        style="background-color: {{ $statusColor }}20; color: {{ $statusColor }};">
                        {{ $statusInfo->name ?? $currentStatusCode }}
                    </div>
                    <p class="text-xs text-gray-500 mt-2">This order is closed. No further status updates are available.</p>
                    @endif
                    @else
                    <div class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-700">
                        {{ $statusInfo->name ?? 'Pending' }}
                    </div>
                    @endif

                    @if(!empty($updated_at))
                    <div class="mt-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <p class="text-xs font-medium text-gray-700 mb-1">Last Updated</p>
                        <p class="text-sm font-semibold text-gray-900">
                            {{ \Carbon\Carbon::parse($updated_at)->format('M d, Y h:i A') }}
                        </p>
                    </div>
                    @endif

                    <!-- Additional Status Info -->
                    @if(!$isFinishedOrder && !empty($estimated_delivery_time))
                    <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                        <p class="text-xs font-medium text-gray-700 mb-1">{{ ($order_type ?? 'delivery') === 'delivery' ? 'Estimated Delivery' : 'Estimated Ready Time' }}</p>
                        <p class="text-sm font-semibold text-gray-900">
                            {{ \Carbon\Carbon::parse($estimated_delivery_time)->format('M d, Y h:i A') }}
                        </p>
                    </div>
                    @endif

                    @if(!empty($actual_delivery_time))
                    <div class="mt-2 p-3 bg-green-50 rounded-lg">
                        <p class="text-xs font-medium text-gray-700 mb-1">Delivered At</p>
                        <p class="text-sm font-semibold text-gray-900">
                            {{ \Carbon\Carbon::parse($actual_delivery_time)->format('M d, Y h:i A') }}
                        </p>
                    </div>
                    @endif
                    @if($isStoppedOrder && !empty($cancellation_reason))
                    <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                        <p class="text-xs font-medium text-gray-700 mb-1">Reason</p>
                        <p class="text-sm text-gray-900">{{ $cancellation_reason }}</p>
                    </div>
                    @endif

                    @if(!empty($stock_restored_at))
                    <div class="mt-3 p-3 bg-green-50 rounded-lg">
                        <p class="text-sm font-medium text-green-800">Reserved stock returned</p>
                        <p class="text-xs text-gray-600 mt-1">{{ \Carbon\Carbon::parse($stock_restored_at)->format('M d, Y h:i A') }}</p>
                    </div>
                    @endif
                </div>

                <!-- Payment Information Card -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-credit-card mr-2 text-primary-100"></i>
                        Payment Information
                    </h3>

                    @if(!empty($payment_method))
                    <div class="mb-3">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-600">Payment Method:</span>
                            @php
                            $methodNameLower = strtolower(fn_get_payment_method_code($payment_method) ?? ($payment_method_name ?? ''));
                            $isCod = str_contains($methodNameLower, 'cash') || str_contains($methodNameLower, 'cod') ||
                            str_contains($methodNameLower, 'delivery');
                            @endphp
                            @if($isCod)
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                <i class="fas fa-money-bill-wave mr-1"></i>
                                {{ fn_get_payment_method_code($payment_method) ?? ($payment_method_name ?? 'N/A') }}
                            </span>
                            @else
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-credit-card mr-1"></i>
                                {{ fn_get_payment_method_code($payment_method) ?? ($payment_method_name ?? 'N/A') }}
                            </span>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if(!empty($payment_status))
                    <div class="mb-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-600">Payment Status:</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                style="{{ $paymentStatusStyle }}">
                                {{ $paymentStatusLabel }}
                            </span>
                        </div>
                    </div>
                    @endif

                    @if($isStoppedOrder && $paymentStatusInfo === \Modules\Payments\Enums\PaymentStatus::PAID)
                    <p class="mb-3 p-3 rounded-lg bg-amber-50 text-sm text-amber-800">
                        Payment remains marked as paid for this closed order.
                    </p>
                    @endif

                    <div id="payment-detail-action">
                        @if(!empty($payment_details))
                        @include('orders::orders.components.payments.detail')
                        @else
                        <p class="text-gray-500 text-sm">No payment transaction details available</p>
                        @endif
                    </div>
                </div>

                <!-- Customer Details Card -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-user mr-2 text-primary-100"></i>
                        Customer Details
                    </h3>
                    @if(!empty($customer_details))
                    <div class="space-y-2 text-sm">
                        <div>
                            <span class="font-medium text-gray-700">Name:</span>
                            <span class="text-gray-900">{{ ($customer_details['name'] ?? '') ?: 'Not provided' }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Email:</span>
                            <span class="text-gray-900 break-all">{{ ($customer_details['email'] ?? '') ?: 'Not provided' }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Phone:</span>
                            <span class="text-gray-900">{{ ($customer_details['phone'] ?? '') ?: ($phone_number ?? '') ?: 'Not provided' }}</span>
                        </div>
                    </div>
                    @elseif(!empty($order) && !empty($order->customer))
                    <div class="space-y-2 text-sm">
                        <div>
                            <span class="font-medium text-gray-700">Name:</span>
                            <span class="text-gray-900">{{ $order->customer->name ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Email:</span>
                            <span class="text-gray-900">{{ $order->customer->email ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Phone:</span>
                            <span class="text-gray-900">{{ ($order->customer->phone ?? '') ?: ($phone_number ?? '') ?: 'Not provided' }}</span>
                        </div>
                    </div>
                    @else
                    <p class="text-sm text-gray-500">No customer information available</p>
                    @endif
                </div>

                <!-- Restaurant Details Card -->
                @if(!empty($restaurant_details))
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-store mr-2 text-primary-100"></i>
                        Restaurant Details
                    </h3>
                    <div class="space-y-2 text-sm">
                        <div>
                            <span class="font-medium text-gray-700">Name:</span>
                            <span class="text-gray-900">{{ $restaurant_details['name'] ?? 'N/A' }}</span>
                        </div>
                        @if(!empty($restaurant_details['phone']))
                        <div>
                            <span class="font-medium text-gray-700">Phone:</span>
                            <span class="text-gray-900">{{ $restaurant_details['phone'] }}</span>
                        </div>
                        @endif
                        @if(!empty($restaurant_details['address']))
                        <div>
                            <span class="font-medium text-gray-700">Address:</span>
                            <span class="text-gray-900">{{ $restaurant_details['address'] }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                @elseif(!empty($order) && !empty($order->vendor))
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-store mr-2 text-primary-100"></i>
                        Restaurant Details
                    </h3>
                    <div class="space-y-2 text-sm">
                        <div>
                            <span class="font-medium text-gray-700">Name:</span>
                            <span class="text-gray-900">{{ $order->vendor->name ?? 'N/A' }}</span>
                        </div>
                        @if(!empty($order->vendor->phone))
                        <div>
                            <span class="font-medium text-gray-700">Phone:</span>
                            <span class="text-gray-900">{{ $order->vendor->phone }}</span>
                        </div>
                        @endif
                        @if(!empty($order->vendor->address_line1))
                        <div>
                            <span class="font-medium text-gray-700">Address:</span>
                            <span class="text-gray-900">{{ $order->vendor->address_line1 }} {{
                                $order->vendor->address_line2 ?? '' }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Billing Address Card -->
                @if(!empty($billing_address))
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-file-invoice mr-2 text-primary-100"></i>
                        Billing Address
                    </h3>
                    <div class="text-sm text-gray-700">
                        @include('orders::orders.components.customer.billing_address')
                    </div>
                </div>
                @endif

                <!-- Shipping/Delivery Address Card -->
                @if(!empty($delivery_address))
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-map-marker-alt mr-2 text-primary-100"></i>
                        Delivery Address
                    </h3>
                    <div class="text-sm text-gray-700 space-y-1">
                        @if(is_array($delivery_address))
                        @if(!empty($delivery_address['address_line_1']))
                        <p>{{ $delivery_address['address_line_1'] }}</p>
                        @endif
                        @if(!empty($delivery_address['address_line_2']))
                        <p>{{ $delivery_address['address_line_2'] }}</p>
                        @endif
                        @if(!empty($delivery_address['city_name']) || !empty($delivery_address['state_id']) ||
                        !empty($delivery_address['zip_code']))
                        <p>
                            {{ $delivery_address['city_name'] ?? '' }}
                            @if(!empty($delivery_address['state_id'])), {{ $delivery_address['state_id'] }}@endif
                            @if(!empty($delivery_address['zip_code'])) {{ $delivery_address['zip_code'] }}@endif
                        </p>
                        @endif
                        @if(!empty($delivery_address['country']))
                        <p>{{ $delivery_address['country'] }}</p>
                        @endif
                        @else
                        <p>{{ $delivery_address }}</p>
                        @endif
                    </div>

                    @if(!empty($delivery_instructions))
                    <div class="mt-3 p-2 bg-yellow-50 rounded border border-yellow-200">
                        <p class="text-xs font-medium text-gray-700 mb-1">Delivery Instructions:</p>
                        <p class="text-xs text-gray-600">{{ $delivery_instructions }}</p>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Order Notes -->
                @if(!empty($order_note) || !empty($notes))
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-sticky-note mr-2 text-primary-100"></i>
                        Order Notes
                    </h3>
                    <div class="text-sm text-gray-700 bg-gray-50 p-3 rounded">
                        {{ $order_note ?? $notes }}
                    </div>
                </div>
                @endif

                <!-- IP & User Agent (for admin reference) -->
                @if($mode === 'view' && (!empty($ip_address) || !empty($user_agent)))
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-desktop mr-2 text-primary-100"></i>
                        Technical Details
                    </h3>
                    <div class="space-y-2 text-xs text-gray-600">
                        @if(!empty($ip_address))
                        <div>
                            <span class="font-medium">IP Address:</span>
                            <span class="ml-1">{{ $ip_address }}</span>
                        </div>
                        @endif
                        @if(!empty($user_agent))
                        <div>
                            <span class="font-medium">User Agent:</span>
                            <span class="ml-1 break-all">{{ $user_agent }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
        @if($mode === 'create' || $mode === 'edit')
    </form>
    @endif
</div>


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Order detail page scripts can go here if needed
    });
</script>
@endpush
