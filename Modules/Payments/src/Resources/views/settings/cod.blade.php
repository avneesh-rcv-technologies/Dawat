
<div class="mt-3">
    <label for="order_created_status" class="block text-sm font-medium text-gray-700 mb-1">Order complete status</label>

    <select name="payment_data[stripe_order_complete]" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
        @foreach (fn_get_order_statuses('O') as $status)
        <option value="{{ $status->status_code }}"
            {{ (!empty($payment_data['stripe_order_complete']) && $payment_data['stripe_order_complete'] == $status->status_code) ? 'selected' : '' }}>
            {{ $status->name }}
        </option>
        @endforeach
    </select>
</div>
