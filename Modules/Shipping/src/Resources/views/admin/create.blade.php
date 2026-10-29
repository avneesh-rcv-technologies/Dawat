<form
    action="{{ route('admin.shipping_methods.store') }}"
    method="POST" class="form-ajax" enctype="multipart/form-data">
    @csrf

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
                <label for="name" class="custom-label">Name</label>
                <input type="text" id="name" name="name" class="input-field" required>
            </div>

            <div>
                <label for="processor" class="custom-label">Shipping Method</label>
                <select id="processor" name="processor" class="input-field" required>
                    <option value="">-- Select Shipping Method --</option>
                    @foreach($processors as $processor)
                        <option value="{{ $processor->code }}">
                            {{ $processor->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="custom-label">Status</label>
                <select name="shipping_data[is_active]" class="input-field">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
        </div>

        <!-- Configuration content (hidden by default; shown when #tab-config is checked) -->
        <div class="tab-config space-y-4 p-1" id="shipping_configuration"></div>
    </div>

    <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="{{ route('admin.shipping_methods.index') }}" class="btn-cancel">Cancel</a>

        <button type="submit" name="button" class="btn-primary">
            Create Shipping Method
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