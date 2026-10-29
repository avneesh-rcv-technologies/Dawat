@extends('backend::layouts.app')

@section('title', 'Restaurant')

@section('breadcrumbs')
    @php
        $title = 'Restaurant';
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