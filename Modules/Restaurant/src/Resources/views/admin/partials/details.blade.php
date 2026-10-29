<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Basic Info -->
    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
        <div class="flex items-center mb-4">
            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900">Basic Information</h3>
        </div>
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-600">Name:</span>
                <span class="text-sm text-gray-900 font-medium">{{ $restaurant->name }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-600">Slug:</span>
                <span class="text-sm text-gray-700 font-mono bg-gray-100 px-2 py-1 rounded">{{ $restaurant->slug }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-600">Phone:</span>
                <span class="text-sm text-gray-900">{{ $restaurant->phone ?: '-' }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-600">Food Type:</span>
                <span class="text-sm text-gray-900 capitalize">{{ $restaurant->food_type ?: 'All Types' }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-600">Created:</span>
                <span class="text-sm text-gray-900">{{ $restaurant->created_at ? \Carbon\Carbon::parse($restaurant->created_at)->format('M d, Y') : '-' }}</span>
            </div>
        </div>
    </div>

    <!-- Status & Admin -->
    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
        <div class="flex items-center mb-4">
            <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900">Status & Admin</h3>
        </div>
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-600">Status:</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $restaurant->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ ucfirst($restaurant->status) }}
                </span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-600">Accepting Orders:</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $restaurant->is_accepting_orders ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $restaurant->is_accepting_orders ? 'Yes' : 'No' }}
                </span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-600">Admin Name:</span>
                <span class="text-sm text-gray-900">{{ $restaurant->admin_name ?: '-' }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-600">Admin Email:</span>
                <span class="text-sm text-gray-900">{{ $restaurant->admin_email ?: '-' }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Address Information -->
@if ($restaurant->address_line1 || $restaurant->city || $restaurant->state)
<div class="bg-gray-50 rounded-lg p-6 border border-gray-200 mt-6">
    <div class="flex items-center mb-4">
        <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
        </svg>
        <h3 class="text-lg font-semibold text-gray-900">Address Information</h3>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-600">Address Line 1:</span>
                <span class="text-sm text-gray-900">{{ $restaurant->address_line1 ?: '-' }}</span>
            </div>
            @if ($restaurant->address_line2)
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-600">Address Line 2:</span>
                <span class="text-sm text-gray-900">{{ $restaurant->address_line2 }}</span>
            </div>
            @endif
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-600">City:</span>
                <span class="text-sm text-gray-900">{{ $restaurant->city ?: '-' }}</span>
            </div>
        </div>
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-600">State:</span>
                <span class="text-sm text-gray-900">{{ $restaurant->state ?: '-' }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-600">Country:</span>
                <span class="text-sm text-gray-900">{{ $restaurant->country ?: '-' }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-600">Postal Code:</span>
                <span class="text-sm text-gray-900">{{ $restaurant->postal_code ?: '-' }}</span>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Service Options -->
<div class="bg-gray-50 rounded-lg p-6 border border-gray-200 mt-6">
    <div class="flex items-center mb-4">
        <svg class="w-5 h-5 text-orange-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
            </path>
        </svg>
        <h3 class="text-lg font-semibold text-gray-900">Service Options</h3>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div class="flex items-center justify-between p-3 bg-white rounded-lg border">
            <span class="text-sm font-medium text-gray-700">Takeaway</span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                {{ $restaurant->is_takeaway ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                {{ $restaurant->is_takeaway ? 'Available' : 'Not Available' }}
            </span>
        </div>
        <div class="flex items-center justify-between p-3 bg-white rounded-lg border">
            <span class="text-sm font-medium text-gray-700">Delivery</span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                {{ $restaurant->is_delivery ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                {{ $restaurant->is_delivery ? 'Available' : 'Not Available' }}
            </span>
        </div>
    </div>
</div>
