@php
    $reviewRecord = $stepRecords->get($activeStep->value);
    $isBankingReview = $activeStep === \Modules\Restaurant\Enums\OnboardingStep::BANKING;
    $isTaxReview = $activeStep === \Modules\Restaurant\Enums\OnboardingStep::TAX;
    $reviewPrefix = $isTaxReview ? 'admin.restaurant.approvals.tax.' : ($isBankingReview ? 'admin.restaurant.approvals.banking.' : 'admin.restaurant.approvals.');
    $hasDetails = $isTaxReview ? $taxRecords->has($restaurant->tax_type) : (!$isBankingReview || $restaurant->bankingDetail);
    $reviewPending = $hasDetails && in_array($reviewRecord?->status, ['submitted', 'pending', 'action_required'], true);
    $reviewStarted = $hasDetails && $reviewRecord?->status === 'under_review';
@endphp
@if($reviewPending || $reviewStarted)
    <div class="review-tab-actions">
        <div class="review-actions">
            <div class="review-action-copy">
                <strong>{{ $activeStep->label() }} review</strong>
                <p>{{ $reviewPending ? 'Start review to check these details, then approve or reject this step.' : 'Review in progress. Approve these details or explain what needs to be corrected.' }}</p>
            </div>
            <div class="review-action-buttons">
                @if($reviewPending)
                    @if(bouncer('admin')->hasPermission('admin.restaurant.approvals.start-review'))
                        <form action="{{ route('admin.restaurant.approvals.start-review', $restaurant->id) }}" method="POST" data-review-confirm="Start reviewing {{ $activeStep->label() }}?">
                            @csrf
                            <input type="hidden" name="step_key" value="{{ $activeStep->value }}">
                            @if($isTaxReview)<input type="hidden" name="type" value="{{ $taxType }}">@endif
                            <button type="submit" class="review-start">Start Review</button>
                        </form>
                    @endif
                @else
                    <span class="review-in-progress">Under review</span>
                    @if(bouncer('admin')->hasPermission($reviewPrefix . 'approve'))
                        <form action="{{ route($reviewPrefix . 'approve', $restaurant->id) }}" method="POST" data-review-confirm="Approve {{ $activeStep->label() }}?">
                            @csrf
                            <input type="hidden" name="step_key" value="{{ $activeStep->value }}">
                            @if($isTaxReview)<input type="hidden" name="type" value="{{ $taxType }}">@endif
                            <button type="submit" class="review-approve">Approve</button>
                        </form>
                    @endif
                    @if(bouncer('admin')->hasPermission($reviewPrefix . 'reject'))
                        <button type="button" id="open-rejection" class="review-reject">Reject</button>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endif
