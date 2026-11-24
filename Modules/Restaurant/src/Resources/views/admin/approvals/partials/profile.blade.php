@php
    $profileAddress = $restaurant->addresses->sortByDesc('is_default')->first();
    $profileSections = [
        'Restaurant information' => [
            'Restaurant name' => $restaurant->name,
            'Phone' => $restaurant->phone,
            'Business license' => $restaurant->business_license,
            'Food type' => $restaurant->food_type ? \Illuminate\Support\Str::headline($restaurant->food_type) : null,
            'Cuisine type' => $restaurant->cuisine_type,
            'Description' => $restaurant->description,
        ],
        'Owner information' => [
            'Owner name' => $restaurant->admin?->name,
            'Email' => $restaurant->admin?->email,
            'Phone' => $restaurant->admin?->phone,
            'Email verified' => $restaurant->admin?->email_verified_at ? 'Yes' : 'No',
        ],
        'Address' => [
            'Address line 1' => $profileAddress?->address_line_1 ?: $restaurant->address_line1,
            'Address line 2' => $profileAddress?->address_line_2 ?: $restaurant->address_line2,
            'City' => $profileAddress?->city?->name ?: ($profileAddress?->city_name ?: $restaurant->city),
            'State' => $profileAddress?->state?->name ?: $restaurant->state,
            'Country' => $profileAddress?->country?->name ?: $restaurant->country,
            'ZIP / Postal code' => $profileAddress?->zip_code ?: $restaurant->postal_code,
        ],
        'Service preferences' => [
            'Takeaway' => $restaurant->is_takeaway ? 'Yes' : 'No',
            'Delivery' => $restaurant->is_delivery ? 'Yes' : 'No',
            'Accepting orders' => $restaurant->is_accepting_orders ? 'Yes' : 'No',
            'Selected tax type' => $restaurant->tax_type ? ucfirst($restaurant->tax_type) : null,
        ],
    ];
@endphp
<div id="profile-panel">
    <div class="ar-panel-head">
        <div>
            <p class="ar-eyebrow">Overview</p>
            <h3>Profile Details</h3>
            <p class="review-help">Restaurant profile, owner contact information and service preferences.</p>
        </div>
    </div>
    <dl class="ar-status-controls">
        <div>
            <dt>Restaurant status</dt>
            <dd class="font-semibold">
                @if (bouncer('admin')->hasPermission('admin.restaurant.approvals.status.update'))
                    <form method="POST" action="{{ route('admin.restaurant.approvals.status.update', $restaurant->id) }}">
                        @csrf
                        @method('PATCH')
                        <select name="status" aria-label="Restaurant status"
                                data-status-confirm="Change restaurant status to">
                            @if (!in_array($restaurant->status, ['active', 'inactive'], true))
                                <option value="" selected disabled>{{ \Illuminate\Support\Str::headline($restaurant->status ?? 'Select status') }}</option>
                            @endif
                            <option value="active" @selected($restaurant->status === 'active')>Active</option>
                            <option value="inactive" @selected($restaurant->status === 'inactive')>Inactive</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </form>
                @else
                    {{ \Illuminate\Support\Str::headline($restaurant->status ?? 'Not set') }}
                @endif
            </dd>
        </div>
    </dl>
    @foreach($profileSections as $sectionLabel => $sectionFields)
        <section class="ar-section" aria-label="{{ $sectionLabel }}">
            <h4 class="ar-section-title">{{ $sectionLabel }}</h4>
            <dl class="ar-fields">
                @foreach($sectionFields as $label => $value)
                    <div @class(['ar-field', 'ar-field--wide' => $label === 'Description'])>
                        <dt>{{ $label }}</dt>
                        <dd @class(['is-empty' => blank($value)])>{{ filled($value) ? (is_array($value) ? implode(', ', $value) : $value) : 'Not provided' }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>
    @endforeach
</div>

@push('scripts')
<script>
    // Status selects ask for confirmation in the shared popup; cancelling restores the saved value.
    document.querySelectorAll('#profile-panel select[data-status-confirm]').forEach(select => {
        select.addEventListener('change', async () => {
            const label = select.options[select.selectedIndex].text;
            const confirmed = await appConfirm({
                title: `${select.dataset.statusConfirm} ${label}?`,
                message: 'The new status is saved immediately.',
                confirmText: 'Change status',
                tone: 'warning',
            });
            if (confirmed) select.form.requestSubmit();
            else select.form.reset();
        });
    });
</script>
@endpush
