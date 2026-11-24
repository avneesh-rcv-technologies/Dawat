@extends('backend::layouts.app')

@section('title', 'Settings | Gallery')

@section('breadcrumbs')
@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'Settings', 'url' => route('settings.index')],
        ['label' => 'Gallery'],
    ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Gallery'" />
@endsection

@section('content')
<p class="mb-4 text-sm text-gray-600">{{ $restaurant->name }}</p>
@unless($canUploadGallery || $canDeleteGallery)
    <p class="mb-4 text-sm text-gray-600">You have permission to view this gallery. Upload or delete permission is required to make changes.</p>
@endunless
@include('restaurant::admin.partials.gallery', [
    'galleryStoreAction' => route('settings.restaurant.gallery.store'),
    'galleryDeleteAction' => route('settings.restaurant.gallery.destroy', ['id' => '__id__']),
])
@endsection
