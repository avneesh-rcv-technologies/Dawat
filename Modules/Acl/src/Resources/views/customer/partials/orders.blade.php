<div class="space-y-6">
    <!-- Order Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-5 border border-purple-200">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
                <span class="text-xs font-medium text-purple-700 bg-purple-200 px-2 py-1 rounded-full">Total</span>
            </div>
            <p class="text-sm font-medium text-purple-900 mb-1">Total Orders</p>
            <p class="text-3xl font-bold text-purple-900">{{ $analytics['orders']['total'] }}</p>
        </div>

        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-5 border border-green-200">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-xs font-medium text-green-700 bg-green-200 px-2 py-1 rounded-full">Success</span>
            </div>
            <p class="text-sm font-medium text-green-900 mb-1">Completed</p>
            <p class="text-3xl font-bold text-green-900">{{ $analytics['orders']['completed'] }}</p>
        </div>

        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg p-5 border border-yellow-200">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-xs font-medium text-yellow-700 bg-yellow-200 px-2 py-1 rounded-full">Active</span>
            </div>
            <p class="text-sm font-medium text-yellow-900 mb-1">Pending</p>
            <p class="text-3xl font-bold text-yellow-900">{{ $analytics['orders']['pending'] }}</p>
        </div>

        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-5 border border-red-200">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <span class="text-xs font-medium text-red-700 bg-red-200 px-2 py-1 rounded-full">Failed</span>
            </div>
            <p class="text-sm font-medium text-red-900 mb-1">Cancelled</p>
            <p class="text-3xl font-bold text-red-900">{{ $analytics['orders']['cancelled'] }}</p>
        </div>
    </div>

    <!-- Customer Behavior Insights -->
    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
        <div class="flex items-center mb-4">
            <svg class="w-5 h-5 text-gray-700 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <h4 class="text-lg font-semibold text-gray-900">Customer Behavior Insights</h4>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-lg p-4 border border-amber-200">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium text-amber-900">Order Frequency</p>
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <p class="text-2xl font-bold text-amber-900">{{ $analytics['behavior']['avg_days_between_orders'] }}</p>
                <p class="text-xs text-amber-700 mt-1">days between orders</p>
            </div>

            <div class="bg-gradient-to-br from-sky-50 to-sky-100 rounded-lg p-4 border border-sky-200">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium text-sky-900">Favorite Time</p>
                    <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p class="text-2xl font-bold text-sky-900">{{ $analytics['behavior']['favorite_hour'] ? str_pad($analytics['behavior']['favorite_hour'], 2, '0', STR_PAD_LEFT) . ':00' : 'N/A' }}</p>
                <p class="text-xs text-sky-700 mt-1">most orders placed</p>
            </div>

            <div class="bg-gradient-to-br from-rose-50 to-rose-100 rounded-lg p-4 border border-rose-200">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium text-rose-900">Favorite Day</p>
                    <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <p class="text-2xl font-bold text-rose-900">{{ $analytics['behavior']['favorite_day'] ?? 'N/A' }}</p>
                <p class="text-xs text-rose-700 mt-1">preferred ordering day</p>
            </div>

            <div class="bg-gradient-to-br from-teal-50 to-teal-100 rounded-lg p-4 border border-teal-200">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium text-teal-900">Avg Order Value</p>
                    <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p class="text-2xl font-bold text-teal-900">{{ $analytics['spending']['average_order_value_formatted'] ?? fn_convert_currency($analytics['spending']['average_order_value'] ?? 0, $analytics['spending']['currency'] ?? fn_get_setting('general.currency', 'USD')) }}</p>
                <p class="text-xs text-teal-700 mt-1">per order</p>
            </div>
        </div>
    </div>

    <!-- Spending Range -->
    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
        <div class="flex items-center mb-4">
            <svg class="w-5 h-5 text-gray-700 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h4 class="text-lg font-semibold text-gray-900">Spending Range</h4>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="text-center p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-lg border border-green-200">
                <p class="text-sm font-medium text-green-900 mb-2">Total Spent</p>
                <p class="text-3xl font-bold text-green-900">{{ $analytics['spending']['total_formatted'] ?? fn_convert_currency($analytics['spending']['total'] ?? 0, $analytics['spending']['currency'] ?? fn_get_setting('general.currency', 'USD')) }}</p>
            </div>
            <div class="text-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg border border-blue-200">
                <p class="text-sm font-medium text-blue-900 mb-2">Highest Order</p>
                <p class="text-3xl font-bold text-blue-900">{{ $analytics['spending']['highest_order_formatted'] ?? fn_convert_currency($analytics['spending']['highest_order'] ?? 0, $analytics['spending']['currency'] ?? fn_get_setting('general.currency', 'USD')) }}</p>
            </div>
            <div class="text-center p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg border border-purple-200">
                <p class="text-sm font-medium text-purple-900 mb-2">Lowest Order</p>
                <p class="text-3xl font-bold text-purple-900">{{ $analytics['spending']['lowest_order_formatted'] ?? fn_convert_currency($analytics['spending']['lowest_order'] ?? 0, $analytics['spending']['currency'] ?? fn_get_setting('general.currency', 'USD')) }}</p>
            </div>
        </div>
    </div>

    <!-- Order Preferences -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Order Type Preferences -->
        @if(!empty($analytics['order_type_preferences']))
        <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
            <div class="flex items-center mb-4">
                <svg class="w-5 h-5 text-gray-700 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <h4 class="text-md font-semibold text-gray-900">Order Type Preferences</h4>
            </div>
            <div class="space-y-3">
                @foreach($analytics['order_type_preferences'] as $type => $count)
                <div class="flex items-center justify-between p-3 bg-gradient-to-r from-indigo-50 to-indigo-100 rounded-lg border border-indigo-200">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-indigo-500 rounded-lg flex items-center justify-center mr-3">
                            @if($type === 'delivery')
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path>
                            </svg>
                            @else
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            @endif
                        </div>
                        <span class="text-sm font-semibold text-indigo-900 capitalize">{{ str_replace('_', ' ', $type) }}</span>
                    </div>
                    <div class="flex items-center">
                        <span class="text-lg font-bold text-indigo-900 mr-2">{{ $count }}</span>
                        <span class="text-xs text-indigo-700">orders</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Payment Method Preferences -->
        @if(!empty($analytics['payment_method_preferences']))
        <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
            <div class="flex items-center mb-4">
                <svg class="w-5 h-5 text-gray-700 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
                <h4 class="text-md font-semibold text-gray-900">Payment Method Preferences</h4>
            </div>
            <div class="space-y-3">
                @foreach($analytics['payment_method_preferences'] as $method => $count)
                <div class="flex items-center justify-between p-3 bg-gradient-to-r from-emerald-50 to-emerald-100 rounded-lg border border-emerald-200">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-emerald-500 rounded-lg flex items-center justify-center mr-3">
                            @if($method === 'card' || $method === 'credit_card')
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                            @else
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            @endif
                        </div>
                        <span class="text-sm font-semibold text-emerald-900 capitalize">{{ str_replace('_', ' ', $method) }}</span>
                    </div>
                    <div class="flex items-center">
                        <span class="text-lg font-bold text-emerald-900 mr-2">{{ $count }}</span>
                        <span class="text-xs text-emerald-700">times</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Monthly Spending Trend -->
    @if(!empty($analytics['monthly_spending_trend']) && count($analytics['monthly_spending_trend']) > 0)
    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
        <div class="flex items-center mb-4">
            <svg class="w-5 h-5 text-gray-700 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
            </svg>
            <h4 class="text-lg font-semibold text-gray-900">Monthly Spending Trend</h4>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Month</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Orders</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Total Spent</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Avg Order</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($analytics['monthly_spending_trend'] as $trend)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            {{ \Carbon\Carbon::parse($trend->month . '-01')->format('F Y') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                {{ $trend->order_count }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-bold text-green-600">
                            {{ fn_convert_currency((float) $trend->total_spent, $analytics['spending']['currency'] ?? fn_get_setting('general.currency', 'USD')) }}
                        </td>
                        <td class="px-4 py-3 text-right text-sm text-gray-900">
                            {{ fn_convert_currency((float) ($trend->order_count > 0 ? $trend->total_spent / $trend->order_count : 0), $analytics['spending']['currency'] ?? fn_get_setting('general.currency', 'USD')) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Recent Orders -->
    @if(!empty($analytics['recent_orders']) && count($analytics['recent_orders']) > 0)
    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-gray-700 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h4 class="text-lg font-semibold text-gray-900">Recent Orders</h4>
            </div>
            <span class="text-xs text-gray-500 bg-gray-100 px-3 py-1 rounded-full">Last 10 orders</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Order #</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Restaurant</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Type</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Total</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($analytics['recent_orders'] as $order)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="text-sm font-mono font-medium text-gray-900">#{{ $order['id'] }}</span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="text-sm text-gray-900">{{ $order['restaurant_name'] }}</span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 capitalize">
                                {{ str_replace('_', ' ', $order['order_type']) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right">
                            <span class="text-sm font-bold text-gray-900">{{ $order['total_formatted'] ?? fn_convert_currency($order['total'] ?? 0, $analytics['spending']['currency'] ?? fn_get_setting('general.currency', 'USD')) }}</span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $order['status'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                            {{ \Carbon\Carbon::parse($order['created_at'])->format('M d, Y H:i') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
