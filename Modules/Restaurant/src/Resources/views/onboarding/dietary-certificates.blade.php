@php
    $profileMode = $profileMode ?? false;
@endphp
<div class="dietary-certificates">
    @if(!$profileMode)
    <p class="dietary-help">{{ $correctionData['hasUploadedCertificates'] ? 'Your uploaded dietary certificates are being reviewed. You can continue completing onboarding while we review them; correct and resubmit any rejected or refill-requested documents. All uploaded certificates must be approved before your restaurant can be activated.' : 'This step is optional until you upload a certificate. You can skip it, or upload documents for the dietary labels you plan to use.' }}</p>
    @endif
    @if($errors->any())
        <div class="dietary-error" role="alert">@foreach($errors->all() as $message)<p>{{ $message }}</p>@endforeach</div>
    @endif
    @forelse($correctionData['certificateLabels'] as $label)
        @php
            $certificate = $label->certificationType ? $correctionData['certificates']->get($label->certificationType->id) : null;
            $status = $certificate?->status ?? 'not_submitted';
            $canUpload = ($canUploadCertificates ?? true) && !in_array($status, ['approved', 'under_review'], true);
            $isImage = $certificate && in_array(strtolower(pathinfo((string) $certificate->file_name, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png'], true);
        @endphp
        <article class="dietary-card">
            <div class="dietary-heading"><h3>{{ $label->name }} certificate</h3><span class="dietary-status" data-status="{{ $status }}">{{ \Modules\DietaryManagment\Services\OnboardingCertificateService::STATUS_LABELS[$status] }}</span></div>
            @if($status !== 'approved' && $label->description)<p class="dietary-help">{{ $label->description }}</p>@endif
            @if($certificate)
                <div class="dietary-document-row">
                    <span class="dietary-filename">{{ $certificate->file_name ?: 'Certificate document' }}</span>
                    <button type="button"
                            class="dietary-preview-btn"
                            data-preview-url="{{ route('restaurant.onboarding.certificates.document', ['id' => $certificate->id, 'preview' => 1]) }}"
                            data-preview-image="{{ $isImage ? 'true' : 'false' }}"
                            data-preview-name="{{ $certificate->file_name ?: $label->name.' certificate' }}"
                            title="Preview {{ $label->name }} certificate"
                            aria-label="Preview {{ $label->name }} certificate"
                            aria-haspopup="dialog"
                            aria-controls="dietary-preview-dialog">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
                @if($status !== 'approved')
                <p class="dietary-help">Submitted: {{ $certificate->submitted_at?->format('d M Y, h:i A') ?? '—' }}@if($certificate->reviewed_at) · Reviewed: {{ $certificate->reviewed_at->format('d M Y, h:i A') }}@endif</p>
                @if($certificate->admin_notes)<div class="dietary-feedback"><strong>Review feedback</strong><p>{{ $certificate->admin_notes }}</p></div>@endif
                @endif
            @endif
            @if($canUpload)
                <form method="POST" action="{{ route($certificateUploadRoute ?? ($profileMode ? 'profile.certificates.store' : 'restaurant.onboarding.certificates.store')) }}" enctype="multipart/form-data" class="dietary-upload">
                    @csrf
                    <input type="hidden" name="restaurant_id" value="{{ $correctionRestaurant->id }}">
                    <input type="hidden" name="dietary_label_id" value="{{ $label->id }}">
                    <label for="dietary-document-{{ $label->id }}">{{ $certificate ? 'Replacement document' : 'Certificate document' }}</label>
                    <input type="file" id="dietary-document-{{ $label->id }}" name="document" accept="application/pdf,image/jpeg,image/png" required aria-describedby="dietary-help-{{ $label->id }}">
                    <p class="dietary-help" id="dietary-help-{{ $label->id }}">PDF, JPG, or PNG · Up to 10 MB. Submit a clear, readable certificate.</p>
                    <button type="submit">{{ $certificate ? 'Update and resubmit' : 'Submit certificate' }}</button>
                </form>
            @elseif($status === 'under_review')
                <p class="dietary-help">Under review. Updates are disabled until the review is complete.</p>
            @endif
        </article>
    @empty
        <p class="dietary-help">No active dietary labels currently require certification.</p>
    @endforelse
    @if(!$profileMode)
    @if(!$correctionData['awaitingCertificateApproval'])
    <form method="POST" action="{{ route('restaurant.onboarding.certificates.skip') }}" class="dietary-skip">
        @csrf
        <input type="hidden" name="restaurant_id" value="{{ $correctionRestaurant->id }}">
        <button type="submit">{{ $correctionData['hasUploadedCertificates'] ? 'Continue to KYC →' : 'Skip for now / Continue to KYC →' }}</button>
    </form>
    @else
        <div class="dietary-skip">
            <a href="{{ route('restaurant.onboarding.kyc', ['step' => 'kyc']) }}" class="dietary-continue">Continue to KYC →</a>
            <p class="dietary-feedback" role="status">Your certificates remain under review while you complete the remaining onboarding steps.</p>
        </div>
    @endif
    @endif
</div>
<dialog id="dietary-preview-dialog" class="dietary-preview-modal" aria-labelledby="dietary-preview-title">
    <div class="dietary-modal-header">
        <h4 id="dietary-preview-title">Document preview</h4>
        <form method="dialog" style="margin: 0;">
            <button type="submit" class="dietary-modal-close" aria-label="Close document preview">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" aria-hidden="true"><path d="M6 6L18 18"></path><path d="M18 6L6 18"></path></svg>
            </button>
        </form>
    </div>
    <div class="dietary-modal-body"><img id="dietary-preview-image" alt="Certificate preview"></div>
</dialog>
<style>
    .dietary-certificates { margin-top: 24px; }
    .dietary-help { margin: 10px 0; color: #A1A1AA; font-size: 13px; line-height: 1.6; }
    .dietary-card { margin-top: 20px; padding: 24px; border: 1px solid #232328; border-radius: 20px; background: #141418; box-shadow: 0 10px 28px rgba(0, 0, 0, 0.16); }
    .dietary-heading { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
    .dietary-heading h3 { margin: 0; color: #FFFFFF; font-size: 18px; font-weight: 600; }
    .dietary-status { padding: 5px 12px; border: 1px solid #383842; border-radius: 9999px; background: #202024; color: #A1A1AA; font-size: 11px; font-weight: 700; letter-spacing: 0.02em; }
    .dietary-status[data-status="approved"] { border-color: rgba(52, 211, 153, 0.3); background: rgba(52, 211, 153, 0.1); color: #6EE7B7; }
    .dietary-status[data-status="rejected"], .dietary-error { border-color: rgba(239, 68, 68, 0.3); background: rgba(239, 68, 68, 0.1); color: #FCA5A5; }
    .dietary-status[data-status="changes_requested"] { border-color: rgba(249, 115, 22, 0.3); background: rgba(249, 115, 22, 0.1); color: #FDBA74; }
    .dietary-status[data-status="submitted"], .dietary-status[data-status="under_review"] { border-color: rgba(96, 165, 250, 0.3); background: rgba(96, 165, 250, 0.1); color: #93C5FD; }
    .dietary-document-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 12px; }
    .dietary-filename { min-width: 0; overflow-wrap: anywhere; color: #D4D4D8; font-size: 13px; font-weight: 600; }
    .dietary-preview-btn { width: 32px; height: 32px; flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center; padding: 0; border: 1px solid rgba(255, 98, 16, 0.2); border-radius: 8px; background: rgba(255, 98, 16, 0.08); color: #FF6210; cursor: pointer; transition: color .15s ease, background-color .15s ease, border-color .15s ease, transform .15s ease; }
    .dietary-preview-btn:hover { border-color: #FF6210; background: rgba(255, 98, 16, 0.18); color: #FFFFFF; transform: translateY(-1px); }
    .dietary-preview-btn:focus-visible, .dietary-modal-close:focus-visible { outline: 3px solid rgba(255, 98, 16, 0.45); outline-offset: 3px; }
    .dietary-feedback, .dietary-error { margin: 16px 0; padding: 14px; border: 1px solid rgba(249, 115, 22, 0.25); border-radius: 12px; background: rgba(249, 115, 22, 0.08); color: #FDBA74; font-size: 13px; }
    .dietary-feedback p { margin: 6px 0 0; white-space: pre-wrap; overflow-wrap: anywhere; }
    .dietary-upload { margin-top: 18px; }
    .dietary-upload label { display: block; margin-bottom: 8px; color: #D4D4D8; font-size: 13px; font-weight: 600; }
    .dietary-upload input[type="file"] { display: block; width: 100%; min-width: 0; box-sizing: border-box; padding: 7px; border: 1px dashed #454550; border-radius: 12px; background: #18181D; color: #D4D4D8; font: inherit; font-size: 13px; cursor: pointer; }
    .dietary-upload input[type="file"]::file-selector-button { margin-right: 10px; padding: 8px 12px; border: 0; border-radius: 8px; background: #272730; color: #FFFFFF; font: inherit; font-size: 12px; font-weight: 600; cursor: pointer; }
    .dietary-upload input[type="file"]:hover, .dietary-upload input[type="file"]:focus { border-color: #FF6210; outline: none; }
    .dietary-upload input[type="file"]:focus-visible { box-shadow: 0 0 0 3px rgba(255, 98, 16, 0.15); }
    .dietary-upload button, .dietary-skip button, .dietary-continue { display: inline-block; padding: 12px 24px; border: 0; border-radius: 9999px; background: #FF5500; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 700; cursor: pointer; margin-top: 10px; text-decoration: none; transition: background-color 0.15s ease, transform 0.15s ease, box-shadow 0.15s ease; box-shadow: 0 4px 18px rgba(255, 85, 0, 0.22); }
    .dietary-upload button:hover, .dietary-skip button:hover, .dietary-continue:hover { background: #FF6611; transform: translateY(-1px); box-shadow: 0 6px 22px rgba(255, 85, 0, 0.32); }
    .dietary-upload button:focus-visible, .dietary-skip button:focus-visible, .dietary-continue:focus-visible { outline: 3px solid rgba(255, 98, 16, 0.45); outline-offset: 3px; }
    .dietary-skip { margin-top: 24px; padding-top: 24px; border-top: 1px solid #232328; }
    .dietary-preview-modal { width: 100%; max-width: min(92vw, 680px); max-height: min(88dvh, 760px); padding: 0; overflow: hidden; border: 1px solid #27272A; border-radius: 20px; background: #121215; color: #FFFFFF; box-shadow: 0 25px 60px rgba(0, 0, 0, .85); }
    .dietary-preview-modal::backdrop { background: rgba(0, 0, 0, .75); backdrop-filter: blur(4px); }
    .dietary-modal-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 18px 24px; border-bottom: 1px solid #232328; background: #141418; }
    .dietary-modal-header h4 { margin: 0; overflow: hidden; color: #FFFFFF; font-size: 16px; font-weight: 600; text-overflow: ellipsis; white-space: nowrap; }
    .dietary-preview-modal .dietary-modal-close { display: inline-flex !important; width: 36px !important; min-width: 36px !important; height: 36px !important; min-height: 36px !important; flex-shrink: 0; align-items: center; justify-content: center; padding: 0 !important; border: 1px solid #383842 !important; border-radius: 10px !important; background: #1F1F26 !important; color: #A1A1AA !important; cursor: pointer; }
    .dietary-preview-modal .dietary-modal-close svg { display: block; width: 18px; height: 18px; stroke: #A1A1AA; }
    .dietary-preview-modal .dietary-modal-close:hover { border-color: rgba(239, 68, 68, .45) !important; background: rgba(239, 68, 68, .14) !important; color: #FCA5A5 !important; }
    .dietary-preview-modal .dietary-modal-close:hover svg { stroke: #FCA5A5; }
    .dietary-modal-body { display: flex; min-height: 180px; max-height: calc(min(88dvh, 760px) - 73px); align-items: center; justify-content: center; overflow: auto; padding: 24px; background: #0A0A0C; }
    .dietary-modal-body img { max-width: 100%; max-height: 64vh; border: 1px solid #27272A; border-radius: 12px; object-fit: contain; }
    @media(max-width: 600px) { .dietary-card { padding: 18px; } .dietary-upload button, .dietary-skip button, .dietary-continue { width: 100%; text-align: center; } }
</style>
<script>
(() => {
    const dialog = document.getElementById('dietary-preview-dialog');
    const image = document.getElementById('dietary-preview-image');
    const title = document.getElementById('dietary-preview-title');
    if (!dialog || !image || !title) return;

    dialog.addEventListener('close', () => image.removeAttribute('src'));
    document.querySelectorAll('.dietary-preview-btn').forEach(button => {
        button.addEventListener('click', () => {
            const url = button.dataset.previewUrl;
            if (!url) return;
            if (button.dataset.previewImage === 'true') {
                image.src = url;
                image.alt = button.dataset.previewName || 'Certificate preview';
                title.textContent = button.dataset.previewName || 'Document preview';
                dialog.showModal();
            } else {
                window.open(url, '_blank', 'noopener,noreferrer');
            }
        });
    });
})();
</script>
