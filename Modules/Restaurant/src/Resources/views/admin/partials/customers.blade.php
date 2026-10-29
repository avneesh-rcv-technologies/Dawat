<div class="space-y-6">
    <!-- Customer Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-5 border border-purple-200">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <span class="text-xs font-medium text-purple-700 bg-purple-200 px-2 py-1 rounded-full">Total</span>
            </div>
            <p class="text-sm font-medium text-purple-900 mb-1">Unique Customers</p>
            <p class="text-3xl font-bold text-purple-900">{{ $analytics['customers']['unique'] }}</p>
        </div>

        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-5 border border-blue-200">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </div>
                <span class="text-xs font-medium text-blue-700 bg-blue-200 px-2 py-1 rounded-full">{{ $analytics['customers']['repeat_rate'] }}%</span>
            </div>
            <p class="text-sm font-medium text-blue-900 mb-1">Repeat Customers</p>
            <p class="text-3xl font-bold text-blue-900">{{ $analytics['customers']['repeat'] }}</p>
        </div>

        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-5 border border-green-200">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                </div>
                <span class="text-xs font-medium text-green-700 bg-green-200 px-2 py-1 rounded-full">New</span>
            </div>
            <p class="text-sm font-medium text-green-900 mb-1">New Customers</p>
            <p class="text-3xl font-bold text-green-900">{{ $analytics['customers']['new'] }}</p>
        </div>
    </div>

    <!-- Customer Spending Analytics -->
    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
        <div class="flex items-center mb-4">
            <svg class="w-5 h-5 text-gray-700 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h4 class="text-lg font-semibold text-gray-900">Customer Spending Analytics</h4>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gradient-to-br from-teal-50 to-teal-100 rounded-lg p-5 border border-teal-200">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium text-teal-900">Average Spend</p>
                    <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-teal-900">{{ $analytics['customers']['average_spend_formatted'] ?? fn_convert_currency($analytics['customers']['average_spend'] ?? 0, $analytics['revenue']['currency'] ?? fn_get_setting('general.currency', 'USD')) }}</p>
                <p class="text-xs text-teal-700 mt-2">Per customer</p>
            </div>
            
            <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-lg p-5 border border-amber-200">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium text-amber-900">Highest Spender</p>
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-amber-900">{{ $analytics['customers']['highest_spend_formatted'] ?? fn_convert_currency($analytics['customers']['highest_spend'] ?? 0, $analytics['revenue']['currency'] ?? fn_get_setting('general.currency', 'USD')) }}</p>
                <p class="text-xs text-amber-700 mt-2">Top customer lifetime value</p>
            </div>
            
            <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg p-5 border border-indigo-200">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium text-indigo-900">Avg Orders</p>
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-indigo-900">{{ number_format($analytics['customers']['average_orders'], 1) }}</p>
                <p class="text-xs text-indigo-700 mt-2">Orders per customer</p>
            </div>
        </div>
    </div>

    <!-- Customer Loyalty Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Repeat Customer Rate -->
        <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-md font-semibold text-gray-900">Customer Loyalty Rate</h4>
                <span class="text-2xl font-bold text-blue-600">{{ $analytics['customers']['repeat_rate'] }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-400 to-blue-600 h-4 rounded-full transition-all duration-500" style="width: {{ $analytics['customers']['repeat_rate'] }}%"></div>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-4">
                <div class="text-center p-3 bg-blue-50 rounded-lg">
                    <p class="text-xs text-blue-700 font-medium">Repeat</p>
                    <p class="text-xl font-bold text-blue-900">{{ $analytics['customers']['repeat'] }}</p>
                </div>
                <div class="text-center p-3 bg-green-50 rounded-lg">
                    <p class="text-xs text-green-700 font-medium">New</p>
                    <p class="text-xl font-bold text-green-900">{{ $analytics['customers']['new'] }}</p>
                </div>
            </div>
            <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                <p class="text-sm text-blue-900">
                    <svg class="w-4 h-4 inline mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    @if($analytics['customers']['repeat_rate'] > 40)
                        <strong>Excellent!</strong> High customer loyalty indicates great service and food quality.
                    @elseif($analytics['customers']['repeat_rate'] > 25)
                        <strong>Good!</strong> Decent customer retention. Consider loyalty programs to improve.
                    @else
                        <strong>Opportunity!</strong> Focus on customer experience to increase repeat orders.
                    @endif
                </p>
            </div>
        </div>

        <!-- Customer Growth -->
        <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-md font-semibold text-gray-900">Customer Base Growth</h4>
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
            </div>
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg border border-purple-200">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-purple-900">Total Customer Base</p>
                            <p class="text-xs text-purple-700">All time</p>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-purple-900">{{ $analytics['customers']['unique'] }}</p>
                </div>
                
                <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-green-900">New Acquisitions</p>
                            <p class="text-xs text-green-700">In selected period</p>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-green-900">{{ $analytics['customers']['new'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Customers -->
    @if(!empty($analytics['customers']['top_customers']) && count($analytics['customers']['top_customers']) > 0)
    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-gray-700 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                </svg>
                <h4 class="text-lg font-semibold text-gray-900">Top Customers</h4>
            </div>
            <span class="text-xs text-gray-500 bg-gray-100 px-3 py-1 rounded-full">Top 10 by spending</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Rank</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Customer</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Orders</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Total Spent</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Avg Order</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($analytics['customers']['top_customers'] as $index => $customer)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($index === 0)
                                <span class="inline-flex items-center justify-center w-8 h-8 bg-yellow-100 text-yellow-800 rounded-full font-bold text-sm">🥇</span>
                            @elseif($index === 1)
                                <span class="inline-flex items-center justify-center w-8 h-8 bg-gray-100 text-gray-800 rounded-full font-bold text-sm">🥈</span>
                            @elseif($index === 2)
                                <span class="inline-flex items-center justify-center w-8 h-8 bg-orange-100 text-orange-800 rounded-full font-bold text-sm">🥉</span>
                            @else
                                <span class="inline-flex items-center justify-center w-8 h-8 bg-gray-100 text-gray-600 rounded-full font-bold text-sm">{{ $index + 1 }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-purple-400 to-purple-600 rounded-full flex items-center justify-center text-white font-bold">
                                    {{ strtoupper(substr($customer['name'], 0, 1)) }}
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $customer['name'] }}</div>
                                    <div class="text-xs text-gray-500">{{ $customer['email'] }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $customer['orders'] }} orders
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right">
                            <span class="text-sm font-bold text-green-600">{{ $customer['total_spent_formatted'] ?? fn_convert_currency($customer['total_spent'] ?? 0, $analytics['revenue']['currency'] ?? fn_get_setting('general.currency', 'USD')) }}</span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right">
                            <span class="text-sm text-gray-900">{{ $customer['avg_order_formatted'] ?? fn_convert_currency($customer['avg_order'] ?? 0, $analytics['revenue']['currency'] ?? fn_get_setting('general.currency', 'USD')) }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Customer Acquisition by Month -->
    @if(!empty($analytics['customers']['by_month']))
    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
        <div class="flex items-center mb-4">
            <svg class="w-5 h-5 text-gray-700 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <h4 class="text-lg font-semibold text-gray-900">Customer Acquisition Trend</h4>
            <span class="ml-auto text-xs text-gray-500">Last 6 months</span>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
            @foreach($analytics['customers']['by_month'] as $month => $count)
            <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg p-4 border border-indigo-200 text-center">
                <p class="text-xs font-medium text-indigo-700 mb-2">{{ \Carbon\Carbon::parse($month . '-01')->format('M Y') }}</p>
                <p class="text-2xl font-bold text-indigo-900">{{ $count }}</p>
                <p class="text-xs text-indigo-700 mt-1">new</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
