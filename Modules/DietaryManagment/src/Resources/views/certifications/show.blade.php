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
    <div class="mx-auto max-w-4xl">
        @if(session('success'))<p class="mb-4 rounded-lg bg-green-50 p-4 text-green-800" role="status">{{ session('success') }}</p>@endif
        @if($errors->any())<div class="mb-4 rounded-lg bg-red-50 p-4 text-red-700" role="alert">@foreach($errors->all() as $message)<p>{{ $message }}</p>@endforeach</div>@endif
        <h2 class="text-lg font-semibold">{{ $certificate->restaurant->name }}</h2>
        @include('dietarymanagment::certifications.partials.review-card')
    </div>
@endsection
