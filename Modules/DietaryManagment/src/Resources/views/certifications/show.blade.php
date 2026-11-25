@extends('backend::layouts.app')
@php($pageTitle = ($ownerSide ?? false) ? 'Certificate Details' : 'Review Certificate')
@section('title', $pageTitle)
@section('breadcrumbs')
    <x-breadcrumbs :breadcrumbs="[
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'Certifications', 'url' => route('admin.dietarymanagment.certifications.index')],
        ['label' => $pageTitle],
    ]" :name="$pageTitle" />
@endsection
@section('content')
    <div class="certificate-review-page mx-auto max-w-7xl">
        @if(session('success'))<p class="mb-4 rounded-lg bg-green-50 p-4 text-green-800" role="status">{{ session('success') }}</p>@endif
        @if($errors->any())<div class="mb-4 rounded-lg bg-red-50 p-4 text-red-700" role="alert">@foreach($errors->all() as $message)<p>{{ $message }}</p>@endforeach</div>@endif
        <header class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">{{ $certificate->restaurant->name }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $certificate->certificationType?->name ?? 'Dietary certificate' }}</p>
            </div>
        </header>

        <div class="certificate-review-workbench">
            <section class="certificate-document-panel" aria-label="Certificate document preview">
                <div class="certificate-document-heading">
                    <div>
                        <h3>Document preview</h3>
                    </div>
                    <a href="{{ route('restaurant.onboarding.certificates.document', ['id' => $certificate->id, 'preview' => 1]) }}" target="_blank" rel="noopener noreferrer">View document</a>
                </div>
                <iframe
                    src="{{ route('restaurant.onboarding.certificates.document', ['id' => $certificate->id, 'preview' => 1]) }}"
                    title="Preview of {{ $certificate->file_name ?: 'uploaded certificate' }}"
                    loading="lazy"></iframe>
            </section>

            <aside class="certificate-review-panel" aria-label="Certificate review">
                @include('dietarymanagment::certifications.partials.review-card')
            </aside>
        </div>
    </div>
    <style>
        .certificate-review-workbench { display:grid; grid-template-columns:minmax(0,1.7fr) minmax(320px,.9fr); align-items:start; gap:20px; }
        .certificate-document-panel,.certificate-review-panel { min-width:0; overflow:hidden; border:1px solid #e2e8f0; border-radius:12px; background:#fff; box-shadow:0 1px 2px rgb(15 23 42 / 5%); }
        .certificate-document-heading { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px; padding:16px 20px; border-bottom:1px solid #e2e8f0; }
        .certificate-document-heading h3 { margin:0; color:#111827; font-size:15px; font-weight:600; }
        .certificate-document-heading p { margin:4px 0 0; color:#64748b; font-size:12px; overflow-wrap:anywhere; }
        .certificate-document-heading a { color:#ea580c; font-size:13px; font-weight:600; text-decoration:underline; }
        .certificate-document-panel iframe { display:block; width:100%; height:min(76vh,900px); min-height:520px; border:0; background:#f1f5f9; }
        .certificate-review-panel article { border:0; border-radius:0; box-shadow:none; }
        @media(max-width:900px) { .certificate-review-workbench { grid-template-columns:1fr; } .certificate-document-panel iframe { height:65vh; min-height:420px; } }
    </style>
@endsection
