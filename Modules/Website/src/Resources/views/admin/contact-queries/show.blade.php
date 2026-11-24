@extends('backend::layouts.app')

@section('title', 'Contact Query Details')

@section('breadcrumbs')
    @php
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index')],
            ['label' => 'Settings', 'url' => route('settings.index')],
            ['label' => 'Contact Queries', 'url' => route('website.contact-queries.index')],
            ['label' => 'Details', 'url' => ''],
        ];
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" name="Contact Query Details" />
@endsection

@section('content')
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="mb-6 flex items-center justify-between gap-4">
            <h1 class="text-xl font-semibold text-gray-900">Contact Query #{{ $contactQuery->id }}</h1>
            <a href="{{ route('website.contact-queries.index') }}" class="text-sm font-medium text-red-600 hover:underline">Back to Contact Queries</a>
        </div>
        <dl class="grid gap-6 sm:grid-cols-2">
            <div><dt class="text-sm text-gray-500">First Name</dt><dd class="mt-1 break-words text-gray-900">{{ $contactQuery->first_name }}</dd></div>
            <div><dt class="text-sm text-gray-500">Last Name</dt><dd class="mt-1 break-words text-gray-900">{{ $contactQuery->last_name }}</dd></div>
            <div><dt class="text-sm text-gray-500">Email</dt><dd class="mt-1 break-words text-gray-900">{{ $contactQuery->email }}</dd></div>
            <div><dt class="text-sm text-gray-500">Submitted At</dt><dd class="mt-1 text-gray-900">{{ $contactQuery->created_at?->format('Y-m-d H:i:s') }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-sm text-gray-500">Message</dt><dd class="mt-2 whitespace-pre-wrap break-words text-gray-900">{{ $contactQuery->message }}</dd></div>
        </dl>
    </div>
@endsection
