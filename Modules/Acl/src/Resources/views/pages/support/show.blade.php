@extends('backend::layouts.app')

@section('title', 'View Support Message')

@section('breadcrumbs')
    @php
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index')],
            ['label' => 'Support Messages', 'url' => route('support.index')],
            ['label' => 'View Message'],
        ];
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'View Support Message'" />
@endsection

@section('content')
    <div class="container mx-auto">
        {{-- Message Header Card --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $message->subject }}</h3>
                    <p class="text-sm text-gray-500 mt-1">Reference: <span class="font-mono font-semibold text-blue-600">{{ $message->reference_number }}</span></p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('support.index') }}" class="btn btn-secondary">
                        <span class="material-icons-outlined text-sm mr-1">arrow_back</span>
                        Back to List
                    </a>
                </div>
            </div>

            {{-- Status and Category --}}
            <div class="flex items-center gap-4 mb-6">
                <div>
                    <label class="text-xs font-medium text-gray-600 uppercase">Status</label>
                    <div class="mt-1">
                        @php
                            $statusColors = [
                                'new' => 'bg-yellow-100 text-yellow-800',
                                'read' => 'bg-blue-100 text-blue-800',
                                'replied' => 'bg-green-100 text-green-800',
                                'closed' => 'bg-gray-100 text-gray-800',
                            ];
                            $color = $statusColors[$message->status] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $color }}">
                            {{ ucfirst($message->status) }}
                        </span>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-600 uppercase">Category</label>
                    <div class="mt-1">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                            {{ $message->category_label }}
                        </span>
                    </div>
                </div>

                <div class="ml-auto">
                    <label class="text-xs font-medium text-gray-600 uppercase">Update Status</label>
                    <div class="mt-1">
                        <select id="status-select" class="form-select rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="new" {{ $message->status === 'new' ? 'selected' : '' }}>New</option>
                            <option value="read" {{ $message->status === 'read' ? 'selected' : '' }}>Read</option>
                            <option value="replied" {{ $message->status === 'replied' ? 'selected' : '' }}>Replied</option>
                            <option value="closed" {{ $message->status === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Sender Information --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 border-t border-gray-200 pt-6">
                <div>
                    <label class="text-sm font-medium text-gray-600">From</label>
                    <p class="text-gray-900 mt-1">{{ $message->name }}</p>
                    <p class="text-sm text-gray-600">{{ $message->email }}</p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-600">Customer</label>
                    <p class="mt-1">
                        @if($message->customer_id)
                            <a href="{{ route('admin.customer.view', $message->customer_id) }}" class="text-blue-600 hover:text-blue-800">
                                Customer #{{ $message->customer_id }}
                            </a>
                        @else
                            <span class="text-gray-500">Guest User</span>
                        @endif
                    </p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-600">Related Order</label>
                    <p class="mt-1">
                        @if($message->order_id)
                            <span class="text-blue-600">Order #{{ $message->order_id }}</span>
                        @else
                            <span class="text-gray-500">None</span>
                        @endif
                    </p>
                </div>
            </div>

            {{-- Timestamps --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 border-t border-gray-200 pt-6 mt-6">
                <div>
                    <label class="text-sm font-medium text-gray-600">Submitted At</label>
                    <p class="text-gray-900 mt-1">{{ $message->created_at->format('M d, Y h:i A') }}</p>
                </div>

                @if($message->replied_at)
                <div>
                    <label class="text-sm font-medium text-gray-600">Replied At</label>
                    <p class="text-gray-900 mt-1">{{ $message->replied_at->format('M d, Y h:i A') }}</p>
                </div>
                @endif

                <div>
                    <label class="text-sm font-medium text-gray-600">IP Address</label>
                    <p class="text-gray-900 mt-1 font-mono text-sm">{{ $message->ip_address ?: 'N/A' }}</p>
                </div>
            </div>
        </div>

        {{-- Message Content Card --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h4 class="text-md font-semibold text-gray-900 mb-4">Message</h4>
            <div class="prose max-w-none">
                <p class="text-gray-700 whitespace-pre-wrap">{{ $message->message }}</p>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('status-select').addEventListener('change', function() {
            const status = this.value;
            const messageId = {{ $message->id }};
            
            if (confirm('Are you sure you want to update the status to "' + status + '"?')) {
                fetch(`/support/${messageId}/status`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ status: status })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Failed to update status');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred');
                });
            } else {
                // Reset select to current status
                this.value = '{{ $message->status }}';
            }
        });
    </script>
    @endpush
@endsection
