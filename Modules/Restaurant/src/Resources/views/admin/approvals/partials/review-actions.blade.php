@php
    $reviewRecord = $stepRecords->get($activeStep->value);
    $isBankingReview = $activeStep === \Modules\Restaurant\Enums\OnboardingStep::BANKING;
    $isTaxReview = $activeStep === \Modules\Restaurant\Enums\OnboardingStep::TAX;
    $reviewPrefix = $isTaxReview ? 'admin.restaurant.approvals.tax.' : ($isBankingReview ? 'admin.restaurant.approvals.banking.' : 'admin.restaurant.approvals.');
    $hasDetails = $isTaxReview ? $taxRecords->has($restaurant->tax_type) : (!$isBankingReview || $restaurant->bankingDetail);
    $awaitingRefill = $stepTabStates[$activeStep->value]['refill_requested'] ?? false;
    $reviewPending = $hasDetails && !$awaitingRefill && in_array($reviewRecord?->status, ['submitted', 'pending'], true);
    $reviewStarted = $hasDetails && !$awaitingRefill && $reviewRecord?->status === 'under_review';
@endphp
@if($reviewPending || $reviewStarted)
    <div class="review-tab-actions">
        <div class="review-actions">
            <div class="review-action-copy">
                <span class="review-action-icon" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></span>
                <div>
                    <strong>{{ $activeStep->label() }} review</strong>
                    <p>{{ $reviewStarted ? 'Review in progress — approve these details or reject this step with a reason.' : 'Approve these details or reject this step with a reason.' }}</p>
                </div>
            </div>
            <div class="review-action-buttons">
                @if(bouncer('admin')->hasPermission($reviewPrefix . 'approve'))
                    <form action="{{ route($reviewPrefix . 'approve', $restaurant->id) }}" method="POST" data-review-confirm="Approve {{ $activeStep->label() }}?">
                        @csrf
                        <input type="hidden" name="step_key" value="{{ $activeStep->value }}">
                        @if($isTaxReview)<input type="hidden" name="type" value="{{ $taxType }}">@endif
                        <button type="submit" class="ar-btn ar-btn--primary review-approve">
                            <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 4 4L19 6"/></svg>
                            Approve
                        </button>
                    </form>
                @endif
                @if(bouncer('admin')->hasPermission($reviewPrefix . 'reject'))
                    <button type="button" id="open-rejection" class="ar-btn ar-btn--danger review-reject">
                        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        Reject
                    </button>
                @endif
            </div>
        </div>
    </div>
@endif
