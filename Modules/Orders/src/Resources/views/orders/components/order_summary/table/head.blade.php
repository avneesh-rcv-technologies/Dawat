<thead class="bg-gray-50">
    @php
        $mode_setting = fn_get_setting('general.line_item_type') ?? 'L';
    @endphp

    <tr>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Food Item
        </th>

        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Price
        </th>

        @if ($mode_setting !== 'S')
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Tax
            </th>
        @endif

        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Quantity
        </th>

        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            SubTotal
        </th>
    </tr>
</thead>
