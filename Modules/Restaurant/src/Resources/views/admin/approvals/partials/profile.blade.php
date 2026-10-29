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
<div id="profile-panel" class="space-y-6 p-6">
    <div>
        <h3 class="text-lg font-semibold text-gray-900">Profile Details</h3>
        <p class="review-help">Restaurant profile, owner contact information and service preferences.</p>
    </div>
    <dl class="grid grid-cols-1 gap-4 rounded-xl border border-gray-100 bg-gray-50 p-4 text-sm md:grid-cols-2">
        <div>
            <dt class="text-gray-500">Restaurant status</dt>
            <dd class="mt-2 font-semibold">
                @if (bouncer('admin')->hasPermission('admin.restaurant.approvals.status.update'))
                    <form method="POST" action="{{ route('admin.restaurant.approvals.status.update', $restaurant->id) }}">
                        @csrf
                        @method('PATCH')
                        <select name="status" aria-label="Restaurant status" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500"
                                onchange="if (window.confirm('Are you sure you want to change the restaurant status to ' + this.options[this.selectedIndex].text + '?')) { this.form.requestSubmit(); } else { this.form.reset(); }">
                            @if (!in_array($restaurant->status, ['active', 'inactive', 'draft'], true))
                                <option value="" selected disabled>{{ \Illuminate\Support\Str::headline($restaurant->status ?? 'Select status') }}</option>
                            @endif
                            <option value="active" @selected($restaurant->status === 'active')>Active</option>
                            <option value="inactive" @selected($restaurant->status === 'inactive')>Inactive</option>
                            <option value="draft" @selected($restaurant->status === 'draft')>Draft</option>
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
        <div>
            <dt class="text-gray-500">Onboarding status</dt>
            <dd class="mt-2">
                @if (bouncer('admin')->hasPermission('admin.restaurant.approvals.onboarding-status.update'))
                    <form method="POST" action="{{ route('admin.restaurant.approvals.onboarding-status.update', $restaurant->id) }}">
                        @csrf
                        @method('PATCH')
                        @php
                            $onboardingOptions = [
                                \Modules\Restaurant\Enums\OnboardingStatus::DRAFT,
                                \Modules\Restaurant\Enums\OnboardingStatus::UNDER_REVIEW,
                                \Modules\Restaurant\Enums\OnboardingStatus::ACTION_REQUIRED,
                                \Modules\Restaurant\Enums\OnboardingStatus::APPROVED,
                            ];
                        @endphp
                        <select name="onboarding_status" aria-label="Onboarding status" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold focus:border-blue-500 focus:ring-blue-500"
                                onchange="if (window.confirm('Are you sure you want to change the onboarding status to ' + this.options[this.selectedIndex].text + '?')) { this.form.requestSubmit(); } else { this.form.reset(); }">
                            @if (!in_array($restaurant->onboarding_status, $onboardingOptions, true))
                                <option value="" selected disabled>{{ $restaurant->onboarding_status?->label() ?? 'Select status' }}</option>
                            @endif
                            @foreach ($onboardingOptions as $option)
                                <option value="{{ $option->value }}" @selected($restaurant->onboarding_status === $option)>{{ $option->label() }}</option>
                            @endforeach
                        </select>
                        @error('onboarding_status')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </form>
                @else
                    <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $restaurant->onboarding_status?->color() ?? 'bg-gray-100 text-gray-600' }}">{{ $restaurant->onboarding_status?->label() ?? 'Not started' }}</span>
                @endif
            </dd>
        </div>
    </dl>
    @foreach($profileSections as $sectionLabel => $sectionFields)
        <section class="border-t border-gray-100 pt-5" aria-label="{{ $sectionLabel }}">
            <h4 class="mb-4 text-sm font-semibold text-gray-900">{{ $sectionLabel }}</h4>
            <dl class="grid grid-cols-1 gap-4 md:grid-cols-2">
                @foreach($sectionFields as $label => $value)
                    <div class="rounded-xl bg-gray-50 p-4">
                        <dt class="text-xs font-semibold text-gray-500">{{ $label }}</dt>
                        <dd class="mt-2 whitespace-pre-line break-words text-sm">{{ filled($value) ? (is_array($value) ? implode(', ', $value) : $value) : 'Not provided' }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>
    @endforeach
</div>
