@extends('backend::layouts.app')

@section('title', 'Certifications')

@section('breadcrumbs')
    @php
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index')],
            ['label' => 'Dietary Manage'],
            ['label' => 'Certifications'],
        ];
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" name="Certifications" />
@endsection

@section('content')
    <x-data-view :data="$lists" title="" />
@endsection
