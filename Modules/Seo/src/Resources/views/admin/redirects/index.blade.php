@extends('backend::layouts.app')

@section('title', 'SEO Redirects')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'SEO Redirects', 'url' => null],
  ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'SEO Redirects'" />
@endsection

@section('content')
<div class="mb-4 flex justify-between items-center">
    <div>
        <h1 class="text-xl font-semibold text-gray-900">URL Redirects</h1>
        <p class="text-sm text-gray-500">Manage 301/302 redirects for legacy URLs.</p>
    </div>
    <a href="{{ route('admin.seo.redirects.create') }}" class="btn btn-primary">Add redirect</a>
</div>

@if (session('success'))
    <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>
@endif

<div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">From</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">To</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Match</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hits</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse ($redirects as $redirect)
                <tr>
                    <td class="px-4 py-3 text-sm font-mono">{{ $redirect->from_path }}</td>
                    <td class="px-4 py-3 text-sm font-mono">{{ $redirect->to_url }}</td>
                    <td class="px-4 py-3 text-sm">{{ $redirect->status_code }}</td>
                    <td class="px-4 py-3 text-sm">{{ $redirect->match_type }}</td>
                    <td class="px-4 py-3 text-sm">{{ number_format($redirect->hits) }}</td>
                    <td class="px-4 py-3 text-sm text-right space-x-2">
                        <a href="{{ route('admin.seo.redirects.edit', $redirect) }}" class="text-blue-600 hover:underline">Edit</a>
                        <form action="{{ route('admin.seo.redirects.destroy', $redirect) }}" method="POST" class="inline" data-confirm="This redirect will be permanently removed." data-confirm-title="Delete this redirect?" data-confirm-button="Delete" data-confirm-tone="danger">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">No redirects configured yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $redirects->links() }}
</div>

<div class="mt-8 bg-white border border-gray-200 rounded-lg p-6">
    <h2 class="text-lg font-medium text-gray-900 mb-2">Import CSV</h2>
    <p class="text-sm text-gray-500 mb-4">Columns: from_path, to_url, status_code, match_type, is_active</p>
    <form action="{{ route('admin.seo.redirects.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-4">
        @csrf
        <input type="file" name="csv" accept=".csv,.txt" required class="input-field">
        <button type="submit" class="btn btn-secondary">Import</button>
    </form>
</div>
@endsection
