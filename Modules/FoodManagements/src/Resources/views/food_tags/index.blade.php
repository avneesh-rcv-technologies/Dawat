@extends('backend::layouts.app')

@section('title', 'Food Tags')

@section('breadcrumbs')
    @php
        $title = 'Food Tags';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index')],
            ['label' => $title],
        ];
    @endphp

    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')
    <x-data-view :data="$lists" title="" />
@endsection
