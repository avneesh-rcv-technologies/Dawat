<div class="space-y-6">
    <!-- Favorite Restaurants -->
    @if(count($analytics['favorite_restaurants']) > 0)
    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
        <h4 class="text-sm font-semibold text-gray-900 mb-3">Favorite Restaurants</h4>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Restaurant</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Orders</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total Spent</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($analytics['favorite_restaurants'] as $restaurant)
                    <tr>
                        <td class="px-3 py-2 text-sm text-gray-900">{{ $restaurant['restaurant_name'] }}</td>
                        <td class="px-3 py-2 text-sm text-right text-gray-900">{{ $restaurant['order_count'] }}</td>
                        <td class="px-3 py-2 text-sm text-right font-semibold text-green-600">{{ $restaurant['total_spent_formatted'] ?? fn_convert_currency($restaurant['total_spent'] ?? 0, $analytics['spending']['currency'] ?? fn_get_setting('general.currency', 'USD')) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="bg-white rounded-lg p-8 shadow-sm border border-gray-200 text-center">
        <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
            </path>
        </svg>
        <p class="text-gray-500">No restaurant data found</p>
    </div>
    @endif
</div>
