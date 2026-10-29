<form id="orderStatusForm" class="form-ajax" method="POST"
    action="@isset($status) {{ route('admin.orders-statuses.update', $status->id) }} @else {{ route('admin.orders-statuses.store') }} @endisset">
    @csrf
    @isset($status) @method('PUT') @endisset

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Status Name Field -->
        <div class="col-span-1">
            <label for="name" class="custom-label">Status Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" id="name" value="{{ $status->name ?? old('name') }}" 
                class="input-field" required placeholder="e.g., Processing, Shipped, Delivered">
        </div>

        <!-- Status Code Field -->
        <div class="col-span-1">
            <label for="status_code" class="custom-label">Status Code <span class="text-red-500">*</span></label>
            <input type="text" name="status_code" id="status_code" 
                value="{{ $status->status_code ?? old('status_code') }}" 
                class="input-field" required maxlength="1" 
                placeholder="Single character code (e.g., P, S, D)"
                @isset($status) readonly @endisset>
            <p class="text-xs text-gray-500 mt-1">Single character code to identify this status</p>
        </div>

        <!-- Type Code Field -->
        <div class="col-span-1">
            <label for="type_code" class="custom-label">Type Code <span class="text-red-500">*</span></label>
            <select name="type_code" id="type_code" class="input-field" required>
                <option value="">Select Type</option>
                <option value="O" {{ (isset($status) && $status->type_code == 'O') || old('type_code') == 'O' ? 'selected' : '' }}>Order (O)</option>
                {{-- <option value="L" {{ (isset($status) && $status->type_code == 'L') || old('type_code') == 'L' ? 'selected' : '' }}>Lead (L)</option> --}}
            </select>
            <p class="text-xs text-gray-500 mt-1">O = Order Status, L = Lead Status</p>
        </div>

        <!-- Color Field -->
        <div class="col-span-1">
            <label for="color" class="custom-label">Status Color <span class="text-red-500">*</span></label>
            <div class="flex gap-2">
                <input type="color" name="color" id="color" 
                    value="{{ $status->color ?? old('color', '#3B82F6') }}" 
                    class="h-10 w-20 rounded border border-gray-300 cursor-pointer" required>
                <input type="text" id="color_hex" 
                    value="{{ $status->color ?? old('color', '#3B82F6') }}" 
                    class="input-field flex-1" readonly>
            </div>
            <p class="text-xs text-gray-500 mt-1">Color used to display this status in the UI</p>
        </div>

        <!-- Is New Status Field -->
        <div class="col-span-1">
            <label for="new" class="custom-label">Mark as New Status</label>
            <div class="flex items-center mt-2">
                <input type="checkbox" name="new" id="new" value="1" 
                    {{ (isset($status) && $status->new == 1) || old('new') ? 'checked' : '' }}
                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                <label for="new" class="ml-2 text-sm text-gray-700">
                    This is a new/initial status for orders
                </label>
            </div>
        </div>

    </div>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <button type="button" onclick="window.closeModal()" class="btn-cancel">Cancel</button>

        <button type="submit" name="button" class="btn-primary">
            @isset($status) Update Status @else Create Status @endisset
        </button>
    </div>

</form>

<script>
    // Sync color picker with hex input
    document.getElementById('color').addEventListener('input', function(e) {
        document.getElementById('color_hex').value = e.target.value.toUpperCase();
    });
</script>