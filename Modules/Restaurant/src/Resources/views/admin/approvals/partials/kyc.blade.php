<div id="kyc-panel" class="review-kyc space-y-5 p-6">
    <div>
        <h3 class="text-lg font-semibold text-gray-900">KYC Documents</h3>
        <p class="review-help">Submitted documents for {{ $restaurant->admin?->name ?? 'the restaurant owner' }}. Open a submission to review its details and files{{ $canReviewKyc ? ', then approve or reject it' : '' }}.</p>
    </div>

    <dl class="kyc-counts" aria-label="KYC document summary">
        @foreach([
            'pending' => ['label' => 'Total Pending', 'help' => 'Awaiting or under review'],
            'approved' => ['label' => 'Total Approved', 'help' => 'Approved documents'],
            'rejected' => ['label' => 'Needs update', 'help' => 'Rejected or refill requested'],
            'resubmitted' => ['label' => 'Total Resubmitted', 'help' => 'KYC resubmission events'],
            'draft' => ['label' => 'Total Draft', 'help' => 'Required, not submitted'],
        ] as $key => $card)
            <div class="kyc-count-card kyc-count-{{ $key }}">
                <dt><span class="kyc-count-dot" aria-hidden="true"></span>{{ $card['label'] }}</dt>
                <dd>{{ $kycCounts[$key] ?? 0 }}</dd>
                <p>{{ $card['help'] }}</p>
            </div>
        @endforeach
    </dl>

    @php
        $submissionsByType = $kycSubmissions->keyBy('kyc_type_id');
        $documentTypes = $kycTypes->concat($kycSubmissions->pluck('kycType')->filter())->unique('id');
    @endphp
    @if($documentTypes->isNotEmpty())
        <section aria-labelledby="kyc-document-types-title" class="kyc-type-checklist">
            <h4 id="kyc-document-types-title" class="text-sm font-semibold">Document types</h4>
            <ul class="kyc-type-list">
                @foreach($documentTypes as $documentType)
                    @php
                        $typeSubmission = $submissionsByType->get($documentType->id);
                        $typeStatus = $typeSubmission?->refill_requested ? 'action_required' : ($typeSubmission?->status ?? 'not_submitted');
                        $typeStatusEnum = \Modules\Restaurant\Enums\OnboardingStatus::tryFrom($typeStatus);
                        $typeStatusLabel = $typeStatus === 'action_required' ? 'Refill requested' : ($typeStatus === 'not_submitted' ? 'Not submitted' : ($typeStatusEnum?->label() ?? \Illuminate\Support\Str::headline($typeStatus)));
                    @endphp
                    <li class="kyc-type-item">
                        @include('restaurant::admin.approvals.partials.kyc-status-icon', ['documentStatus' => $typeStatus])
                        <div class="min-w-0">
                            @if($typeSubmission)
                                <a href="#kyc-document-{{ $typeSubmission->id }}" class="kyc-type-name">{{ $documentType->name }}</a>
                            @else
                                <span class="kyc-type-name">{{ $documentType->name }}</span>
                            @endif
                            <span class="kyc-type-status">{{ $typeStatusLabel }}</span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    @forelse($kycSubmissions as $submission)
        @php
            $submissionStatus = \Modules\Restaurant\Enums\OnboardingStatus::tryFrom($submission->status);
            $statusLabel = $submissionStatus?->label() ?? \Illuminate\Support\Str::headline($submission->status);
            $statusColor = $submissionStatus?->color() ?? 'bg-yellow-100 text-yellow-800';
            if ($submission->refill_requested) {
                $statusLabel = 'Refill requested';
                $statusColor = 'bg-orange-100 text-orange-800';
            }
            $canReviewSubmission = $canReviewKyc
                && !app(\Modules\Restaurant\Services\RestaurantApprovalAccess::class)->isOwner(auth('admin')->user())
                && (in_array($submission->status, ['pending', 'under_review'], true) || $submission->refill_requested);
            $formData = $submission->data->filter(fn ($data) => $data->field?->field_type !== 'file' && $data->field_value !== 'file_uploaded');
        @endphp
        <article class="kyc-document" id="kyc-document-{{ $submission->id }}">
            <header class="kyc-document-header">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        @include('restaurant::admin.approvals.partials.kyc-status-icon', ['documentStatus' => $submission->refill_requested ? 'action_required' : $submission->status])
                        <h4>{{ $submission->kycType?->name ?? 'KYC document' }}</h4>
                    </div>
                    <p>Submitted {{ $submission->created_at?->format('M d, Y h:i A') ?? 'N/A' }} · {{ $submission->files->count() }} {{ $submission->files->count() === 1 ? 'file' : 'files' }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="kyc-status {{ $statusColor }}">{{ $statusLabel }}</span>
                    @include('backend::partials.correction-menu-button', [
                        'correctionTitle' => $submission->kycType?->name ?? 'KYC document',
                        'correctionUrl' => route('admin.kyc.submissions.review', $submission->id),
                        'correctionStep' => null,
                        'correctionMode' => 'ajax',
                        'correctionAllowed' => $canReviewKyc && !app(\Modules\Restaurant\Services\RestaurantApprovalAccess::class)->isOwner(auth('admin')->user()),
                        'correctionDisabled' => false,
                        'correctionView' => route('admin.kyc.submissions.show', ['submission' => $submission->id, 'restaurant_id' => $restaurant->id]),
                        'correctionViewLabel' => 'View submission',
                    ])
                </div>
            </header>

            <div class="kyc-document-content">
                @if($formData->isNotEmpty())
                    <dl class="kyc-fields">
                        @foreach($formData as $data)
                            <div>
                                <dt>{{ $data->field?->field_name ?? \Illuminate\Support\Str::headline($data->field_key ?? 'Field') }}</dt>
                                <dd>{{ is_array($data->field_value) ? implode(', ', $data->field_value) : ($data->field_value ?? 'N/A') }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @else
                    <p class="text-xs text-gray-500">No additional form details submitted.</p>
                @endif

                @if($submission->files->isNotEmpty())
                    <div class="kyc-files">
                        <p class="kyc-section-label">Uploaded documents</p>
                        <ul>
                            @foreach($submission->files as $file)
                                <li>
                                    @if(bouncer('admin')->hasPermission('admin.kyc.submissions.preview-file'))
                                        <a href="{{ route('admin.kyc.submissions.preview-file', ['submission' => $submission->id, 'file' => $file->id]) }}" target="_blank" rel="noopener" class="kyc-file-link">{{ $file->field?->field_name ?? 'Document ' . $loop->iteration }} <span aria-hidden="true">↗</span></a>
                                    @else
                                        <span>{{ $file->field?->field_name ?? 'Document ' . $loop->iteration }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($submission->rejection_reason)
                    <div class="kyc-rejection"><strong>Review feedback / required corrections</strong><p>{{ $submission->rejection_reason }}</p></div>
                @endif
            </div>

            @if($canReviewSubmission)
                <div class="kyc-step-review-actions">
                    @if($submission->status === 'pending' || $submission->refill_requested)
                        <form method="POST" action="{{ route('admin.kyc.submissions.review', $submission->id) }}" data-kyc-review data-kyc-review-confirm="Start reviewing this KYC document?">
                            @csrf
                            <input type="hidden" name="action" value="under_review">
                            <button type="submit" class="kyc-review-action">Start review</button>
                            <p data-kyc-review-error role="alert" hidden></p>
                        </form>
                    @elseif($submission->status === 'under_review')
                        <form method="POST" action="{{ route('admin.kyc.submissions.review', $submission->id) }}" data-kyc-review data-kyc-review-confirm="Approve this KYC document?">
                            @csrf
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="kyc-review-action kyc-review-action-approve">Approve</button>
                            <p data-kyc-review-error role="alert" hidden></p>
                        </form>
                        <details class="kyc-step-reject">
                            <summary>Reject</summary>
                            <form method="POST" action="{{ route('admin.kyc.submissions.review', $submission->id) }}" data-kyc-review data-kyc-review-confirm="Reject this KYC document?">
                                @csrf
                                <input type="hidden" name="action" value="reject">
                                <label for="kyc-rejection-{{ $submission->id }}">Rejection reason</label>
                                <textarea name="reason" id="kyc-rejection-{{ $submission->id }}" required maxlength="500" rows="3"></textarea>
                                <button type="submit" class="kyc-review-action kyc-review-action-reject">Confirm rejection</button>
                                <p data-kyc-review-error role="alert" hidden></p>
                            </form>
                        </details>
                    @endif
                </div>
            @endif

            <footer class="kyc-document-footer">
                <p>
                    @if($submission->reviewed_at)
                        Reviewed {{ $submission->reviewed_at->format('M d, Y h:i A') }}@if($submission->reviewer) by {{ $submission->reviewer->name }}@endif
                    @else
                        Awaiting review
                    @endif
                </p>
                <a href="{{ route('admin.kyc.submissions.show', ['submission' => $submission->id, 'restaurant_id' => $restaurant->id]) }}" class="kyc-review-link">{{ $canReviewSubmission ? 'Review document' : 'View submission' }} <span aria-hidden="true">→</span></a>
            </footer>
        </article>
    @empty
        <div class="kyc-empty">
            <h4>No KYC documents submitted yet</h4>
            <p>The owner's submitted KYC documents will appear here once uploaded.</p>
        </div>
    @endforelse
</div>
