@extends('backend::layouts.app')

@section('title', 'Featured Items')

@section('breadcrumbs')
    @php
        $title = 'Featured Items';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index')],
            ['label' => 'Food Management', 'url' => '#'],
            ['label' => $title],
        ];
    @endphp

    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')
    <x-data-view :data="$lists" title="" />
@endsection