@php
    $tax = $taxRecords->get($taxType);
    $taxStep = $stepRecords->get(\Modules\Restaurant\Enums\OnboardingStep::TAX->value);
    $canEditTax = $tax && bouncer('admin')->hasPermission('admin.restaurant.approvals.tax.update');
@endphp
<div class="space-y-6 p-6" id="tax-panel">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">Tax Information</h3>
            <p class="review-help">Review the owner's individual and business tax information.</p>
        </div>
        <form id="tax-type-selection" method="POST" action="{{ route('admin.restaurant.approvals.tax.type', $restaurant->id) }}">
            @csrf
            @method('PATCH')
            <fieldset class="flex flex-wrap items-center gap-4" @disabled(!bouncer('admin')->hasPermission('admin.restaurant.approvals.tax.type'))>
                <legend class="mb-2 text-sm font-medium">Tax type</legend>
                @foreach(['individual' => 'Individual', 'business' => 'Business'] as $value => $label)
                    <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm {{ $taxRecords->has($value) ? 'cursor-pointer' : 'cursor-not-allowed opacity-50' }}" @if(!$taxRecords->has($value)) title="No {{ strtolower($label) }} tax information has been submitted" @endif>
                        <input type="radio" name="tax_type" value="{{ $value }}" @checked($taxType === $value) @disabled(!$taxRecords->has($value)) required class="h-4 w-4 accent-amber-600">
                        {{ $label }}
                    </label>
                @endforeach
            </fieldset>
            @error('tax_type')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            @if($taxRecords->count() < 2)<p class="mt-2 text-xs text-gray-500">A tax type can be selected after its information has been submitted.</p>@endif
        </form>
    </div>
    <p class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">Only the selected tax type is used for review. Changing the tax type submits its saved information for review again.</p>
    @if($tax)
        @if($canEditTax)
            <button type="button" id="edit-tax" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold" aria-expanded="false" aria-controls="tax-edit">Edit information</button>
            @include('restaurant::admin.approvals.partials.tax-edit')
        @endif
        <div id="tax-details" class="space-y-6">
        <dl class="grid grid-cols-1 gap-4 md:grid-cols-2">
            @php
                $taxFields = $taxType === 'individual'
                    ? ['full_name' => 'Full name', 'company_legal_name' => 'Business legal entity name']
                    : ['company_legal_name' => 'Company legal name', 'doing_business_as' => 'Doing business as'];
            @endphp
            @foreach($taxFields as $field => $label)
                <div class="rounded-xl bg-gray-50 p-4"><dt class="text-xs font-semibold text-gray-500">{{ $label }}</dt><dd class="mt-2 break-words text-sm">{{ $tax->{$field} ?: 'Not provided' }}</dd></div>
            @endforeach
            <div class="rounded-xl bg-gray-50 p-4"><dt class="text-xs font-semibold text-gray-500">{{ $taxType === 'individual' ? 'SSN' : 'EIN' }}</dt><dd class="mt-2 text-sm">{{ filled($taxType === 'individual' ? $tax->ssn : $tax->ein) ? '•••• ' . substr($taxType === 'individual' ? $tax->ssn : $tax->ein, -4) : 'Not provided' }}</dd></div>
            @foreach([
                'Address' => $tax->address,
                'City' => $tax->city?->name,
                'State' => $tax->state?->name,
                'Country' => $tax->country?->name,
                'ZIP code' => $tax->zip_code,
                'Same mailing address' => $tax->same_mailing_address ? 'Yes' : 'No',
                'Receive 1099 electronically' => $tax->receive_1099_electronically ? 'Yes' : 'No',
                'Backup tax withholding' => $taxType === 'business' ? ($tax->backup_tax_withholding ? 'Yes' : 'No') : null,
                'Tax certification' => $taxType === 'business' ? ($tax->tax_certification ? 'Certified' : 'Not certified') : null,
                'Certified at' => $taxType === 'business' ? $tax->tax_certified_at?->format('M d, Y h:i A') : null,
            ] as $label => $value)
                @if($value !== null)
                <div class="rounded-xl bg-gray-50 p-4"><dt class="text-xs font-semibold text-gray-500">{{ $label }}</dt><dd class="mt-2 break-words text-sm">{{ $value ?: 'Not provided' }}</dd></div>
                @endif
            @endforeach
        </dl>
        @unless($tax->same_mailing_address)
            <div class="border-t border-gray-100 pt-5">
                <h4 class="font-semibold">Mailing address</h4>
                <dl class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    @foreach(['mailing_address' => 'Address', 'mailing_city' => 'City', 'mailing_state' => 'State', 'mailing_zip_code' => 'ZIP code'] as $field => $label)
                        <div class="rounded-xl bg-gray-50 p-4"><dt class="text-xs font-semibold text-gray-500">{{ $label }}</dt><dd class="mt-2 break-words text-sm">{{ $tax->{$field} ?: 'Not provided' }}</dd></div>
                    @endforeach
                </dl>
            </div>
        @endunless
        <dl class="grid grid-cols-1 gap-4 border-t border-gray-100 pt-5 text-sm md:grid-cols-2">
            <div><dt class="text-gray-500">Submitted</dt><dd class="mt-1">{{ $taxStep?->submitted_at?->format('M d, Y h:i A') ?? 'N/A' }}</dd></div>
            <div><dt class="text-gray-500">Last updated</dt><dd class="mt-1">{{ $tax->updated_at?->format('M d, Y h:i A') ?? 'N/A' }}</dd></div>
            <div class="md:col-span-2"><dt class="text-gray-500">Reviewed</dt><dd class="mt-1">{{ $taxStep?->reviewed_at?->format('M d, Y h:i A') ?? 'N/A' }}@if($taxStep?->reviewer) by {{ $taxStep->reviewer->name }}@endif</dd></div>
        </dl>
        </div>
    @else
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-6 text-center">
            <h4 class="font-semibold">No {{ $taxType }} tax information yet</h4>
            <p class="review-help">Information will appear here when the owner submits this tax type.</p>
        </div>
    @endif
    @if($taxStep?->rejection_reason && $taxStep->status !== 'action_required')
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-800"><h4 class="font-semibold">Rejection reason</h4><p class="mt-2 whitespace-pre-line text-sm">{{ $taxStep->rejection_reason }}</p></div>
    @endif
</div>
