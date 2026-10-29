<div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t">
    <div class="md:col-span-1">
        <label class="text-gray-600 text-lg font-semibold">Company Business Address</label>
        <p class="mt-1 text-sm text-gray-500">Enter detailed business address.</p>
    </div>
    <div class="md:col-span-2 space-y-4">
        <div>
            <label for="address_street" class="custom-label">Street</label>
            <input type="text" id="address_street" name="settings[general.company.address_street]"
                value="{{ old('general.company.address_street', fn_get_setting('general.company.address_street')) }}"
                class="input-field w-full">
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="address_city" class="custom-label">City</label>
                <input type="text" id="address_city" name="settings[general.company.address_city]"
                    value="{{ old('general.company.address_city', fn_get_setting('general.company.address_city')) }}"
                    class="input-field w-full">
            </div>
            <div>
                <label for="address_state" class="custom-label">State</label>
                <input type="text" id="address_state" name="settings[general.company.address_state]"
                    value="{{ old('general.company.address_state', fn_get_setting('general.company.address_state')) }}"
                    class="input-field w-full">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="address_postal" class="custom-label">Postal Code</label>
                <input type="text" id="address_postal" name="settings[general.company.address_postal]"
                    value="{{ old('general.company.address_postal', fn_get_setting('general.company.address_postal')) }}"
                    class="input-field w-full">
            </div>
            <div>
                <x-advanced-selector name="settings[general.company.country]" table="countries" search-column="name" value-column="code"
                    display-column="name" :multiple="false" :required="false"
                    :selected="fn_get_setting('general.company.country') ? [fn_get_setting('general.company.country')] : []" placeholder="Select country..."
                    label="Country" :additional-columns="['code']" :filters="[]" order-by="name"
                    order-direction="asc" />


            </div>
        </div>
    </div>
</div>