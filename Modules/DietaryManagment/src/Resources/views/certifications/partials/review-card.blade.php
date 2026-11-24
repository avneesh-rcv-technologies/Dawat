<article class="rounded-xl border border-gray-200 bg-white p-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h4 class="font-semibold text-gray-900">{{ $certificate->certificationType?->name ?? 'Certificate' }}</h4>
        @include('dietarymanagment::certifications.partials.status', ['status' => $certificate->status])
    </div>
    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-lg bg-gray-50 p-4">
        <p class="min-w-0 break-words text-sm text-gray-700">{{ $certificate->file_name ?: 'Uploaded certificate' }}</p>
        <a href="{{ route('restaurant.onboarding.certificates.document', ['id' => $certificate->id, 'preview' => 1]) }}" target="_blank" rel="noopener noreferrer" class="shrink-0 text-sm font-medium text-orange-600 underline">View document <span class="sr-only">(opens in a new tab)</span></a>
    </div>
    @if($certificate->status !== 'approved' && $certificate->admin_notes)<p class="mt-4 whitespace-pre-wrap rounded-lg bg-gray-50 p-3 text-sm">{{ $certificate->admin_notes }}</p>@endif
    @if(in_array($certificate->status, ['submitted', 'under_review'], true) && \Modules\DietaryManagment\Services\OnboardingCertificateService::canReview(auth('admin')->user()))
        <form method="POST" action="{{ route('admin.dietarymanagment.certifications.review', $certificate->id) }}" class="mt-5 space-y-4">
            @csrf
            @method('PATCH')
            <input type="hidden" name="document_version" value="{{ $certificate->documentVersion() }}">
            <input type="hidden" name="return_to_onboarding" value="{{ ($returnToOnboarding ?? false) ? 1 : 0 }}">
            <div><label for="certificate-status-{{ $certificate->id }}" class="block text-sm font-medium">Review action</label>
                <select name="status" id="certificate-status-{{ $certificate->id }}" class="input-field mt-2" required>
                    <option value="">Select action</option>
                    @foreach(['under_review' => 'Mark under review', 'approved' => 'Approve', 'rejected' => 'Reject', 'changes_requested' => 'Request changes'] as $value => $label)
                        @if($certificate->status !== $value)<option value="{{ $value }}">{{ $label }}</option>@endif
                    @endforeach
                </select>
            </div>
            <div><label for="certificate-notes-{{ $certificate->id }}" class="block text-sm font-medium">Review notes</label>
                <textarea name="admin_notes" id="certificate-notes-{{ $certificate->id }}" class="input-field mt-2" rows="3" maxlength="1000">{{ old('admin_notes') }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Required for rejection or change requests. The owner will see these notes.</p>
            </div>
            <button type="submit" class="btn-primary">Save review</button>
        </form>
    @elseif(in_array($certificate->status, ['rejected', 'changes_requested'], true))
        <p class="mt-4 text-sm text-gray-500">Waiting for the owner to upload a replacement document.</p>
    @endif
</article>
