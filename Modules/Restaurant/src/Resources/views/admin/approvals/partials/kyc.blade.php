<div id="kyc-panel" class="review-kyc">
    @php
        $submissionsByType = $kycSubmissions->sortByDesc('id')->unique('kyc_type_id')->keyBy('kyc_type_id');
        $documentTypes = $kycTypes->concat($kycSubmissions->pluck('kycType')->filter())->unique('id');
        $approvedDocuments = $documentTypes->filter(fn ($type) => $submissionsByType->get($type->id)?->status === 'approved' && !$submissionsByType->get($type->id)?->refill_requested)->count();
    @endphp
    <div class="ar-panel-head">
        <div>
            <p class="ar-eyebrow">Step {{ \Modules\Restaurant\Enums\OnboardingStep::KYC->order() }}</p>
            <h3>KYC Documents</h3>
            <p class="review-help">Document status for {{ $restaurant->admin?->name ?? 'the restaurant owner' }}. Select a document to open its KYC review page.</p>
        </div>
        @if($documentTypes->isNotEmpty())
            <span class="ar-pill {{ $approvedDocuments === $documentTypes->count() ? 'ar-pill--success' : 'ar-pill--neutral' }}">{{ $approvedDocuments }} of {{ $documentTypes->count() }} approved</span>
        @endif
    </div>

    <ul class="kyc-document-list" aria-label="KYC documents and approval status">
        @forelse($documentTypes as $documentType)
            @php
                $typeSubmission = $submissionsByType->get($documentType->id);
                $typeStatus = $typeSubmission?->refill_requested ? 'action_required' : ($typeSubmission?->status ?? 'not_submitted');
                $typeStatusLabel = match ($typeStatus) {
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                    'action_required' => 'Refill requested',
                    'pending', 'submitted', 'resubmitted' => 'Pending',
                    'under_review' => 'Under review',
                    'draft', 'not_submitted' => 'Required — not submitted',
                    default => \Illuminate\Support\Str::headline($typeStatus),
                };
            @endphp
            <li class="kyc-document-list-entry">
                @if($typeSubmission)
                    <a class="kyc-document-row" href="{{ route('admin.kyc.submissions.show', ['submission' => $typeSubmission->id, 'restaurant_id' => $restaurant->id]) }}">
                @else
                    <div class="kyc-document-row">
                @endif
                    @include('restaurant::admin.approvals.partials.kyc-status-icon', ['documentStatus' => $typeStatus])
                    <span class="kyc-document-row-info">
                        <span class="kyc-document-row-name">{{ $documentType->name }}</span>
                        <span class="kyc-document-row-status" data-status="{{ $typeStatus }}">{{ $typeStatusLabel }}</span>
                    </span>
                    @if($typeSubmission)
                        <span class="kyc-document-row-link">View document <span aria-hidden="true">→</span></span>
                    @else
                        <span class="kyc-document-row-missing">Awaiting upload</span>
                    @endif
                @if($typeSubmission)
                    </a>
                @else
                    </div>
                @endif
            </li>
        @empty
            <li class="ar-empty">
                <span class="ar-empty-icon" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8Z"/><path d="M14 3v5h5M9 13h6M9 17h4"/></svg></span>
                <h4>No KYC document types available</h4>
                <p>Documents will appear here when KYC requirements are configured.</p>
            </li>
        @endforelse
    </ul>
</div>
