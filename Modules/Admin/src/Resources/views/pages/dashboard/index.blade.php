@extends('backend::layouts.app')

@section('page_title', 'Dashboard')
@section('title', 'Dashboard')
@section('content-class', (isset($dashboard) || $isOwnerDashboard) ? 'admin-dashboard-content' : 'space-y-6 px-6 md:px-28 py-6')

@section('breadcrumbs')
    @if (!isset($dashboard) && !$isOwnerDashboard)
        <x-breadcrumbs :breadcrumbs="[['label' => 'Dashboard', 'url' => null]]" name="Dashboard" />
    @endif
@endsection

@section('content')
    @hook('admin:dashboard.pre')

    @if ($isOwnerDashboard)
        @include('admin::pages.dashboard.owner')
    @elseif (isset($dashboard))
        @include('admin::pages.dashboard.overview')
    @else
        @include('admin::pages.dashboard.welcome', ['userName' => $userName])
    @endif

    @hook('admin:dashboard.post')

@endsection
