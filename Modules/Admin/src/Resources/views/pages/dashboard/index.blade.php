@extends('backend::layouts.app')

@section('page_title', 'Dashboard')

@section('breadcrumbs')
    @php
        $title = 'Dashboard';
        $breadcrumbs = [['label' => $title, 'url' => null]];
    @endphp

    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')
    @hook('admin:dashboard.pre')

    @include('admin::pages.dashboard.welcome', ['userName' => $userName])

    @hook('admin:dashboard.post')

@endsection
