@extends('backend::layouts.app')

@section('title', 'Tax Category')

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
<x-data-view :data="$lists" title="Tax Category" url="" />   
@endsection

