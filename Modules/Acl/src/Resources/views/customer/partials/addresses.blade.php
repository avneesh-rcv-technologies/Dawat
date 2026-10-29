<div class="space-y-4">
    @foreach($addresses as $address)
    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-900 capitalize">{{ $address->address_type }} Address</span>
            @if($address->is_default)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Default</span>
            @endif
        </div>
        <p class="text-sm text-gray-600">
            {{ $address->address_line_1 }}
            @if($address->address_line_2), {{ $address->address_line_2 }}@endif
        </p>
        <p class="text-sm text-gray-600">
            {{ $address->city->name ?? '' }}, {{ $address->state->name ?? '' }} {{ $address->zip_code }}
        </p>
        <p class="text-sm text-gray-600">{{ $address->country->name ?? '' }}</p>
    </div>
    @endforeach
</div>
