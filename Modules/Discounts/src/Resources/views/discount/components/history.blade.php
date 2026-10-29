<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Coupon Usage History</h3>
        <p class="mt-1 text-sm text-gray-500">Track all instances where coupons from this discount have been used</p>
    </div>

    <div class="p-6">
        @if(isset($discount) && $discount->couponUsages && $discount->couponUsages->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Coupon Code
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Used By
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                User Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Discount Amount
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Order ID
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Used At
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($discount->couponUsages()->with(['coupon', 'usable'])->latest('used_at')->get() as $usage)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $usage->coupon->code ?? 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($usage->usable)
                                        <div class="text-sm text-gray-900">
                                            {{ $usage->usable->name ?? $usage->usable->email ?? 'Unknown' }}
                                        </div>
                                        @if(isset($usage->usable->email) && $usage->usable->email)
                                            <div class="text-xs text-gray-500">
                                                {{ $usage->usable->email }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-sm text-gray-400">Deleted User</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if(str_contains($usage->usable_type, 'Admin')) bg-purple-100 text-purple-800
                                        @elseif(str_contains($usage->usable_type, 'Vendor')) bg-blue-100 text-blue-800
                                        @else bg-green-100 text-green-800
                                        @endif">
                                        {{ class_basename($usage->usable_type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        ${{ number_format($usage->discount_amount, 2) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($usage->order_id)                                        
                                            #{{ $usage->order_id }}
                                        
                                    @else
                                        <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div>{{ $usage->used_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-400">{{ $usage->used_at->format('h:i A') }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Summary Statistics -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-blue-50 rounded-lg p-4">
                    <div class="text-sm text-blue-600 font-medium">Total Uses</div>
                    <div class="text-2xl font-bold text-blue-900">{{ $discount->couponUsages->count() }}</div>
                </div>
                <div class="bg-green-50 rounded-lg p-4">
                    <div class="text-sm text-green-600 font-medium">Total Discount Given</div>
                    <div class="text-2xl font-bold text-green-900">
                        ${{ number_format($discount->couponUsages->sum('discount_amount'), 2) }}
                    </div>
                </div>
                <div class="bg-purple-50 rounded-lg p-4">
                    <div class="text-sm text-purple-600 font-medium">Unique Users</div>
                    <div class="text-2xl font-bold text-purple-900">
                        {{ $discount->couponUsages->unique(fn($u) => $u->usable_type . $u->usable_id)->count() }}
                    </div>
                </div>
                <div class="bg-orange-50 rounded-lg p-4">
                    <div class="text-sm text-orange-600 font-medium">Avg. Discount</div>
                    <div class="text-2xl font-bold text-orange-900">
                        ${{ number_format($discount->couponUsages->avg('discount_amount') ?? 0, 2) }}
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No usage history</h3>
                <p class="mt-1 text-sm text-gray-500">This discount hasn't been used yet.</p>
            </div>
        @endif
    </div>
</div>
