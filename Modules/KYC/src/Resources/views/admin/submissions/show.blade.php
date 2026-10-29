@extends('backend::layouts.app')

@section('title', 'KYC Submission Details')

@section('breadcrumbs')
@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'KYC Submissions', 'url' => route('admin.kyc.submissions.index')],
        ['label' => 'Submission #' . $submission->id],
    ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Submission #' . $submission->id" />
@endsection

@section('content')
@php
    $canReviewSubmission = bouncer('admin')->hasPermission('admin.kyc.submissions.review');
    $canPreviewFiles = bouncer('admin')->hasPermission('admin.kyc.submissions.preview-file');
    $canDownloadFiles = bouncer('admin')->hasPermission('admin.kyc.submissions.download-file');
    $canAddMessage = bouncer('admin')->hasPermission('admin.kyc.submissions.add-message');
@endphp
@include('kyc::admin.partials.kyc-styles')

<style>
    .wb-scroll { scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent; }
    .wb-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
    .wb-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }

    .wb-tab { position: relative; padding: .75rem 1rem; font-size: .875rem; font-weight: 500; color: #64748b;
              border-bottom: 2px solid transparent; white-space: nowrap; transition: color .15s, border-color .15s; }
    .wb-tab:hover { color: #0f172a; }
    .wb-tab.is-active { color: #03502B; border-bottom-color: #03502B; }
    .wb-tab:focus-visible { outline: 2px solid #03502B; outline-offset: -2px; border-radius: 6px; }

    .wb-badge { display: inline-flex; align-items: center; justify-content: center; min-width: 1.25rem; height: 1.25rem;
                padding: 0 .375rem; margin-left: .375rem; border-radius: 9999px; font-size: .6875rem; font-weight: 600;
                background: #f1f5f9; color: #475569; }
    .wb-tab.is-active .wb-badge { background: #e6f2ec; color: #03502B; }

    .file-item.is-active { border-color: #03502B; box-shadow: 0 0 0 3px rgba(3, 80, 43, .18); }
    .file-item:focus-visible { outline: 2px solid #03502B; outline-offset: 2px; }

    .wb-toolbar button, .wb-toolbar a { color: #e2e8f0; }
    .wb-toolbar button:hover { background: rgba(255,255,255,.12); }
    .wb-toolbar button:focus-visible { outline: 2px solid #fff; outline-offset: 1px; }

    @media (prefers-reduced-motion: reduce) { #image-wrapper { transition: none !important; } }
</style>

<div class="max-w-full mx-auto space-y-4">

    <!-- ================= Summary header ================= -->
    <div class="kyc-panel">
        <div class="px-4 sm:px-6 py-4 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-center gap-4 min-w-0">
                <div class="h-12 w-12 flex-shrink-0 rounded-full bg-[#03502B] text-white flex items-center justify-center text-lg font-semibold">
                    {{ strtoupper(substr(optional($submission->user)->name ?? 'U', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                        <h1 class="text-lg font-semibold text-gray-900 truncate">{{ $submission->kycType->name }}</h1>
                        @include('kyc::admin.partials.submission-status', ['status' => $submission->refill_requested ? 'refill_requested' : $submission->status])
                    </div>
                    <p class="text-sm text-gray-600 truncate">
                        {{ optional($submission->user)->name ?? 'Unknown' }}
                        <span class="text-gray-400">&nbsp;|&nbsp;</span>
                        {{ optional($submission->user)->email ?? '—' }}
                    </p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Submission #{{ $submission->id }} submitted {{ $submission->created_at->format('M d, Y H:i') }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                @include('backend::partials.correction-menu-button', [
                    'correctionTitle' => $submission->kycType->name,
                    'correctionUrl' => route('admin.kyc.submissions.review', $submission->id),
                    'correctionStep' => null,
                    'correctionMode' => 'ajax',
                    'correctionAllowed' => $canReviewSubmission && !app(\Modules\Restaurant\Services\RestaurantApprovalAccess::class)->isOwner(auth('admin')->user()),
                    'correctionDisabled' => false,
                    'correctionView' => $canPreviewFiles && $submission->files->isNotEmpty() ? '#preview-container' : null,
                    'correctionViewLabel' => 'View documents',
                ])
            @if($canReviewSubmission && ($submission->status === 'pending' || $submission->refill_requested))
            <div class="flex flex-wrap items-center gap-2 lg:flex-shrink-0">
                <button type="button" onclick="updateStatus('under_review')" class="btn-secondary justify-center flex-1 sm:flex-none">
                    Start review
                </button>
            </div>
            @elseif($canReviewSubmission && $submission->status === 'under_review')
            <div class="flex flex-wrap items-center gap-2 lg:flex-shrink-0">
                <button type="button" onclick="openRejectModal()"
                    class="btn-secondary justify-center flex-1 sm:flex-none text-red-800 border-red-200 hover:bg-red-50">
                    Reject
                </button>
                <button type="button" onclick="approveSubmission()" class="btn-primary justify-center flex-1 sm:flex-none">
                    Approve
                </button>
            </div>
            @endif
            </div>
        </div>

        @if($submission->rejection_reason)
            <section class="review-refill-alert" role="status">
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
        <div class="border-t border-gray-100 px-4 sm:px-6 py-2.5 flex items-center gap-3 bg-gray-50/60 rounded-b-xl">
            <span class="text-xs font-medium text-gray-500 flex-shrink-0">
                {{ $userSubmissions->count() }} {{ \Illuminate\Support\Str::plural('submission', $userSubmissions->count()) }}
            </span>
            <nav class="flex gap-2 overflow-x-auto wb-scroll py-1" aria-label="Owner submissions">
                @if($onboardingRestaurant)
                    <a href="{{ route('admin.restaurant.approvals.show', ['id' => $onboardingRestaurant->id, 'step' => \Modules\Restaurant\Enums\OnboardingStep::KYC->value]) }}"
                       class="flex-shrink-0 inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:border-[#03502B] hover:text-[#03502B]"
                       title="Back to {{ $onboardingRestaurant->name }} onboarding KYC documents">
                        <span aria-hidden="true">←</span> Onboarding
                    </a>
                @endif
                @foreach($userSubmissions as $sub)
                <a href="{{ route('admin.kyc.submissions.show', array_filter(['submission' => $sub->id, 'restaurant_id' => $onboardingRestaurant?->id])) }}"
                   @if($sub->id === $submission->id) aria-current="page" @endif
                   title="{{ $sub->kycType->name }} - {{ $sub->created_at->format('M d, Y') }}"
                   class="flex-shrink-0 inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-medium transition-colors
                          {{ $sub->id === $submission->id
                              ? 'bg-[#03502B] border-[#03502B] text-white'
                              : 'bg-white border-gray-200 text-gray-700 hover:border-[#03502B] hover:text-[#03502B]' }}">
                    {{ $sub->kycType->name }}
                    <span class="{{ $sub->id === $submission->id ? 'text-white/70' : 'text-gray-400' }}">{{ $sub->created_at->format('M d') }}</span>
                </a>
                @endforeach
            </nav>
        </div>
    </div>

    <!-- ================= Workbench ================= -->
    <div class="grid grid-cols-12 gap-4 xl:h-[calc(100vh-19rem)] xl:min-h-[30rem]">

        <!-- Viewer -->
        <section class="col-span-12 xl:col-span-8 kyc-panel overflow-hidden flex flex-col md:flex-row min-h-[26rem] xl:min-h-0 xl:h-full">

            <!-- File rail -->
            <div class="order-2 md:order-1 md:w-36 flex-shrink-0 border-t md:border-t-0 md:border-r border-gray-100 bg-white">
                <div class="hidden md:flex items-center justify-between px-3 py-2.5 border-b border-gray-100">
                    <span class="text-xs font-semibold text-gray-700">Files</span>
                    <span class="wb-badge !ml-0">{{ $submission->files->count() }}</span>
                </div>
                <div class="flex md:flex-col gap-3 p-3 overflow-x-auto md:overflow-x-hidden md:overflow-y-auto wb-scroll md:h-[calc(100%-2.75rem)]">
                    @forelse($canPreviewFiles ? $submission->files : collect() as $file)
                    @php
                    $isImage = str_contains($file->file_type, 'image');
                    $isPdf = str_contains($file->file_type, 'pdf');
                    $previewUrl = route('admin.kyc.submissions.preview-file', [$submission->id, $file->id]);
                    $downloadUrl = $canDownloadFiles ? route('admin.kyc.submissions.download-file', [$submission->id, $file->id]) : '';
                    @endphp
                    <div tabindex="0" role="button" data-filename="{{ basename($file->file_path) }}"
                        class="file-item flex-shrink-0 w-28 md:w-full cursor-pointer rounded-lg border border-gray-200 bg-white overflow-hidden transition-shadow hover:shadow-sm {{ $loop->first ? 'is-active' : '' }}"
                        onclick="previewFile(this, '{{ $file->file_type }}', '{{ $previewUrl }}', '{{ $downloadUrl }}')"
                        onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();this.click();}">

                        <div class="aspect-[4/3] bg-gray-50 flex items-center justify-center">
                            @if($isImage)
                            <img src="{{ $previewUrl }}" loading="lazy" class="w-full h-full object-cover" alt="{{ $file->field->field_name }}">
                            @elseif($isPdf)
                            <div class="flex flex-col items-center text-red-500">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span class="text-[10px] font-semibold">PDF</span>
                            </div>
                            @else
                            <div class="flex flex-col items-center text-gray-400">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span class="text-[10px] font-semibold">FILE</span>
                            </div>
                            @endif
                        </div>
                        <div class="px-2 py-1.5">
                            <p class="text-xs font-medium text-gray-800 truncate" title="{{ $file->field->field_name }}">{{ $file->field->field_name }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-gray-500 py-2 px-1">{{ $canPreviewFiles ? 'No documents available' : 'You do not have permission to preview these files.' }}</p>
                    @endforelse
                </div>
            </div>

            <!-- Canvas -->
            <div class="order-1 md:order-2 flex-1 min-w-0 flex flex-col">
                <div class="px-4 py-2.5 border-b border-gray-100 bg-white flex items-center gap-2 min-w-0">
                    <svg class="h-4 w-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <p class="text-sm font-medium text-gray-800 truncate" id="preview-filename">Select a file to view</p>
                </div>

                <div class="flex-1 min-h-[18rem] xl:min-h-0 bg-slate-900 relative overflow-hidden flex items-center justify-center p-4 sm:p-6"
                    id="preview-container">

                    <!-- Placeholder -->
                    <div id="preview-placeholder" class="text-center relative z-10 px-4">
                        <div class="inline-flex p-4 rounded-full bg-white/5 ring-1 ring-white/10 mb-3">
                            <svg class="h-9 w-9 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h4 class="text-slate-100 font-medium mb-1">No document selected</h4>
                        <p class="text-sm text-slate-400">Choose a file from the list to preview it.</p>
                    </div>

                    <!-- Image preview -->
                    <div id="image-wrapper"
                        class="absolute inset-0 flex items-center justify-center transition-transform duration-200 ease-out hidden p-4 sm:p-6 pb-20"
                        style="transform-origin: center center;">
                        <img id="preview-image" src=""
                            class="max-w-full max-h-full w-auto h-auto object-contain rounded-md shadow-2xl bg-white"
                            alt="Document preview">
                    </div>

                    <!-- PDF preview -->
                    <iframe id="preview-frame" src="" title="PDF preview"
                        class="hidden w-full h-full rounded-md bg-white z-10"></iframe>

                    <!-- Unavailable -->
                    <div id="preview-error" class="hidden text-center relative z-10 px-4">
                        <div class="inline-flex p-4 rounded-full bg-red-500/10 ring-1 ring-red-400/30 mb-3">
                            <svg class="h-8 w-8 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <p class="text-slate-100 font-medium mb-1">Preview unavailable</p>
                        <p class="text-xs text-slate-400 mb-4">This file type can't be displayed here. Download it to view.</p>
                        <a id="error-download-btn" href="#" @if(!$canDownloadFiles) style="display: none !important" @endif
                            class="inline-flex items-center px-4 py-2 bg-white text-sm font-medium rounded-lg text-gray-800 hover:bg-gray-100 transition-colors">
                            Download file
                        </a>
                    </div>

                    <!-- Floating toolbar -->
                    <div id="preview-actions"
                        class="hidden wb-toolbar absolute bottom-4 left-1/2 -translate-x-1/2 z-20 items-center gap-1 rounded-full bg-slate-800/95 ring-1 ring-white/10 shadow-xl px-2 py-1.5 backdrop-blur">
                        <div id="image-tools" class="flex items-center gap-1">
                            <button type="button" onclick="zoomOut()" class="p-2 rounded-full transition-colors" title="Zoom out" aria-label="Zoom out">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                </svg>
                            </button>
                            <span class="text-xs font-medium text-slate-200 w-11 text-center tabular-nums" id="zoom-level">100%</span>
                            <button type="button" onclick="zoomIn()" class="p-2 rounded-full transition-colors" title="Zoom in" aria-label="Zoom in">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
                            <button type="button" onclick="rotateImage()" class="p-2 rounded-full transition-colors" title="Rotate 90°" aria-label="Rotate 90 degrees">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </button>
                            <span class="h-5 w-px bg-white/15 mx-1"></span>
                        </div>
                        <a id="download-btn" href="#" target="_blank" rel="noopener" @if(!$canDownloadFiles) style="display: none !important" @endif
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium hover:bg-white/10 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Download
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Side panel with tabs -->
        <aside class="col-span-12 xl:col-span-4 kyc-panel overflow-hidden flex flex-col max-h-[36rem] xl:max-h-none xl:h-full">
            <div class="border-b border-gray-100 px-2 flex overflow-x-auto wb-scroll" role="tablist">
                <button type="button" role="tab" class="wb-tab is-active" data-tab="data" onclick="switchTab('data')">
                    Form data<span class="wb-badge">{{ $submission->data->count() }}</span>
                </button>
                <button type="button" role="tab" class="wb-tab" data-tab="details" onclick="switchTab('details')">
                    Details
                </button>
                <button type="button" role="tab" class="wb-tab" data-tab="messages" onclick="switchTab('messages')">
                    Messages<span class="wb-badge">{{ $submission->messages->count() }}</span>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto wb-scroll">

                <!-- Form data -->
                <div id="tab-panel-data" class="p-4 sm:p-5 space-y-4" role="tabpanel">
                    @forelse($submission->data as $data)
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ $data->field->field_name }}</label>
                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm font-medium text-gray-900 break-words">
                            {{ $data->field_value }}
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-10 text-gray-500">
                        <p class="text-sm">No additional data submitted</p>
                    </div>
                    @endforelse
                </div>

                <!-- Details -->
                <div id="tab-panel-details" class="hidden p-4 sm:p-5" role="tabpanel">
                    <dl class="divide-y divide-gray-100">
                        <div class="flex items-center justify-between gap-4 py-3">
                            <dt class="text-sm text-gray-500">Status</dt>
                            <dd>@include('kyc::admin.partials.submission-status', ['status' => $submission->status])</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-3">
                            <dt class="text-sm text-gray-500">Restaurant owner</dt>
                            <dd class="text-sm font-medium text-gray-900 text-right break-words">{{ optional($submission->user)->name ?? 'Unknown' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-3">
                            <dt class="text-sm text-gray-500">Email</dt>
                            <dd class="text-sm font-medium text-gray-900 text-right break-all">{{ optional($submission->user)->email ?? '—' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-3">
                            <dt class="text-sm text-gray-500">Document type</dt>
                            <dd class="text-sm font-medium text-gray-900 text-right">{{ $submission->kycType->name }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-3">
                            <dt class="text-sm text-gray-500">Submitted</dt>
                            <dd class="text-sm font-medium text-gray-900 text-right">{{ $submission->created_at->format('M d, Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Messages -->
                <div id="tab-panel-messages" class="hidden p-4 sm:p-5" role="tabpanel">
                    @if($canAddMessage)
                    <button type="button" onclick="openMessageModal()" class="btn-secondary w-full justify-center mb-4">
                        + Add message
                    </button>
                    @endif
                    <div class="space-y-3">
                        @forelse($submission->messages->sortByDesc('created_at') as $message)
                        <div class="flex gap-3">
                            <div class="flex-shrink-0">
                                <div class="h-8 w-8 rounded-full bg-[#03502B] flex items-center justify-center text-white text-xs font-semibold">
                                    {{ strtoupper(substr($message->sender_name, 0, 1)) }}
                                </div>
                            </div>
                            <div class="flex-1 min-w-0 bg-gray-50 border border-gray-100 rounded-lg p-3">
                                <div class="flex flex-wrap justify-between items-baseline gap-x-2">
                                    <span class="text-sm font-medium text-gray-900 truncate">{{ $message->sender_name }}</span>
                                    <span class="text-xs text-gray-500">{{ $message->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="mt-1 text-sm text-gray-700 break-words">{{ $message->message }}</p>
                            </div>
                        </div>
                        @empty
                        <p class="text-sm text-gray-500 text-center py-8">No messages yet</p>
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

    function approveSubmission() {
        if (confirm('Are you sure you want to approve this submission?')) {
            submitReview('approve');
        }
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
                else alert('Error: ' + response.message);
            },
            error: function (xhr) {
                alert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'));
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
        var reason = document.getElementById('rejection_reason').value;
        if (!reason) {
            alert('Please provide a rejection reason');
            return;
        }
        submitReview('reject', reason);
    }

    function openMessageModal() {
        document.getElementById('messageModal').classList.remove('hidden');
    }

    function closeMessageModal() {
        document.getElementById('messageModal').classList.add('hidden');
        document.getElementById('message').value = '';
    }

    function sendMessage() {
        var message = document.getElementById('message').value;
        if (!message) {
            alert('Please enter a message');
            return;
        }

        $.ajax({
            url: '{{ route('admin.kyc.submissions.add-message', $submission->id) }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                message: message
            },
            success: function (response) {
                if (response.success) location.reload();
                else alert('Error: ' + response.message);
            },
            error: function (xhr) {
                alert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'));
            }
        });
    }
</script>
@endpush
