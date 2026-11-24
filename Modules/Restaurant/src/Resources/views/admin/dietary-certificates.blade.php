@extends('backend::layouts.app')

@section('title', 'Settings | Dietary Certificates')

@section('breadcrumbs')
@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'Settings', 'url' => route('settings.index')],
        ['label' => 'Dietary Certificates'],
    ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Dietary Certificates'" />
@endsection

@section('content')
<section class="owner-dietary-settings rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
    <h2 class="text-xl font-semibold">Dietary Certificates</h2>
    <p class="mt-2 text-sm text-gray-500">Upload certificates for the dietary labels you want to use. Each required certificate must be approved by admin before its label can be selected for food items.</p>
    @if($certificateRestaurant)
        @if($certificateRestaurants->count() > 1)
            <form method="GET" action="{{ route('settings.restaurant.dietary-certificates.index') }}" class="mt-5">
                <label for="certificate-restaurant" class="block text-sm font-medium">Restaurant</label>
                <select id="certificate-restaurant" name="restaurant_id" class="mt-2 w-full rounded-lg border border-gray-300 p-3" onchange="this.form.submit()">
                    @foreach($certificateRestaurants as $ownedRestaurant)
                        <option value="{{ $ownedRestaurant->id }}" @selected($certificateRestaurant->id === $ownedRestaurant->id)>{{ $ownedRestaurant->name }}</option>
                    @endforeach
                </select>
            </form>
        @else
            <p class="mt-4 font-medium">{{ $certificateRestaurant->name }}</p>
        @endif
        @unless($canUploadCertificates)
            <p class="mt-4 text-sm text-gray-500">You have permission to view certificates. Upload permission is required to submit or replace documents.</p>
        @endunless
        @include('restaurant::onboarding.dietary-certificates', [
            'correctionRestaurant' => $certificateRestaurant,
            'correctionData' => $certificateData,
            'profileMode' => true,
            'certificateUploadRoute' => 'settings.restaurant.dietary-certificates.store',
        ])
    @else
        <p class="mt-4 text-sm text-gray-500">No restaurant is linked to your account yet.</p>
    @endif
</section>
<style>
    .owner-dietary-settings .dietary-card { background: #fff; border-color: #e5e7eb; box-shadow: 0 2px 8px rgb(15 23 42 / 4%); }
    .owner-dietary-settings .dietary-heading h3 { color: #111827; }
    .owner-dietary-settings .dietary-help { color: #64748b; }
    .owner-dietary-settings .dietary-filename, .owner-dietary-settings .dietary-upload label { color: #334155; }
    .owner-dietary-settings .dietary-upload input[type="file"] { background: #f8fafc; color: #334155; border-color: #cbd5e1; }
    .owner-dietary-settings .dietary-upload input[type="file"]::file-selector-button { background: #e2e8f0; color: #334155; }
    .owner-dietary-settings .dietary-status { background: #f1f5f9; color: #475569; border-color: #cbd5e1; }
    .owner-dietary-settings .dietary-status[data-status="approved"] { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
    .owner-dietary-settings .dietary-status[data-status="rejected"], .owner-dietary-settings .dietary-error { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }
    .owner-dietary-settings .dietary-status[data-status="changes_requested"], .owner-dietary-settings .dietary-feedback { background: #fff7ed; color: #c2410c; border-color: #fed7aa; }
    .owner-dietary-settings .dietary-status[data-status="submitted"], .owner-dietary-settings .dietary-status[data-status="under_review"] { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
</style>
@endsection
