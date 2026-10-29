<form
    action="{{ route('admin.shipping_methods.update', $shipping->id) }}"
    method="POST" class="form-ajax" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <input type="radio" name="tab" id="tab-general" class="hidden" checked>
    <input type="radio" name="tab" id="tab-config" class="hidden">

    <!-- Tab buttons (labels toggle the radios) -->
    <div class="border-b border-gray-200 mb-4">
        <div class="flex space-x-6">
            <label for="tab-general"
                class="cursor-pointer pb-2 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700">
                General
            </label>

            <label for="tab-config"
                class="cursor-pointer pb-2 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700">
                Configuration
            </label>
        </div>
    </div>

    <!-- Tab contents wrapper -->
    <div class="tab-contents">
        <!-- General content (hidden by default; shown when #tab-general is checked) -->
        <div class="tab-general space-y-4 p-1">
            <div>
                <label for="processor" class="custom-label">Shipping Method</label>
                <select id="processor" name="processor" class="input-field" required>
                    <option value="">-- Select Shipping Method --</option>
                    @foreach($processors as $processor)
                        <option value="{{ $processor->code }}" 
                                {{ $shipping->shippingMethod->code == $processor->code ? 'selected' : '' }}>
                            {{ $processor->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="custom-label">Status</label>
                <select name="shipping_data[is_active]" class="input-field">
                    <option value="1" {{ ($shipping->shippingMethod->is_active ?? true) ? 'selected' : '' }}>
                        Active
                    </option>
                    <option value="0" {{ !($shipping->shippingMethod->is_active ?? true) ? 'selected' : '' }}>
                        Inactive
                    </option>
                </select>
            </div>
        </div>

        <!-- Configuration content (hidden by default; shown when #tab-config is checked) -->
        <div class="tab-config space-y-4 p-1" id="shipping_configuration">
            @if($shipping->shippingMethod && $shipping->shippingMethod->template)
                @include($shipping->shippingMethod->template, ['configuration' => $shipping->configuration ?? []])
            @endif
        </div>
    </div>

    <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="{{ route('admin.shipping_methods.index') }}" class="btn-cancel">Cancel</a>

        <button type="submit" name="button" class="btn-primary">
            Update Shipping Method
        </button>
    </div>
</form>

<!-- Small CSS controlling which .tab-* is visible -->
<style>
    /* default hide both (in case of Tailwind interference) */
    .tab-contents .tab-general,
    .tab-contents .tab-config {
        display: none;
    }

    /* show General when its radio is checked */
    #tab-general:checked~.tab-contents .tab-general {
        display: block;
    }

    /* show Config when its radio is checked */
    #tab-config:checked~.tab-contents .tab-config {
        display: block;
    }
</style>