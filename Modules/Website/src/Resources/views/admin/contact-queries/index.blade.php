@extends('backend::layouts.app')

@section('title', 'Contact Queries')

@section('breadcrumbs')
    @php
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index')],
            ['label' => 'Settings', 'url' => route('settings.index')],
            ['label' => 'Contact Queries', 'url' => ''],
        ];
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" name="Contact Queries" />
@endsection

@section('content')
    <x-data-view :data="$lists" title="Contact Queries" url="" />
@endsection
