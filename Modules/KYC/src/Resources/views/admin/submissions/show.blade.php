@extends('backend::layouts.app')

@php
    $ownerOnly = app(\Modules\Restaurant\Services\RestaurantApprovalAccess::class)->isOwner(auth('admin')->user());
@endphp

@section('title', 'KYC Submission Details')
@section('content-class', 'kyc-submission-content')

@section('breadcrumbs')
@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        $ownerOnly
            ? ['label' => 'KYC']
            : ['label' => 'KYC Submissions', 'url' => route('admin.kyc.submissions.index')],
        ['label' => $submission->kycType->name],
    ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'KYC document review'" />
@endsection

@section('content')
@php
    $canReviewSubmission = bouncer('admin')->hasPermission('admin.kyc.submissions.review');
    $canPreviewFiles = bouncer('admin')->hasPermission('admin.kyc.submissions.preview-file');
    $canDownloadFiles = bouncer('admin')->hasPermission('admin.kyc.submissions.download-file');
    $isSubmissionOwner = $ownerOnly
        && (int) $submission->user_id === (int) auth('admin')->id();
    $canAddMessage = $isSubmissionOwner || bouncer('admin')->hasPermission('admin.kyc.submissions.add-message');
@endphp
@include('kyc::admin.partials.kyc-styles')
<link rel="stylesheet" href="{{ asset('css/kyc-submission.css') }}?v={{ filemtime(public_path('css/kyc-submission.css')) }}">

@php
    $submissionStatus = $submission->refill_requested ? 'refill_requested' : $submission->status;
    $ownerName = optional($submission->user)->name ?? 'Unknown';
@endphp
<div class="kyc-submission">

    <!-- ================= Summary header ================= -->
    <section class="ks-hero ks-card" aria-labelledby="kyc-submission-title">
        <div class="ks-hero-main">
            <div class="ks-identity">
                <span class="ks-avatar" aria-hidden="true">{{ strtoupper(substr($ownerName, 0, 1)) }}</span>
                <div class="min-w-0">
                    <p class="ks-eyebrow">Document verification</p>
                    <div class="ks-title-row">
                        <h1 id="kyc-submission-title" class="ks-title">{{ $submission->kycType->name }}</h1>
                        @include('kyc::admin.partials.submission-status', ['status' => $submissionStatus])
                    </div>
                    <ul class="ks-meta kyc-owner-contact" aria-label="Submission summary">
                        <li>
                            <svg aria-hidden="true" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a7 7 0 0 1 16 0v1"/></svg>
                            <strong>{{ $ownerName }}</strong>
                        </li>
                        <li>
                            <svg aria-hidden="true" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                            {{ optional($submission->user)->email ?? '—' }}
                        </li>
                        <li>
                            <svg aria-hidden="true" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                            <span>Submitted <strong><time datetime="{{ $submission->created_at->toIso8601String() }}">{{ $submission->created_at->format('M d, Y · h:i A') }}</time></strong></span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="kyc-summary-actions">
                @include('backend::partials.correction-menu-button', [
                    'correctionTitle' => $submission->kycType->name,
                    'correctionButtonLabel' => 'Actions',
                    'correctionUrl' => route('admin.kyc.submissions.review', $submission->id),
                    'correctionStep' => null,
                    'correctionMode' => 'ajax',
                    'correctionAllowed' => $canReviewSubmission && !$ownerOnly,
                    'correctionDisabled' => false,
                    'correctionView' => $canPreviewFiles && $submission->files->isNotEmpty() ? '#preview-container' : null,
                    'correctionViewLabel' => 'View documents',
                ])
                @if($canReviewSubmission && !$submission->refill_requested && in_array($submission->status, ['pending', 'under_review'], true))
                <div class="kyc-review-buttons">
                    <button type="button" onclick="openRejectModal()" class="ks-btn ks-btn--danger">
                        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        Reject
                    </button>
                    <button type="button" onclick="approveSubmission()" class="ks-btn ks-btn--primary">
                        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 4 4L19 6"/></svg>
                        Approve
                    </button>
                </div>
                @endif
            </div>
        </div>

        @if($submission->rejection_reason)
            <section class="review-refill-alert" role="status">
                <span class="review-refill-alert-icon" aria-hidden="true">!</span>
                <div>
                    @if($submission->refill_requested)
                        <p class="review-refill-alert-eyebrow">ONBOARDING STEP ALERT</p>
                        <h3>KYC Documents — Refill requested</h3>
                        <p>Please refill the details or reupload {{ $submission->kycType->name }} and resubmit for review.</p>
                    @else
                        <h3>Review feedback</h3>
                    @endif
                    <p class="review-refill-alert-reason"><strong>Reason:</strong> {{ $submission->rejection_reason }}</p>
                </div>
            </section>
        @endif

        <!-- Owner's other submissions -->
        <div class="kyc-submissions-nav">
            <span>{{ $userSubmissions->count() }} {{ \Illuminate\Support\Str::plural('document', $userSubmissions->count()) }}</span>
            <nav class="wb-scroll" aria-label="Owner documents">
                @if($onboardingRestaurant)
                    <a href="{{ route('admin.restaurant.approvals.show', ['id' => $onboardingRestaurant->id, 'step' => \Modules\Restaurant\Enums\OnboardingStep::KYC->value]) }}"
                       class="ks-chip ks-chip--back"
                       title="Back to {{ $onboardingRestaurant->name }} onboarding KYC documents">
                        <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                        Onboarding
                    </a>
                @endif
                @foreach($userSubmissions as $sub)
                <a href="{{ route('admin.kyc.submissions.show', array_filter(['submission' => $sub->id, 'restaurant_id' => $onboardingRestaurant?->id])) }}"
                   @if($sub->id === $submission->id) aria-current="page" @endif
                   title="{{ $sub->kycType->name }} - {{ $sub->created_at->format('M d, Y') }}"
                   class="ks-chip">
                    {{ $sub->kycType->name }}
                    <span>{{ $sub->created_at->format('M d') }}</span>
                </a>
                @endforeach
            </nav>
        </div>
    </section>

    <!-- ================= Workbench ================= -->
    <div class="kyc-workbench">

        <!-- Viewer -->
        <section class="kyc-viewer ks-card" aria-label="Document viewer">

            <!-- File rail -->
            <div class="kyc-file-rail">
                <div class="ks-panel-label">
                    <span>Files</span>
                    <span class="wb-badge">{{ $submission->files->count() }}</span>
                </div>
                <div class="kyc-file-list wb-scroll">
                    @forelse($canPreviewFiles ? $submission->files : collect() as $file)
                    @php
                    $isImage = str_contains($file->file_type, 'image');
                    $isPdf = str_contains($file->file_type, 'pdf');
                    $previewUrl = route('admin.kyc.submissions.preview-file', [$submission->id, $file->id]);
                    $downloadUrl = $canDownloadFiles ? route('admin.kyc.submissions.download-file', [$submission->id, $file->id]) : '';
                    $fileLabel = $file->field?->field_name ?? $submission->kycType->name;
                    @endphp
                    <div tabindex="0" role="button" data-filename="{{ $fileLabel }}" aria-label="Preview {{ $fileLabel }}"
                        class="file-item {{ $loop->first ? 'is-active' : '' }}"
                        onclick="previewFile(this, '{{ $file->file_type }}', '{{ $previewUrl }}', '{{ $downloadUrl }}')"
                        onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();this.click();}">
                        <div class="file-thumb">
                            @if($isImage)
                            <img src="{{ $previewUrl }}" loading="lazy" alt="">
                            @elseif($isPdf)
                            <span class="file-thumb-type file-thumb-type--pdf">
                                <svg aria-hidden="true" width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8Z"/><path d="M14 3v5h5"/></svg>
                                PDF
                            </span>
                            @else
                            <span class="file-thumb-type file-thumb-type--other">
                                <svg aria-hidden="true" width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8Z"/><path d="M14 3v5h5M9 13h6M9 17h6"/></svg>
                                FILE
                            </span>
                            @endif
                        </div>
                        <p class="file-name" title="{{ $file->field?->field_name ?? 'Document' }}">{{ $file->field?->field_name ?? 'Document' }}</p>
                    </div>
                    @empty
                    <p class="ks-empty">{{ $canPreviewFiles ? 'No documents available' : 'You do not have permission to preview these files.' }}</p>
                    @endforelse
                </div>
            </div>

            <!-- Canvas -->
            <div class="kyc-canvas">
                <div class="kyc-canvas-header">
                    <svg aria-hidden="true" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                    <p id="preview-filename">Select a file to view</p>
                </div>

                <div id="preview-container">
                    <!-- Placeholder -->
                    <div id="preview-placeholder" class="ks-canvas-state">
                        <span class="ks-canvas-icon" aria-hidden="true">
                            <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8Z"/><path d="M14 3v5h5M9 13h6M9 17h6"/></svg>
                        </span>
                        <h4>No document selected</h4>
                        <p>Choose a file from the list to preview it.</p>
                    </div>

                    <!-- Image preview -->
                    <div id="image-wrapper" class="hidden">
                        <img id="preview-image" src="" alt="Document preview">
                    </div>

                    <!-- PDF preview -->
                    <iframe id="preview-frame" src="" title="PDF preview" class="hidden"></iframe>

                    <!-- Unavailable -->
                    <div id="preview-error" class="hidden ks-canvas-state">
                        <span class="ks-canvas-icon ks-canvas-icon--error" aria-hidden="true">
                            <svg width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 3.9 2.1 18a2 2 0 0 0 1.7 3h16.4a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4m0 4h.01"/></svg>
                        </span>
                        <p>Preview unavailable</p>
                        <p>This file type can't be displayed here. Download it to view.</p>
                        <a id="error-download-btn" href="#" class="ks-btn" @if(!$canDownloadFiles) style="display: none !important" @endif>Download file</a>
                    </div>
                </div>

                <!-- Document controls stay outside the preview canvas. -->
                <div id="preview-actions" class="hidden wb-toolbar" role="group" aria-label="Document controls">
                    <div id="image-tools">
                        <button type="button" onclick="zoomOut()" title="Zoom out" aria-label="Zoom out">
                            <svg aria-hidden="true" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2" stroke-linecap="round"><path d="M20 12H4"/></svg>
                        </button>
                        <span id="zoom-level">100%</span>
                        <button type="button" onclick="zoomIn()" title="Zoom in" aria-label="Zoom in">
                            <svg aria-hidden="true" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2" stroke-linecap="round"><path d="M12 4v16m8-8H4"/></svg>
                        </button>
                        <button type="button" onclick="rotateImage()" title="Rotate 90°" aria-label="Rotate 90 degrees">
                            <svg aria-hidden="true" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-2.6-6.4L21 8"/><path d="M21 3v5h-5"/></svg>
                        </button>
                    </div>
                    <a id="download-btn" href="#" target="_blank" rel="noopener" class="ks-btn" @if(!$canDownloadFiles) style="display: none !important" @endif>
                        <svg aria-hidden="true" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12m0 0-4-4m4 4 4-4M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
                        Download
                    </a>
                </div>
            </div>
        </section>

        <!-- Side panel with tabs -->
        <aside class="kyc-inspector ks-card" aria-label="Submission information">
            <div class="wb-scroll" role="tablist" aria-label="Submission information">
                <button type="button" role="tab" id="tab-data" aria-controls="tab-panel-data" aria-selected="true" tabindex="0" class="wb-tab is-active" data-tab="data" onclick="switchTab('data')">
                    Data<span class="wb-badge">{{ $submission->data->count() }}</span>
                </button>
                <button type="button" role="tab" id="tab-details" aria-controls="tab-panel-details" aria-selected="false" tabindex="-1" class="wb-tab" data-tab="details" onclick="switchTab('details')">
                    Details
                </button>
                <button type="button" role="tab" id="tab-messages" aria-controls="tab-panel-messages" aria-selected="false" tabindex="-1" class="wb-tab" data-tab="messages" onclick="switchTab('messages')">
                    Messages<span class="wb-badge">{{ $submission->messages->count() }}</span>
                </button>
            </div>

            <div class="kyc-inspector-content">

                <!-- Form data -->
                <div id="tab-panel-data" aria-labelledby="tab-data" class="wb-scroll" role="tabpanel">
                    @if($submission->data->isNotEmpty())
                    <dl class="ks-data-list">
                        @foreach($submission->data as $data)
                        <div class="ks-data-item">
                            <dt>{{ $data->field->field_name }}</dt>
                            @if($data->field_value === 'file_uploaded')
                                <dd class="is-file">
                                    <svg aria-hidden="true" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 4 4L19 6"/></svg>
                                    Document uploaded
                                </dd>
                            @else
                                <dd>{{ $data->field_value }}</dd>
                            @endif
                        </div>
                        @endforeach
                    </dl>
                    @else
                    <div class="ks-empty">
                        <svg aria-hidden="true" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8Z"/><path d="M14 3v5h5"/></svg>
                        No additional data submitted
                    </div>
                    @endif
                </div>

                <!-- Details -->
                <div id="tab-panel-details" aria-labelledby="tab-details" class="hidden wb-scroll" role="tabpanel">
                    <dl class="ks-detail-list">
                        <div>
                            <dt>Status</dt>
                            <dd>@include('kyc::admin.partials.submission-status', ['status' => $submissionStatus])</dd>
                        </div>
                        <div>
                            <dt>Restaurant owner</dt>
                            <dd>{{ $ownerName }}</dd>
                        </div>
                        <div>
                            <dt>Email</dt>
                            <dd>{{ optional($submission->user)->email ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt>Document type</dt>
                            <dd>{{ $submission->kycType->name }}</dd>
                        </div>
                        <div>
                            <dt>Files</dt>
                            <dd>{{ $submission->files->count() }}</dd>
                        </div>
                        <div>
                            <dt>Submitted</dt>
                            <dd><time datetime="{{ $submission->created_at->toIso8601String() }}">{{ $submission->created_at->format('M d, Y · h:i A') }}</time></dd>
                        </div>
                    </dl>
                </div>

                <!-- Messages -->
                <div id="tab-panel-messages" aria-labelledby="tab-messages" class="hidden" role="tabpanel">
                    @if($canAddMessage)
                    <form id="kyc-reply-form" class="kyc-reply-form" onsubmit="event.preventDefault(); sendMessage();">
                        <label for="message">{{ $isSubmissionOwner ? 'Reply to the review team' : 'Send a message' }}</label>
                        <textarea id="message" name="message" rows="3" required maxlength="1000" placeholder="Type your message…"></textarea>
                        <div class="kyc-reply-footer"><span>Up to 1,000 characters</span><button type="submit" id="kyc-send-message" class="ks-btn ks-btn--primary">Send message</button></div>
                        <p id="kyc-message-error" role="alert" hidden></p>
                    </form>
                    @endif
                    <div class="kyc-message-list wb-scroll" role="region" aria-label="Message conversation, newest first" tabindex="0">
                        @forelse($submission->messages->sortByDesc('created_at') as $message)
                        <article class="ks-message {{ (int) $message->sender_id === (int) auth('admin')->id() ? 'kyc-message-own' : '' }}">
                            <span class="ks-message-avatar" aria-hidden="true">{{ strtoupper(substr($message->sender_name, 0, 1)) }}</span>
                            <div class="ks-message-bubble">
                                <div class="ks-message-head">
                                    <strong>{{ $message->sender_name }}</strong>
                                    <time datetime="{{ $message->created_at->toIso8601String() }}" title="{{ $message->created_at->format('M d, Y h:i A') }}">{{ $message->created_at->diffForHumans() }}</time>
                                </div>
                                <p class="kyc-message-text">{{ $message->message }}</p>
                            </div>
                        </article>
                        @empty
                        <div class="ks-empty">
                            <svg aria-hidden="true" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2Z"/></svg>
                            No messages yet
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>

<!-- Modals (Reject & Message) -->
@include('kyc::admin.submissions.partials.modals')
@include('backend::partials.correction-dialog')

@endsection

@push('scripts')
<script>
    // Initialize with first file if available
    document.addEventListener('DOMContentLoaded', function () {
        const openMessages = sessionStorage.getItem('kyc-open-messages') === location.pathname;
        sessionStorage.removeItem('kyc-open-messages');
        switchTab(openMessages ? 'messages' : 'data');
        const tabs = Array.from(document.querySelectorAll('.wb-tab'));
        tabs.forEach((tab, index) => tab.addEventListener('keydown', function (event) {
            let next;
            if (event.key === 'ArrowRight') next = (index + 1) % tabs.length;
            else if (event.key === 'ArrowLeft') next = (index - 1 + tabs.length) % tabs.length;
            else if (event.key === 'Home') next = 0;
            else if (event.key === 'End') next = tabs.length - 1;
            else return;
            event.preventDefault();
            switchTab(tabs[next].dataset.tab);
            tabs[next].focus();
        }));
        const cooldownUntil = Number(sessionStorage.getItem(messageCooldownKey));
        if (cooldownUntil > Date.now()) startMessageCooldown(cooldownUntil);
        const firstFile = document.querySelector('.file-item');
        if (firstFile) {
            firstFile.click();
        }
    });

    let currentZoom = 1;
    let currentRotation = 0;

    // Tabs
    function switchTab(name) {
        document.querySelectorAll('.wb-tab').forEach(tab => {
            const active = tab.dataset.tab === name;
            tab.classList.toggle('is-active', active);
            tab.setAttribute('aria-selected', active ? 'true' : 'false');
            tab.tabIndex = active ? 0 : -1;
        });
        ['data', 'details', 'messages'].forEach(key => {
            document.getElementById('tab-panel-' + key).classList.toggle('hidden', key !== name);
        });
    }

    function previewFile(element, fileType, previewUrl, downloadUrl) {
        // Selection state
        document.querySelectorAll('.file-item').forEach(el => el.classList.remove('is-active'));
        element.classList.add('is-active');

        // Elements
        const placeholder = document.getElementById('preview-placeholder');
        const img = document.getElementById('preview-image');
        const imgWrapper = document.getElementById('image-wrapper');
        const frame = document.getElementById('preview-frame');
        const error = document.getElementById('preview-error');
        const actions = document.getElementById('preview-actions');
        const imageTools = document.getElementById('image-tools');
        const downloadBtn = document.getElementById('download-btn');
        const errorDownloadBtn = document.getElementById('error-download-btn');
        const filenameDisplay = document.getElementById('preview-filename');

        // Reset zoom / rotate
        currentZoom = 1;
        currentRotation = 0;
        updateTransform();

        placeholder.classList.add('hidden');
        imgWrapper.classList.add('hidden');
        frame.classList.add('hidden');
        error.classList.add('hidden');
        actions.classList.remove('hidden');
        actions.classList.add('flex');
        imageTools.classList.add('hidden');

        // Filename
        filenameDisplay.textContent = element.dataset.filename || '';

        // Download links
        downloadBtn.href = downloadUrl;
        errorDownloadBtn.href = downloadUrl;

        // Preview type
        if (fileType.includes('image')) {
            img.src = previewUrl;
            imgWrapper.classList.remove('hidden');
            imageTools.classList.remove('hidden');
        } else if (fileType.includes('pdf')) {
            frame.src = previewUrl;
            frame.classList.remove('hidden');
            if (!downloadUrl) {
                actions.classList.add('hidden');
                actions.classList.remove('flex');
            }
        } else {
            error.classList.remove('hidden');
            actions.classList.add('hidden');
            actions.classList.remove('flex');
        }
    }

    function zoomIn() {
        if (currentZoom < 3) {
            currentZoom += 0.25;
            updateTransform();
        }
    }

    function zoomOut() {
        if (currentZoom > 0.5) {
            currentZoom -= 0.25;
            updateTransform();
        }
    }

    function rotateImage() {
        currentRotation = (currentRotation + 90) % 360;
        updateTransform();
    }

    function updateTransform() {
        const imgWrapper = document.getElementById('image-wrapper');
        const zoomDisplay = document.getElementById('zoom-level');

        imgWrapper.style.transform = `scale(${currentZoom}) rotate(${currentRotation}deg)`;
        zoomDisplay.textContent = `${Math.round(currentZoom * 100)}%`;
    }

    // Status update functions
    function updateStatus(action) {
        submitReview(action);
    }

    async function approveSubmission() {
        const approved = await appConfirm({
            title: 'Approve this document?',
            message: 'The owner will be notified that this document has been approved.',
            confirmText: 'Approve',
        });
        if (approved) submitReview('approve');
    }

    function submitReview(action, reason = null) {
        const data = {
            _token: '{{ csrf_token() }}',
            action: action
        };
        if (reason) data.reason = reason;

        $.ajax({
            url: '{{ route('admin.kyc.submissions.review', $submission->id) }}',
            type: 'POST',
            data: data,
            success: function (response) {
                if (response.success) location.reload();
                else appAlert(response.message || 'The review could not be saved.', { tone: 'error', title: 'Review not saved' });
            },
            error: function (xhr) {
                appAlert(xhr.responseJSON?.message || 'Something went wrong. Please try again.', { tone: 'error', title: 'Review not saved' });
            }
        });
    }

    // Modal functions
    function openRejectModal() {
        document.getElementById('rejectModal').classList.remove('hidden');
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
        document.getElementById('rejection_reason').value = '';
    }

    function rejectSubmission() {
        const reasonInput = document.getElementById('rejection_reason');
        const reason = reasonInput.value.trim();
        if (!reason) {
            appAlert('Please provide a rejection reason.', { tone: 'warning', title: 'Reason required' }).then(() => reasonInput.focus());
            return;
        }
        submitReview('reject', reason);
    }

    const messageCooldownKey = 'kyc-message-cooldown-{{ auth('admin')->id() }}';
    let messageCooldownTimer = null;

    function startMessageCooldown(until) {
        const button = document.getElementById('kyc-send-message');
        if (!button) return;
        clearInterval(messageCooldownTimer);
        sessionStorage.setItem(messageCooldownKey, String(until));
        const update = () => {
            const seconds = Math.max(0, Math.ceil((until - Date.now()) / 1000));
            button.disabled = seconds > 0;
            button.textContent = seconds > 0 ? `Send in ${seconds}s` : 'Send message';
            if (!seconds) {
                clearInterval(messageCooldownTimer);
                messageCooldownTimer = null;
                sessionStorage.removeItem(messageCooldownKey);
            }
        };
        messageCooldownTimer = setInterval(update, 1000);
        update();
    }

    function sendMessage() {
        const form = document.getElementById('kyc-reply-form');
        if (!form || !form.reportValidity()) return;
        const input = document.getElementById('message');
        const button = document.getElementById('kyc-send-message');
        const error = document.getElementById('kyc-message-error');
        if (button.disabled) return;
        const message = input.value.trim();
        if (!message) {
            error.textContent = 'Please enter a message.';
            error.hidden = false;
            input.focus();
            return;
        }
        button.disabled = true;
        button.textContent = 'Sending…';
        error.hidden = true;
        $.ajax({
            url: '{{ route($isSubmissionOwner ? 'admin.kyc.submissions.reply' : 'admin.kyc.submissions.add-message', $submission->id) }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', message: message },
            success: function (response) {
                if (response.success) {
                    startMessageCooldown(Date.now() + 10000);
                    sessionStorage.setItem('kyc-open-messages', location.pathname);
                    location.reload();
                } else {
                    error.textContent = response.message || 'Unable to send the message.';
                    error.hidden = false;
                }
            },
            error: function (xhr) {
                if (xhr.status === 429) {
                    const seconds = Number(xhr.getResponseHeader('Retry-After') || xhr.responseJSON?.retry_after) || 10;
                    startMessageCooldown(Date.now() + seconds * 1000);
                }
                error.textContent = xhr.responseJSON?.errors?.message?.[0] || xhr.responseJSON?.message || 'Unable to send the message. Please try again.';
                error.hidden = false;
            },
            complete: function () {
                if (messageCooldownTimer !== null) return;
                button.disabled = false;
                button.textContent = 'Send message';
            }
        });
    }
</script>
@endpush
