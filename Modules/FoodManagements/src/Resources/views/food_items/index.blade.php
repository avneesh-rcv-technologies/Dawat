@extends('backend::layouts.app')

@section('title', 'Food Items')

@section('breadcrumbs')
    @php
        $title = 'Food Items';
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
