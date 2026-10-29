<!-- Currency -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t">
    <div class="md:col-span-1">
        <label for="currency" class="block text-sm font-medium text-gray-700">Admin Currency</label>
        <p class="mt-1 text-sm text-gray-500">Set the currency for financial values.</p>
    </div>
    <div class="md:col-span-2">
        <select id="currency" name="settings[general.currency]" class="w-full px-3 py-2 border border-gray-300 rounded-md">
            @foreach (fn_get_currencies() as $currency)
            <option value="{{ $currency->code }}" {{ old('general.currency', fn_get_setting('general.currency')) == $currency->code ? 'selected' : '' }}>
                {{ $currency->name }} ({{ $currency->code }})
            </option>
            @endforeach
        </select>
    </div>
</div>