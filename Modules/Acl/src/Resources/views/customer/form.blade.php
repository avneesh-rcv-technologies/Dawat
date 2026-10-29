
<form id="customerForm" class="form-ajax" method="POST"
    action="@isset($customer) {{ route('admin.customer.update', $customer->id) }} @else {{ route('admin.customer.store') }} @endisset">
    @csrf
    @isset($customer) @method('PUT') @endisset

    <!-- Name Field -->
    <div class="mb-4">
        <label class="custom-label">Name <span class="text-red-500">*</span></label>
        <input type="text" name="name" id="name"
            value="{{ $customer->name ?? old('name') }}"
            class="input-field @error('name') border-red-500 @enderror"
            placeholder="Enter customer name">
        <div class="error-message text-red-500 text-sm mt-1" id="name-error">
            @error('name') {{ $message }} @enderror
        </div>
    </div>

    <!-- Email Field -->
    <div class="mb-4">
        <label class="custom-label">Email <span class="text-red-500">*</span></label>
        <input type="email" name="email" id="email"
            value="{{ $customer->email ?? old('email') }}"
            class="input-field @error('email') border-red-500 @enderror"
            placeholder="Enter email address">
        <div class="error-message text-red-500 text-sm mt-1" id="email-error">
            @error('email') {{ $message }} @enderror
        </div>
    </div>

    <!-- Password Field -->
    <div class="mb-4">
        <label class="custom-label">Password @if(!isset($customer)) <span class="text-red-500">*</span> @endif</label>
        <input type="password" name="password" id="password"
            class="input-field @error('password') border-red-500 @enderror"
            placeholder="@isset($customer) Leave blank to keep current password @else Enter password @endisset">
        <div class="error-message text-red-500 text-sm mt-1" id="password-error">
            @error('password') {{ $message }} @enderror
        </div>
        @isset($customer)
            <p class="text-sm text-gray-500 mt-1">Leave blank to keep current password</p>
        @endisset
    </div>

    <!-- Phone Field -->
    <div class="mb-4">
        <label class="custom-label">Phone</label>
        <input type="text" name="phone" id="phone"
            value="{{ $customer->phone ?? old('phone') }}"
            class="input-field @error('phone') border-red-500 @enderror"
            placeholder="Enter phone number">
        <div class="error-message text-red-500 text-sm mt-1" id="phone-error">
            @error('phone') {{ $message }} @enderror
        </div>
    </div>

    <!-- Guest Method Field -->
    <!-- <div class="mb-4">
        <label class="custom-label">Guest Method</label>
        <select name="guest_method" id="guest_method" 
            class="input-field @error('guest_method') border-red-500 @enderror">
            <option value="">Select guest method</option>
            <option value="Delivery" @selected(($customer->guest_method ?? old('guest_method')) == 'Delivery')>Delivery</option>
            <option value="Pickup" @selected(($customer->guest_method ?? old('guest_method')) == 'Pickup')>Pickup</option>
        </select>
        <div class="error-message text-red-500 text-sm mt-1" id="guest_method-error">
            @error('guest_method') {{ $message }} @enderror
        </div>
    </div> -->

    <!-- Number of Guests Field -->
    <!-- <div class="mb-4">
        <label class="custom-label">Number of Guests</label>
        <input type="number" name="number_of_guests" id="number_of_guests" min="1"
            value="{{ $customer->number_of_guests ?? old('number_of_guests') }}"
            class="input-field @error('number_of_guests') border-red-500 @enderror"
            placeholder="Enter number of guests">
        <div class="error-message text-red-500 text-sm mt-1" id="number_of_guests-error">
            @error('number_of_guests') {{ $message }} @enderror
        </div>
    </div> -->

    <!-- Preferred Delivery Date Field -->
    <!-- <div class="mb-4">
        <label class="custom-label">Preferred Delivery Date</label>
        <input type="date" name="preferred_delivery_date" id="preferred_delivery_date"
            value="{{ isset($customer) ? ($customer->preferred_delivery_date ? $customer->preferred_delivery_date->format('Y-m-d') : old('preferred_delivery_date')) : old('preferred_delivery_date') }}"
            class="input-field @error('preferred_delivery_date') border-red-500 @enderror">
        <div class="error-message text-red-500 text-sm mt-1" id="preferred_delivery_date-error">
            @error('preferred_delivery_date') {{ $message }} @enderror
        </div>
    </div> -->

    <!-- Status Field -->
    <div class="mb-4">
        <label class="custom-label">Status</label>
        <select name="status" id="status" 
            class="input-field @error('status') border-red-500 @enderror">
            <option value="1" @selected(($customer->status ?? old('status', true)) == true)>Active</option>
            <option value="0" @selected(($customer->status ?? old('status', true)) == false)>Inactive</option>
        </select>
        <div class="error-message text-red-500 text-sm mt-1" id="status-error">
            @error('status') {{ $message }} @enderror
        </div>
    </div>

    <!-- Email Verified Field -->
    <div class="mb-4">
        <label class="custom-label">Email Verified</label>
        <div class="flex items-center gap-3 mt-2">
            <input type="hidden" name="email_verified" value="0">
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox"
                    name="email_verified"
                    id="email_verified"
                    value="1"
                    class="sr-only peer"
                    style="position: absolute; width: 1px; height: 1px; opacity: 0; margin: 0;"
                    role="switch"
                    aria-label="Email verified"
                    @checked((bool) ($customer->email_verified ?? old('email_verified', false)))>
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-500 rounded-full peer peer-checked:bg-green-600 transition-all"></div>
                <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-all peer-checked:translate-x-5"></div>
            </label>
            <span id="email_verified_label" class="text-sm text-gray-700">
                {{ (bool) ($customer->email_verified ?? old('email_verified', false)) ? 'Verified' : 'Unverified' }}
            </span>
        </div>
        <div class="error-message text-red-500 text-sm mt-1" id="email_verified-error">
            @error('email_verified') {{ $message }} @enderror
        </div>
    </div>

    <div class="mt-8 flex justify-end space-x-3">
        <x-button type="submit" class="primary btn-primary" label="Save" icon='' name='button' />
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const emailVerifiedCheckbox = document.getElementById('email_verified');
        const emailVerifiedLabel = document.getElementById('email_verified_label');

        if (emailVerifiedCheckbox && emailVerifiedLabel) {
            const updateLabel = () => {
                emailVerifiedLabel.textContent = emailVerifiedCheckbox.checked ? 'Verified' : 'Unverified';
            };

            emailVerifiedCheckbox.addEventListener('change', updateLabel);
            updateLabel();
        }
    });
</script>
