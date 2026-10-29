<div class="space-y-6">
    <!-- Recent Transactions -->
    @if(count($analytics['recent_transactions']) > 0)
    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
        <h4 class="text-sm font-semibold text-gray-900 mb-3">Recent Transactions</h4>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Transaction #</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Order #</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($analytics['recent_transactions'] as $transaction)
                    <tr>
                        <td class="px-3 py-2 text-sm font-mono text-gray-900">{{ $transaction['transaction_number'] }}</td>
                        <td class="px-3 py-2 text-sm font-mono text-gray-900">{{ $transaction['order_number'] }}</td>
                        <td class="px-3 py-2 text-sm text-gray-900 capitalize">{{ $transaction['type'] }}</td>
                        <td class="px-3 py-2 text-sm text-right font-semibold text-gray-900">{{ $transaction['amount_formatted'] ?? fn_convert_currency($transaction['amount'] ?? 0, $analytics['spending']['currency'] ?? fn_get_setting('general.currency', 'USD')) }}</td>
                        <td class="px-3 py-2 text-sm">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $transaction['status'] == 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $transaction['status'] }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-sm text-gray-600">{{ \Carbon\Carbon::parse($transaction['created_at'])->format('M d, Y H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="bg-white rounded-lg p-8 shadow-sm border border-gray-200 text-center">
        <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
        </svg>
        <p class="text-gray-500">No transactions found</p>
    </div>
    @endif
</div>
