@extends('backend::layouts.app')

@section('title', 'Restaurant Application Details')

@section('breadcrumbs')
    @php
        $title = 'Application Details';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index')],
            ['label' => 'Restaurant', 'url' => route('admin.restaurant.index')],
            ['label' => 'Approvals', 'url' => route('admin.restaurant.approvals.index')],
            ['label' => $title],
        ];
    @endphp

    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')
<div class="mx-auto">
    
    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Header --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-6">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-4 min-w-0">
                <a href="{{ route('admin.restaurant.approvals.index') }}"
                    class="flex h-11 w-11 items-center justify-center rounded-xl border border-gray-200 bg-gray-50 text-gray-600 transition hover:border-gray-300 hover:bg-gray-100 hover:text-gray-900">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>

                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-100 to-yellow-200 ring-1 ring-amber-200">
                    <svg class="h-7 w-7 text-yellow-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>

                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Application Details</p>
                    <h2 class="mt-1 truncate text-2xl font-bold text-gray-900">{{ $restaurant->name }}</h2>
                    <p class="mt-1 text-sm text-gray-500">Restaurant Application Review</p>
                </div>
            </div>

            <div>
                @if($restaurant->approval_status === 'pending')
                    <span class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-4 py-2 text-sm font-semibold text-amber-800 ring-1 ring-inset ring-amber-200">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        Pending Review
                    </span>
                @elseif($restaurant->approval_status === 'approved')
                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-4 py-2 text-sm font-semibold text-emerald-800 ring-1 ring-inset ring-emerald-200">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Approved
                    </span>
                @else
                    <span class="inline-flex items-center gap-2 rounded-full bg-red-100 px-4 py-2 text-sm font-semibold text-red-800 ring-1 ring-inset ring-red-200">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        Rejected
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Applied</p>
            <p class="mt-2 text-lg font-semibold text-gray-900">{{ $restaurant->created_at ? $restaurant->created_at->format('M d, Y') : 'N/A' }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Location</p>
            <p class="mt-2 text-lg font-semibold text-gray-900">{{ $restaurant->city ?: 'N/A' }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Owner</p>
            <p class="mt-2 text-lg font-semibold text-gray-900">{{ $restaurant->admin->name ?? 'N/A' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                    <h3 class="flex items-center text-lg font-semibold text-gray-900">
                        <svg class="mr-2 h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Restaurant Information
                    </h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div class="rounded-xl bg-gray-50 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Restaurant Name</dt>
                            <dd class="mt-2 text-sm font-semibold text-gray-900">{{ $restaurant->name }}</dd>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Phone</dt>
                            <dd class="mt-2 text-sm text-gray-900">{{ $restaurant->phone ?: 'N/A' }}</dd>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-4 md:col-span-2">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Address</dt>
                            <dd class="mt-2 text-sm text-gray-900">{{ $restaurant->address_line1 ?: 'N/A' }}</dd>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">City</dt>
                            <dd class="mt-2 text-sm text-gray-900">{{ $restaurant->city ?: 'N/A' }}</dd>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Country</dt>
                            <dd class="mt-2 text-sm text-gray-900">{{ $restaurant->country ?: 'N/A' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                    <h3 class="flex items-center text-lg font-semibold text-gray-900">
                        <svg class="mr-2 h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Owner Information
                    </h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div class="rounded-xl bg-gray-50 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Owner Name</dt>
                            <dd class="mt-2 text-sm font-semibold text-gray-900">{{ $restaurant->admin->name ?? 'N/A' }}</dd>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Email</dt>
                            <dd class="mt-2 text-sm text-gray-900">
                                <a href="mailto:{{ $restaurant->admin->email ?? '' }}" class="font-medium text-blue-600 hover:text-blue-800">
                                    {{ $restaurant->admin->email ?? 'N/A' }}
                                </a>
                            </dd>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-4 md:col-span-2">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Email Verified</dt>
                            <dd class="mt-2 text-sm text-gray-900">
                                {{ $restaurant->admin && $restaurant->admin->email_verified_at ? $restaurant->admin->email_verified_at->format('M d, Y h:i A') : 'Not verified' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            @if($restaurant->rejection_reason)
                <div class="rounded-2xl border border-red-200 bg-red-50 p-6">
                    <h3 class="mb-3 flex items-center text-lg font-semibold text-red-900">
                        <svg class="mr-2 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        Rejection Reason
                    </h3>
                    <p class="text-sm leading-6 text-red-800">{{ $restaurant->rejection_reason }}</p>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                    <h3 class="flex items-center text-lg font-semibold text-gray-900">
                        <svg class="mr-2 h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Application Timeline
                    </h3>
                </div>
                <div class="space-y-4 p-6">
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-gray-500">Applied On</p>
                        <p class="mt-2 text-sm font-medium text-gray-900">{{ $restaurant->created_at ? $restaurant->created_at->format('M d, Y h:i A') : 'N/A' }}</p>
                    </div>

                    @if($restaurant->approved_at)
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-gray-500">
                                {{ $restaurant->approval_status === 'approved' ? 'Approved On' : 'Reviewed On' }}
                            </p>
                            <p class="mt-2 text-sm font-medium text-gray-900">{{ $restaurant->approved_at->format('M d, Y h:i A') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            @if($restaurant->approval_status === 'pending')
                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900">Actions</h3>
                    </div>
                    <div class="space-y-3 p-6">
                        <form action="{{ route('admin.restaurant.approvals.approve', $restaurant->id) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    onclick="return confirm('Are you sure you want to approve this restaurant application?')"
                                    class="flex w-full items-center justify-center rounded-xl bg-green-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                <svg class="mr-2 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Approve Application
                            </button>
                        </form>

                        <button type="button"
                                onclick="document.getElementById('rejectModal').classList.remove('hidden')"
                                class="flex w-full items-center justify-center rounded-xl bg-red-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                            <svg class="mr-2 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                            Reject Application
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Reject Application</h3>
            <button type="button" 
                    onclick="document.getElementById('rejectModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form action="{{ route('admin.restaurant.approvals.reject', $restaurant->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason*</label>
                <textarea name="rejection_reason" 
                          rows="4" 
                          required 
                          placeholder="Please provide a clear reason for rejecting this application..."
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"></textarea>
            </div>
            <div class="flex space-x-3">
                <button type="button" 
                        onclick="document.getElementById('rejectModal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition">
                    Cancel
                </button>
                <button type="submit" 
                        class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                    Reject Application
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
