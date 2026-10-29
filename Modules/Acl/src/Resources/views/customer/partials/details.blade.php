<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Basic Info -->
    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
        <div class="flex items-center mb-4">
            <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900">Basic Information</h3>
        </div>
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-600">Name:</span>
                <span class="text-sm text-gray-900 font-medium">{{ $customer->name }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-600">Email:</span>
                <span class="text-sm text-gray-900">{{ $customer->email }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-600">Phone:</span>
                <span class="text-sm text-gray-900">{{ $customer->phone ?: 'N/A' }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-600">Status:</span>
                @if($customer->status)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Inactive</span>
                @endif
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-600">Registered:</span>
                <span class="text-sm text-gray-900">{{ $customer->created_at->format('M d, Y h:i A') }}</span>
            </div>
        </div>
    </div>

    <!-- Guest Information -->
    @if($customer->guest_method || $customer->number_of_guests || $customer->preferred_delivery_date)
    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
        <div class="flex items-center mb-4">
            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900">Guest Information</h3>
        </div>
        <div class="space-y-3">
            @if($customer->guest_method)
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-600">Guest Method:</span>
                <span class="text-sm text-gray-900 capitalize">{{ $customer->guest_method }}</span>
            </div>
            @endif
            @if($customer->number_of_guests)
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-600">Number of Guests:</span>
                <span class="text-sm text-gray-900">{{ $customer->number_of_guests }}</span>
            </div>
            @endif
            @if($customer->preferred_delivery_date)
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-600">Preferred Delivery Date:</span>
                <span class="text-sm text-gray-900">{{ $customer->preferred_delivery_date->format('M d, Y') }}</span>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>
