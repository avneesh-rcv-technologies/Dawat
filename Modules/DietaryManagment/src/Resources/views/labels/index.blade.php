@extends('backend::layouts.app')

@section('title', 'Dietary Labels')

@section('breadcrumbs')
    @php
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index')],
            ['label' => 'Dietary Labels'],
        ];
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" name="Dietary Labels" />
@endsection

@section('content')
    @if (session('success'))
        <p class="mb-4 rounded-lg bg-green-50 p-3 text-green-800" role="status">{{ session('success') }}</p>
    @endif
    <x-data-view :data="$lists" title="" />
@endsection
