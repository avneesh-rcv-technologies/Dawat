<div class="bg-white rounded-lg shadow p-6 space-y-4">
    <div class="flex justify-between items-center">
        <h3 class="text-lg font-medium text-gray-900">Coupons</h3>
        <button type="button" id="add-coupon-btn"
            class="text-sm bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">
            + Add Coupon
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usage Limit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Per User Limit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min Order</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="coupons-container" class="bg-white divide-y divide-gray-200">
                @if(isset($discount) && $discount->coupons->count())
                    @foreach($discount->coupons as $index => $coupon)
                        <tr>
                            <td class="px-3 py-2 md:px-6 md:py-4 whitespace-nowrap">
                                <input type="hidden" value="{{ $coupon->id }}" data-base-name="id">
                                <input type="text" value="{{ $coupon->code }}" class="input-field w-full" placeholder="SUMMER20"
                                    required data-base-name="code">
                            </td>
                            <td class="px-3 py-2 md:px-6 md:py-4">
                                <input type="text" value="{{ $coupon->description }}" class="input-field w-full" 
                                    placeholder="Description..." data-base-name="description">
                            </td>
                            <td class="px-3 py-2 md:px-6 md:py-4 whitespace-nowrap">
                                <input type="number" value="{{ $coupon->usage_limit }}" class="input-field w-full"
                                    placeholder="Unlimited" data-base-name="usage_limit" min="0">
                            </td>
                            <td class="px-3 py-2 md:px-6 md:py-4 whitespace-nowrap">
                                <input type="number" value="{{ $coupon->usage_limit_per_user }}" class="input-field w-full"
                                    placeholder="Unlimited" data-base-name="usage_limit_per_user" min="0">
                            </td>
                            <td class="px-3 py-2 md:px-6 md:py-4 whitespace-nowrap">
                                <input type="number" step="0.01" value="{{ $coupon->min_order_amount }}" class="input-field w-full"
                                    placeholder="No minimum" data-base-name="min_order_amount" min="0">
                            </td>
                            <td class="px-3 py-2 md:px-6 md:py-4 whitespace-nowrap">
                                <select class="input-field w-full" data-base-name="is_active">
                                    <option value="1" @selected($coupon->is_active)>Active</option>
                                    <option value="0" @selected(!$coupon->is_active)>Inactive</option>
                                </select>
                            </td>
                            <td class="px-3 py-2 md:px-6 md:py-4 whitespace-nowrap">
                                <button type="button" class="text-red-600 hover:text-red-800 remove-coupon">
                                    <span class="material-icons-outlined text-xl">delete</span>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <!-- Template for new coupons -->
    <template id="coupon-template">
        <tr>
            <td class="px-3 py-2 md:px-6 md:py-4 whitespace-nowrap">
                <input type="text" class="input-field w-full" placeholder="SUMMER20" required data-base-name="code">
            </td>
            <td class="px-3 py-2 md:px-6 md:py-4">
                <input type="text" class="input-field w-full" placeholder="Description..." data-base-name="description">
            </td>
            <td class="px-3 py-2 md:px-6 md:py-4 whitespace-nowrap">
                <input type="number" class="input-field w-full" placeholder="Unlimited" data-base-name="usage_limit" min="0">
            </td>
            <td class="px-3 py-2 md:px-6 md:py-4 whitespace-nowrap">
                <input type="number" class="input-field w-full" placeholder="Unlimited" data-base-name="usage_limit_per_user" min="0">
            </td>
            <td class="px-3 py-2 md:px-6 md:py-4 whitespace-nowrap">
                <input type="number" step="0.01" class="input-field w-full" placeholder="No minimum"
                    data-base-name="min_order_amount" min="0">
            </td>
            <td class="px-3 py-2 md:px-6 md:py-4 whitespace-nowrap">
                <select class="input-field w-full" data-base-name="is_active">
                    <option value="1" selected>Active</option>
                    <option value="0">Inactive</option>
                </select>
            </td>
            <td class="px-3 py-2 md:px-6 md:py-4 whitespace-nowrap">
                <button type="button" class="text-red-600 hover:text-red-800 remove-coupon">
                    <span class="material-icons-outlined text-xl">delete</span>
                </button>
            </td>
        </tr>
    </template>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const addBtn = document.getElementById('add-coupon-btn');
        const container = document.getElementById('coupons-container');
        const template = document.getElementById('coupon-template').content;

        // Rebuilds correct names: coupons[0][field], coupons[1][field], ...
        function updateIndexes() {
            container.querySelectorAll('tr').forEach((tr, index) => {
                tr.querySelectorAll('[data-base-name]').forEach(input => {
                    const base = input.getAttribute('data-base-name');
                    input.name = `coupons[${index}][${base}]`;
                });
            });
        }

        // Attach delete buttons
        function attachRemoveButtons() {
            container.querySelectorAll('.remove-coupon').forEach(btn => {
                btn.onclick = (e) => {
                    e.preventDefault();
                    if (confirm('Are you sure you want to remove this coupon?')) {
                        btn.closest('tr').remove();
                        updateIndexes();
                    }
                };
            });
        }

        // Add new coupon row
        addBtn.addEventListener('click', function (e) {
            e.preventDefault();
            const clone = document.importNode(template, true);
            container.appendChild(clone);
            updateIndexes();
            attachRemoveButtons();
        });

        // Initialize
        updateIndexes();
        attachRemoveButtons();
    });
</script>