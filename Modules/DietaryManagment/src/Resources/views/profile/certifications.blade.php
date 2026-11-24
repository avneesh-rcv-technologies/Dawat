<section class="profile-card mt-6 bg-white p-6 md:p-8" aria-labelledby="dietary-certifications-heading">
    <h2 id="dietary-certifications-heading" class="text-lg font-semibold text-gray-900">Dietary Certifications</h2>
    <p class="mt-2 text-sm text-gray-500">Upload certificates for labels such as Halal and Kosher. Each restaurant needs its own approved certificate before the label can be used on food items.</p>
    @if (session('success'))
        <p class="mt-4 rounded-lg bg-green-50 p-4 text-sm text-green-800" role="status">{{ session('success') }}</p>
    @endif
    @if ($errors->any())
        <div class="mt-4 rounded-lg bg-red-50 p-4 text-sm text-red-700" role="alert">
            @foreach ($errors->all() as $message)<p>{{ $message }}</p>@endforeach
        </div>
    @endif
    @if ($certificationRestaurants->count() > 1)
        <form action="{{ route('profile.edit') }}" method="GET" class="mt-5">
            <input type="hidden" name="tab" value="certifications">
            <label class="custom-label" for="certificate-restaurant">Restaurant</label>
            <div class="mt-2 flex gap-3">
                <select name="restaurant_id" id="certificate-restaurant" class="input-field">
                    @foreach ($certificationRestaurants as $restaurant)
                        <option value="{{ $restaurant->id }}" @selected($restaurant->id === $certificationRestaurant->id)>{{ $restaurant->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary">View</button>
            </div>
        </form>
    @endif
    <p class="mt-5 text-sm font-medium text-gray-700">{{ $certificationRestaurant->name }}</p>
    <div class="mt-4 grid grid-cols-1 gap-5 md:grid-cols-2">
        @forelse ($certificationTypes as $type)
            @php
                $certificate = $certificates->get($type->id);
                $status = $certificate?->status ?? 'not_submitted';
                $canUpload = in_array($status, ['not_submitted', 'rejected', 'changes_requested'], true);
            @endphp
            <article class="rounded-xl border border-gray-200 p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h3 class="font-semibold text-gray-900">{{ $type->dietaryLabel->name }} Certificate</h3>
                    @include('dietarymanagment::certifications.partials.status', ['status' => $status])
                </div>
                <p class="mt-3 text-sm text-gray-500">{{ $certificationEligibility[(string) $type->dietary_label_id]['message'] ?? '' }}</p>
                @if ($certificate)
                    <a href="{{ route('profile.certifications.document', $certificate->id) }}" class="mt-3 inline-block text-sm text-orange-600 underline">Download {{ $certificate->file_name ?: 'certificate' }}</a>
                    <p class="mt-2 text-xs text-gray-500">Submitted {{ $certificate->submitted_at?->format('d M Y, h:i A') ?? '—' }}</p>
                    @if ($certificate->reviewed_at)
                        <p class="mt-1 text-xs text-gray-500">Reviewed {{ $certificate->reviewed_at->format('d M Y, h:i A') }}</p>
                    @endif
                    @if ($certificate->admin_notes)
                        <div class="mt-4 rounded-lg bg-gray-50 p-3 text-sm text-gray-700">
                            <p class="font-medium">Review feedback</p>
                            <p class="mt-1 whitespace-pre-wrap">{{ $certificate->admin_notes }}</p>
                        </div>
                    @endif
                @endif
                @if ($canUpload)
                    <form action="{{ route('profile.certifications.store') }}" method="POST" enctype="multipart/form-data" class="mt-5 space-y-3">
                        @csrf
                        <input type="hidden" name="restaurant_id" value="{{ $certificationRestaurant->id }}">
                        <input type="hidden" name="certification_type_id" value="{{ $type->id }}">
                        <label for="certificate-file-{{ $type->id }}" class="block text-sm font-medium text-gray-700">{{ $certificate ? 'Replacement certificate' : 'Certificate document' }}</label>
                        <input type="file" id="certificate-file-{{ $type->id }}" name="document" accept="application/pdf,image/jpeg,image/png" required class="input-field w-full p-3" aria-describedby="certificate-help-{{ $type->id }}">
                        <p id="certificate-help-{{ $type->id }}" class="text-xs text-gray-500">PDF, JPG, or PNG · Up to 10 MB. Upload a clear, readable certificate.</p>
                        <button type="submit" class="btn-primary">{{ $certificate ? 'Upload and resubmit' : 'Submit certificate' }}</button>
                    </form>
                @endif
            </article>
        @empty
            <p class="py-4 text-sm text-gray-500">No dietary certificates are currently required.</p>
        @endforelse
    </div>
</section>
