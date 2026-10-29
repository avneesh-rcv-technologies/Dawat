<div class="space-y-6">
    <!-- Orders Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-5 border border-blue-200">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
                <span class="text-xs font-medium text-blue-700 bg-blue-200 px-2 py-1 rounded-full">Total</span>
            </div>
            <p class="text-sm font-medium text-blue-900 mb-1">Total Orders</p>
            <p class="text-3xl font-bold text-blue-900">{{ $analytics['orders']['total'] }}</p>
        </div>

        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-5 border border-green-200">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-xs font-medium text-green-700 bg-green-200 px-2 py-1 rounded-full">{{
                    $analytics['orders']['completion_rate'] }}%</span>
            </div>
            <p class="text-sm font-medium text-green-900 mb-1">Completed Orders</p>
            <p class="text-3xl font-bold text-green-900">{{ $analytics['orders']['completed'] }}</p>
        </div>

        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg p-5 border border-yellow-200">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-xs font-medium text-yellow-700 bg-yellow-200 px-2 py-1 rounded-full">Active</span>
            </div>
            <p class="text-sm font-medium text-yellow-900 mb-1">Pending Orders</p>
            <p class="text-3xl font-bold text-yellow-900">{{ $analytics['orders']['pending'] }}</p>
        </div>
    </div>

    <!-- Order Status Breakdown -->
    @if(!empty($analytics['orders']['by_status']))
    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
        <div class="flex items-center mb-4">
            <svg class="w-5 h-5 text-gray-700 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                </path>
            </svg>
            <h4 class="text-lg font-semibold text-gray-900">Orders by Status</h4>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($analytics['orders']['by_status'] as $statusCode => $statusData)
            @php
            // Use color from database or default colors
            $bgColor = 'bg-gray-50';
            $textColor = 'text-gray-900';
            $borderColor = 'border-gray-200';
            $iconColor = 'text-gray-600';

            if(isset($statusData['color'])) {
            // Convert hex color to Tailwind-like classes (simplified)
            $color = strtolower($statusData['color']);
            if(strpos($color, 'yellow') !== false || strpos($color, '#fbbf24') !== false) {
            $bgColor = 'bg-yellow-50';
            $textColor = 'text-yellow-900';
            $borderColor = 'border-yellow-200';
            $iconColor = 'text-yellow-600';
            } elseif(strpos($color, 'blue') !== false || strpos($color, '#3b82f6') !== false) {
            $bgColor = 'bg-blue-50';
            $textColor = 'text-blue-900';
            $borderColor = 'border-blue-200';
            $iconColor = 'text-blue-600';
            } elseif(strpos($color, 'green') !== false || strpos($color, '#10b981') !== false) {
            $bgColor = 'bg-green-50';
            $textColor = 'text-green-900';
            $borderColor = 'border-green-200';
            $iconColor = 'text-green-600';
            } elseif(strpos($color, 'red') !== false || strpos($color, '#ef4444') !== false) {
            $bgColor = 'bg-red-50';
            $textColor = 'text-red-900';
            $borderColor = 'border-red-200';
            $iconColor = 'text-red-600';
            } elseif(strpos($color, 'purple') !== false || strpos($color, '#8b5cf6') !== false) {
            $bgColor = 'bg-purple-50';
            $textColor = 'text-purple-900';
            $borderColor = 'border-purple-200';
            $iconColor = 'text-purple-600';
            }
            }
            @endphp
            <div class="{{$bgColor}} rounded-lg p-4 border {{$borderColor}}">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-medium {{$textColor}} uppercase tracking-wide">{{ $statusData['name'] }}</p>
                    <span class="text-xs {{$iconColor}} font-mono bg-white px-2 py-0.5 rounded">{{ $statusData['code']
                        }}</span>
                </div>
                <p class="text-2xl font-bold {{$textColor}}">{{ $statusData['count'] }}</p>
                @if($analytics['orders']['total'] > 0)
                <p class="text-xs {{$iconColor}} mt-1">{{ round(($statusData['count'] / $analytics['orders']['total']) *
                    100, 1) }}% of total</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Revenue Analytics -->
    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
        <div class="flex items-center mb-4">
            <svg class="w-5 h-5 text-gray-700 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                </path>
            </svg>
            <h4 class="text-lg font-semibold text-gray-900">Revenue Analytics</h4>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-lg p-5 border border-emerald-200">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium text-emerald-900">Total Revenue</p>
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-emerald-900">{{ $analytics['revenue']['total_formatted'] ??
                    fn_convert_currency($analytics['revenue']['total'] ?? 0, $analytics['revenue']['currency'] ??
                    fn_get_setting('general.currency', 'USD')) }}</p>
                <p class="text-xs text-emerald-700 mt-2">From {{ $analytics['orders']['completed'] }} completed orders
                </p>
            </div>

            <div class="bg-gradient-to-br from-cyan-50 to-cyan-100 rounded-lg p-5 border border-cyan-200">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium text-cyan-900">Average Order Value</p>
                    <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                        </path>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-cyan-900">{{ $analytics['revenue']['average_order_value_formatted'] ??
                    fn_convert_currency($analytics['revenue']['average_order_value'] ?? 0,
                    $analytics['revenue']['currency'] ?? fn_get_setting('general.currency', 'USD')) }}</p>
                <p class="text-xs text-cyan-700 mt-2">Per completed order</p>
            </div>

            <div class="bg-gradient-to-br from-violet-50 to-violet-100 rounded-lg p-5 border border-violet-200">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium text-violet-900">Highest Order</p>
                    <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z">
                        </path>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-violet-900">{{ $analytics['revenue']['highest_order_formatted'] ??
                    fn_convert_currency($analytics['revenue']['highest_order'] ?? 0, $analytics['revenue']['currency']
                    ?? fn_get_setting('general.currency', 'USD')) }}</p>
                <p class="text-xs text-violet-700 mt-2">Maximum single order value</p>
            </div>
        </div>

        <!-- Additional Revenue Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div class="bg-gradient-to-br from-rose-50 to-rose-100 rounded-lg p-4 border border-rose-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-rose-900">Lowest Order</p>
                        <p class="text-2xl font-bold text-rose-900 mt-1">{{
                            $analytics['revenue']['lowest_order_formatted'] ??
                            fn_convert_currency($analytics['revenue']['lowest_order'] ?? 0,
                            $analytics['revenue']['currency'] ?? fn_get_setting('general.currency', 'USD')) }}</p>
                    </div>
                    <svg class="w-8 h-8 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                    </svg>
                </div>
            </div>

            <div class="bg-gradient-to-br from-sky-50 to-sky-100 rounded-lg p-4 border border-sky-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-sky-900">Avg Processing Time</p>
                        <p class="text-2xl font-bold text-sky-900 mt-1">{{ $analytics['orders']['avg_processing_time']
                            }} min</p>
                    </div>
                    <svg class="w-8 h-8 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Busiest Days of Week -->
    @if(!empty($analytics['orders']['busiest_days']) && $analytics['orders']['busiest_days']->count() > 0)
    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
        <div class="flex items-center mb-4">
            <svg class="w-5 h-5 text-gray-700 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <h4 class="text-lg font-semibold text-gray-900">Busiest Days of Week</h4>
            <span class="ml-auto text-xs text-gray-500">Orders and revenue by day</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-7 gap-3">
            @foreach($analytics['orders']['busiest_days'] as $dayData)
            @php
            $maxCount = $analytics['orders']['busiest_days']->max('count');
            $heightPercent = $maxCount > 0 ? ($dayData->count / $maxCount) * 100 : 0;
            @endphp
            <div class="text-center">
                <div class="bg-gradient-to-t from-blue-500 to-blue-300 rounded-t-lg mx-auto transition-all hover:from-blue-600 hover:to-blue-400"
                    style="height: {{ max($heightPercent, 20) }}px; width: 100%; max-height: 150px;">
                </div>
                <div class="mt-2 p-3 bg-blue-50 rounded-lg border border-blue-200">
                    <p class="text-xs font-bold text-blue-900">{{ substr($dayData->day ?? 'N/A', 0, 3) }}</p>
                    <p class="text-lg font-bold text-blue-900 mt-1">{{ $dayData->count }}</p>
                    <p class="text-xs text-blue-700">{{ fn_convert_currency((float) $dayData->revenue,
                        $analytics['revenue']['currency'] ?? fn_get_setting('general.currency', 'USD')) }}</p>
                </div>
            </div>
            @endforeach
        </div>
        @if($analytics['orders']['busiest_days']->first() && $analytics['orders']['busiest_days']->first()->day)
        <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
            <p class="text-sm text-blue-900">
                <svg class="w-4 h-4 inline mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <strong>Insight:</strong> {{ $analytics['orders']['busiest_days']->first()->day }} is your busiest day
                with {{ $analytics['orders']['busiest_days']->first()->count }} orders.
            </p>
        </div>
        @endif
    </div>
    @endif

    <!-- Peak Hours -->
    @if(!empty($analytics['orders']['peak_hours']))
    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
        <div class="flex items-center mb-4">
            <svg class="w-5 h-5 text-gray-700 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h4 class="text-lg font-semibold text-gray-900">Peak Order Hours</h4>
            <span class="ml-auto text-xs text-gray-500">Top 6 busiest hours</span>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
            @foreach($analytics['orders']['peak_hours'] as $hour => $count)
            <div
                class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4 border border-orange-200 text-center">
                <div class="flex items-center justify-center mb-2">
                    <svg class="w-4 h-4 text-orange-600 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm font-bold text-orange-900">{{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}:00</p>
                </div>
                <p class="text-2xl font-bold text-orange-900">{{ $count }}</p>
                <p class="text-xs text-orange-700 mt-1">orders</p>
            </div>
            @endforeach
        </div>
        <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
            <p class="text-sm text-blue-900">
                <svg class="w-4 h-4 inline mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <strong>Tip:</strong> These are your busiest hours. Consider having more staff during these times to
                handle the order volume efficiently.
            </p>
        </div>
    </div>
    @endif

    <!-- Order Performance Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Completion Rate -->
        <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-md font-semibold text-gray-900">Order Completion Rate</h4>
                <span class="text-2xl font-bold text-green-600">{{ $analytics['orders']['completion_rate'] }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                <div class="bg-gradient-to-r from-green-400 to-green-600 h-4 rounded-full transition-all duration-500"
                    style="width: {{ $analytics['orders']['completion_rate'] }}%"></div>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-4">
                <div class="text-center p-3 bg-green-50 rounded-lg">
                    <p class="text-xs text-green-700 font-medium">Completed</p>
                    <p class="text-xl font-bold text-green-900">{{ $analytics['orders']['completed'] }}</p>
                </div>
                <div class="text-center p-3 bg-red-50 rounded-lg">
                    <p class="text-xs text-red-700 font-medium">Cancelled</p>
                    <p class="text-xl font-bold text-red-900">{{ $analytics['orders']['cancelled'] }}</p>
                </div>
            </div>
        </div>

        <!-- Cancellation Rate -->
        <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-md font-semibold text-gray-900">Order Cancellation Rate</h4>
                <span class="text-2xl font-bold text-red-600">{{ $analytics['orders']['cancellation_rate'] }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                <div class="bg-gradient-to-r from-red-400 to-red-600 h-4 rounded-full transition-all duration-500"
                    style="width: {{ $analytics['orders']['cancellation_rate'] }}%"></div>
            </div>
            <div class="mt-4 p-4 bg-amber-50 rounded-lg border border-amber-200">
                <p class="text-sm text-amber-900">
                    <svg class="w-4 h-4 inline mr-1 text-amber-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                    @if($analytics['orders']['cancellation_rate'] > 10)
                    <strong>High cancellation rate!</strong> Consider reviewing order processing and customer
                    communication.
                    @elseif($analytics['orders']['cancellation_rate'] > 5)
                    <strong>Moderate cancellation rate.</strong> Monitor for patterns and improve where possible.
                    @else
                    <strong>Good performance!</strong> Your cancellation rate is within acceptable range.
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Order Type & Payment Method Breakdown -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Order Type Breakdown -->
        @if(!empty($analytics['order_type_breakdown']))
        <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
            <div class="flex items-center mb-4">
                <svg class="w-5 h-5 text-gray-700 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
                    </path>
                </svg>
                <h4 class="text-md font-semibold text-gray-900">Orders by Type</h4>
            </div>
            <div class="space-y-3">
                @foreach($analytics['order_type_breakdown'] as $type => $data)
                <div
                    class="flex items-center justify-between p-3 bg-gradient-to-r from-indigo-50 to-indigo-100 rounded-lg border border-indigo-200">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-indigo-500 rounded-lg flex items-center justify-center mr-3">
                            @if($type === 'delivery')
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0">
                                </path>
                            </svg>
                            @elseif($type === 'pickup' || $type === 'takeaway')
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            @else
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-indigo-900">{{ $data['name'] }}</p>
                            <p class="text-xs text-indigo-700">{{ $data['count'] }} orders</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-indigo-900">{{ fn_convert_currency((float) $data['revenue'],
                            $analytics['revenue']['currency'] ?? fn_get_setting('general.currency', 'USD')) }}</p>
                        <p class="text-xs text-indigo-700">revenue</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Payment Method Breakdown -->
        @if(!empty($analytics['payment_method_breakdown']))
        <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
            <div class="flex items-center mb-4">
                <svg class="w-5 h-5 text-gray-700 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                    </path>
                </svg>
                <h4 class="text-md font-semibold text-gray-900">Payment Methods</h4>
            </div>
            <div class="space-y-3">
                @foreach($analytics['payment_method_breakdown'] as $method => $data)
                <div
                    class="flex items-center justify-between p-3 bg-gradient-to-r from-emerald-50 to-emerald-100 rounded-lg border border-emerald-200">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-emerald-500 rounded-lg flex items-center justify-center mr-3">
                            @if($method === 'card' || $method === 'credit_card')
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                                </path>
                            </svg>
                            @elseif($method === 'cash')
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                            @else
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                </path>
                            </svg>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-emerald-900">{{ $data['name'] }}</p>
                            <p class="text-xs text-emerald-700">{{ $data['count'] }} transactions</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-emerald-900">{{ fn_convert_currency((float) $data['revenue'],
                            $analytics['revenue']['currency'] ?? fn_get_setting('general.currency', 'USD')) }}</p>
                        <p class="text-xs text-emerald-700">collected</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Daily Trend (Last 7 Days) -->
    @if(!empty($analytics['daily_trend']) && count($analytics['daily_trend']) > 0)
    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
        <div class="flex items-center mb-4">
            <svg class="w-5 h-5 text-gray-700 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
            </svg>
            <h4 class="text-lg font-semibold text-gray-900">Daily Performance Trend</h4>
            <span class="ml-auto text-xs text-gray-500">Last 7 days</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Date</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Total Orders
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Completed</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Revenue</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Completion %
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($analytics['daily_trend'] as $day)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            {{ \Carbon\Carbon::parse($day->date)->format('D, M d') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $day->total_orders }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ $day->completed_orders }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-bold text-green-600">
                            {{ fn_convert_currency((float) $day->revenue, $analytics['revenue']['currency'] ??
                            fn_get_setting('general.currency', 'USD')) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                            $completionRate = $day->total_orders > 0 ? round(($day->completed_orders /
                            $day->total_orders) * 100, 1) : 0;
                            @endphp
                            <div class="flex items-center justify-center">
                                <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ $completionRate }}%">
                                    </div>
                                </div>
                                <span class="text-xs font-medium text-gray-700">{{ $completionRate }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>