@extends('backend::layouts.app')

@section('title', 'Discounts')

@section('breadcrumbs')
    @php
        $title = 'Discount';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index')],
            ['label' => $title],
        ];
    @endphp

    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')
<x-data-view :data="$lists" title="Discounts" url="" />   
@endsection

