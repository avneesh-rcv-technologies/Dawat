@if(($mode ?? '') === 'view' && ($can_view_payout_details ?? false))
    <section class="receipt-payout-panel bg-white rounded-lg shadow-sm p-6" aria-labelledby="order-payout-details-heading">
        <h3 id="order-payout-details-heading" class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-wallet mr-2 text-primary-100" aria-hidden="true"></i>
            Payouts Details
        </h3>
        @if($order_payout)
            @php
                $payoutSettlement = $order_payout->settlementData();
                $payoutMoney = static fn ($value) => fn_format_currency_amount((float) $value, $order_payout->currency);
                $payoutStyle = match ($order_payout->status) {
                    'processed' => 'background:#ecfdf5;color:#047857;',
                    'on_hold' => 'background:#fffbeb;color:#92400e;',
                    'failed' => 'background:#fef2f2;color:#b91c1c;',
                    'cancelled' => 'background:#f3f4f6;color:#4b5563;',
                    default => 'background:#fff7ed;color:#c2410c;',
                };
            @endphp
            <dl class="space-y-3 text-sm">
                <div class="flex items-center justify-between gap-3">
                    <dt class="font-medium text-gray-600">Payout status</dt>
                    <dd class="px-2.5 py-0.5 rounded-full text-xs font-medium" style="{{ $payoutStyle }}">{{ ucfirst(str_replace('_', ' ', $order_payout->status)) }}</dd>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <dt class="text-gray-600">Commission rate</dt>
                    <dd class="font-medium text-gray-900">{{ $order_payout->commission_percentage }}%</dd>
                </div>
                @foreach(['platform_commission_amount' => 'Original commission', 'refunded_amount' => 'Successful refunds', 'commission_reversed_amount' => 'Commission reversed', 'effective_commission' => 'Effective commission', 'restaurant_refund_deduction' => 'Restaurant refund deduction'] as $field => $label)
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-gray-600">{{ $label }}</dt>
                        <dd class="font-medium text-gray-900 text-right">{{ $payoutMoney($payoutSettlement[$field]) }}</dd>
                    </div>
                @endforeach
                <div class="receipt-payout-total flex items-start justify-between gap-3 border-t border-gray-200 pt-3">
                    <dt class="font-semibold text-gray-900">Restaurant payable</dt>
                    <dd class="font-semibold text-gray-900 text-right">{{ $payoutMoney($order_payout->final_payable_amount) }}</dd>
                </div>
                <div class="flex items-start justify-between gap-3">
                    <dt class="text-gray-600">Paid to restaurant</dt>
                    <dd class="font-medium text-gray-900 text-right">{{ $payoutMoney($order_payout->paid_amount) }}</dd>
                </div>
                @if((float) $payoutSettlement['recovery_due_amount'] > 0)
                    <div class="flex items-start justify-between gap-3" style="color:#b91c1c">
                        <dt>Recovery due</dt>
                        <dd class="font-semibold text-right">{{ $payoutMoney($payoutSettlement['recovery_due_amount']) }}</dd>
                    </div>
                @endif
                @if($order_payout->processed_at)
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-gray-600">Processed at</dt>
                        <dd class="text-gray-900 text-right">{{ $order_payout->processed_at->format('M d, Y h:i A') }}</dd>
                    </div>
                @endif
            </dl>
            @if($order_payout->status === 'on_hold')
                <p class="mt-4 text-xs" style="color:#92400e">Payout is on hold while the cancellation or refund is reviewed.</p>
            @endif
            <a href="{{ route('admin.order-commissions.show', $order_payout->id) }}" class="receipt-detail-link inline-flex items-center gap-1 mt-4 text-sm">
                View payout details <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i>
            </a>
        @else
            <p class="text-sm text-gray-500">No payout record has been created for this order yet.</p>
        @endif
    </section>
@endif
