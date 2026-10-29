@extends('backend::layouts.app')

@section('title', 'Tax Rates')

@section('breadcrumbs')
    @php
        $title = 'Tax';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index')],
            ['label' => $title],
        ];
    @endphp

    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection


@section('content')
{{-- @include('admin::components.common.back-button', ['route' => route('admin.tax-category.index'), 'name' => 'Tax Rates']) --}}
<x-data-view :data="$lists" title="" url="" />   
@endsection

