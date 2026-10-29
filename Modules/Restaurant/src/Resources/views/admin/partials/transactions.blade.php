<div class="space-y-6">
    <!-- Transaction Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-lg p-5 border border-emerald-200">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-emerald-500 rounded-lg flex items-center justify-center shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                </div>
                <span class="text-xs font-medium text-emerald-700 bg-emerald-200 px-2 py-1 rounded-full">Total</span>
            </div>
            <p class="text-sm font-medium text-emerald-900 mb-1">Total Transactions</p>
            <p class="text-3xl font-bold text-emerald-900">{{ $analytics['transactions']['total'] }}</p>
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
            <p class="text-3xl font-bold text-green-900">{{ $analytics['transactions']['completed'] }}</p>
        </div>

        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-5 border border-orange-200">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                    </svg>
                </div>
                <span class="text-xs font-medium text-orange-700 bg-orange-200 px-2 py-1 rounded-full">Refunds</span>
            </div>
            <p class="text-sm font-medium text-orange-900 mb-1">Refunded</p>
            <p class="text-3xl font-bold text-orange-900">{{ $analytics['transactions']['refunds'] }}</p>
        </div>
    </div>

    <!-- Payment Summary -->
    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
        <div class="flex items-center mb-4">
            <svg class="w-5 h-5 text-gray-700 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <h4 class="text-lg font-semibold text-gray-900">Payment Summary</h4>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gradient-to-br from-teal-50 to-teal-100 rounded-lg p-5 border border-teal-200">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium text-teal-900">Total Collected</p>
                    <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-teal-900">{{ $analytics['payments']['total_collected_formatted'] ?? fn_convert_currency($analytics['payments']['total_collected'] ?? 0, $analytics['revenue']['currency'] ?? fn_get_setting('general.currency', 'USD')) }}</p>
                <p class="text-xs text-teal-700 mt-2">Successfully processed</p>
            </div>
            
            <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-lg p-5 border border-amber-200">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium text-amber-900">Pending Payments</p>
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-amber-900">{{ $analytics['payments']['pending_formatted'] ?? fn_convert_currency($analytics['payments']['pending'] ?? 0, $analytics['revenue']['currency'] ?? fn_get_setting('general.currency', 'USD')) }}</p>
                <p class="text-xs text-amber-700 mt-2">Awaiting confirmation</p>
            </div>
            
            <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-5 border border-red-200">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium text-red-900">Failed Payments</p>
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-red-900">{{ $analytics['payments']['failed_count'] }}</p>
                <p class="text-xs text-red-700 mt-2">Failed transactions</p>
            </div>
        </div>
    </div>

    <!-- Transaction Success Rate -->
    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h4 class="text-md font-semibold text-gray-900">Transaction Success Rate</h4>
            @php
                $successRate = $analytics['transactions']['total'] > 0 ? round(($analytics['transactions']['completed'] / $analytics['transactions']['total']) * 100, 2) : 0;
            @endphp
            <span class="text-2xl font-bold text-green-600">{{ $successRate }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
            <div class="bg-gradient-to-r from-green-400 to-green-600 h-4 rounded-full transition-all duration-500" style="width: {{ $successRate }}%"></div>
        </div>
        <div class="mt-4 grid grid-cols-3 gap-4">
            <div class="text-center p-3 bg-green-50 rounded-lg">
                <p class="text-xs text-green-700 font-medium">Completed</p>
                <p class="text-xl font-bold text-green-900">{{ $analytics['transactions']['completed'] }}</p>
            </div>
            <div class="text-center p-3 bg-orange-50 rounded-lg">
                <p class="text-xs text-orange-700 font-medium">Refunded</p>
                <p class="text-xl font-bold text-orange-900">{{ $analytics['transactions']['refunds'] }}</p>
            </div>
            <div class="text-center p-3 bg-gray-50 rounded-lg">
                <p class="text-xs text-gray-700 font-medium">Other</p>
                <p class="text-xl font-bold text-gray-900">{{ $analytics['transactions']['total'] - $analytics['transactions']['completed'] - $analytics['transactions']['refunds'] }}</p>
            </div>
        </div>
        <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
            <p class="text-sm text-blue-900">
                <svg class="w-4 h-4 inline mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                @if($successRate >= 95)
                    <strong>Excellent!</strong> Your transaction success rate is outstanding. Keep up the great work!
                @elseif($successRate >= 85)
                    <strong>Good!</strong> Your transaction success rate is healthy. Monitor for any issues.
                @else
                    <strong>Attention!</strong> Your transaction success rate could be improved. Review failed transactions.
                @endif
            </p>
        </div>
    </div>

    <!-- Financial Overview -->
    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
        <div class="flex items-center mb-4">
            <svg class="w-5 h-5 text-gray-700 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
            <h4 class="text-lg font-semibold text-gray-900">Financial Overview</h4>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Money Flow -->
            <div class="space-y-3">
                <h5 class="text-sm font-semibold text-gray-700 mb-3">Money Flow</h5>
                <div class="flex items-center justify-between p-3 bg-gradient-to-r from-green-50 to-green-100 rounded-lg border border-green-200">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-green-900">Money In</p>
                            <p class="text-sm text-green-700">Collected payments</p>
                        </div>
                    </div>
                    <p class="text-lg font-bold text-green-900">{{ $analytics['payments']['total_collected_formatted'] ?? fn_convert_currency($analytics['payments']['total_collected'] ?? 0, $analytics['revenue']['currency'] ?? fn_get_setting('general.currency', 'USD')) }}</p>
                </div>

                <div class="flex items-center justify-between p-3 bg-gradient-to-r from-orange-50 to-orange-100 rounded-lg border border-orange-200">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-orange-500 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-orange-900">Money Out</p>
                            <p class="text-sm text-orange-700">Refunded amount</p>
                        </div>
                    </div>
                    @php
                        $refundAmount = $analytics['transactions']['refunds'] * ($analytics['revenue']['average_order_value'] ?? 0);
                    @endphp
                    <p class="text-lg font-bold text-orange-900">{{ fn_convert_currency((float) $refundAmount, $analytics['revenue']['currency'] ?? fn_get_setting('general.currency', 'USD')) }}</p>
                </div>

                <div class="flex items-center justify-between p-3 bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg border border-blue-200">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-blue-900">Net Revenue</p>
                            <p class="text-sm text-blue-700">After refunds</p>
                        </div>
                    </div>
                    <p class="text-lg font-bold text-blue-900">{{ fn_convert_currency((float) ($analytics['payments']['total_collected'] - $refundAmount), $analytics['revenue']['currency'] ?? fn_get_setting('general.currency', 'USD')) }}</p>
                </div>
            </div>

            <!-- Transaction Stats -->
            <div class="space-y-3">
                <h5 class="text-sm font-semibold text-gray-700 mb-3">Transaction Statistics</h5>
                <div class="p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg border border-purple-200">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-medium text-purple-900">Total Transactions</p>
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <p class="text-3xl font-bold text-purple-900">{{ $analytics['transactions']['total'] }}</p>
                </div>

                <div class="p-4 bg-gradient-to-br from-cyan-50 to-cyan-100 rounded-lg border border-cyan-200">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-medium text-cyan-900">Success Rate</p>
                        <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <p class="text-3xl font-bold text-cyan-900">{{ $successRate }}%</p>
                </div>

                <div class="p-4 bg-gradient-to-br from-rose-50 to-rose-100 rounded-lg border border-rose-200">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-medium text-rose-900">Refund Rate</p>
                        <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                        </svg>
                    </div>
                    @php
                        $refundRate = $analytics['transactions']['total'] > 0 ? round(($analytics['transactions']['refunds'] / $analytics['transactions']['total']) * 100, 2) : 0;
                    @endphp
                    <p class="text-3xl font-bold text-rose-900">{{ $refundRate }}%</p>
                </div>
            </div>
        </div>
    </div>
</div>
