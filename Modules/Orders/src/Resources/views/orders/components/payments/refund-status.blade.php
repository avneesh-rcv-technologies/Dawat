@php
    $paymentRefunds = collect($refunds ?? [])->sortByDesc('id');
    $refundMessages = [
        'pending' => ['label' => 'Refund pending', 'message' => 'The refund has been requested and is awaiting manual processing. Payment status changes after the refund is recorded as completed.', 'icon' => 'fa-clock', 'background' => '#fffbeb', 'border' => '#fde68a', 'color' => '#92400e'],
        'processing' => ['label' => 'Refund processing', 'message' => 'This refund is in progress. Completion has not yet been recorded.', 'icon' => 'fa-arrows-rotate', 'background' => '#eff6ff', 'border' => '#bfdbfe', 'color' => '#1e40af'],
        'succeeded' => ['label' => 'Refund completed', 'message' => 'This manual refund has been recorded successfully. The payment status reflects the total amount refunded.', 'icon' => 'fa-circle-check', 'background' => '#ecfdf5', 'border' => '#a7f3d0', 'color' => '#065f46'],
        'failed' => ['label' => 'Refund failed', 'message' => 'The refund attempt failed. Review the refund request and retry after resolving the issue.', 'icon' => 'fa-circle-exclamation', 'background' => '#fef2f2', 'border' => '#fecaca', 'color' => '#991b1b'],
        'rejected' => ['label' => 'Refund rejected', 'message' => 'This refund request was rejected. Open the request to review its details.', 'icon' => 'fa-ban', 'background' => '#f9fafb', 'border' => '#e5e7eb', 'color' => '#4b5563'],
    ];
@endphp
@if(($mode ?? '') === 'view')
    <div class="receipt-refund-statuses">
        @forelse($paymentRefunds as $paymentRefund)
            @php
                $refundMessage = $refundMessages[$paymentRefund->refund_status] ?? ['label' => 'Refund under review', 'message' => 'Open the refund request to review its current status.', 'icon' => 'fa-circle-info', 'background' => '#f9fafb', 'border' => '#e5e7eb', 'color' => '#4b5563'];
            @endphp
            <section class="receipt-refund-notice" style="background: {{ $refundMessage['background'] }}; border-color: {{ $refundMessage['border'] }}; color: {{ $refundMessage['color'] }};" aria-label="{{ $refundMessage['label'] }} {{ $paymentRefund->refund_number }}">
                <div class="receipt-refund-heading"><i class="fas {{ $refundMessage['icon'] }}" aria-hidden="true"></i><strong>{{ $refundMessage['label'] }}</strong></div>
                <p>{{ $refundMessage['message'] }}</p>
                <div class="receipt-refund-meta">
                    <a href="{{ route('admin.refunds.show', $paymentRefund->id) }}" class="receipt-refund-link">{{ $paymentRefund->refund_number }} <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
                    @if($paymentRefund->refund_amount !== null)
                        <span>{{ fn_format_currency_amount((float) $paymentRefund->refund_amount, $paymentRefund->payment?->currency ?? $currency ?? 'USD') }}</span>
                    @endif
                </div>
            </section>
        @empty
            @if(($isStoppedOrder ?? false) && in_array($payment_status ?? '', ['paid', 'completed', 'captured', 'partially_refunded'], true))
                <p class="receipt-refund-notice" style="background: #fffbeb; border-color: #fde68a; color: #92400e;">No refund request is recorded for this closed order. Review the captured payment before recording a manual refund.</p>
            @endif
        @endforelse
    </div>
@endif
