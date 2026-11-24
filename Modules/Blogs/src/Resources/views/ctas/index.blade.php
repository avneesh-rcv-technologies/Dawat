@extends('backend::layouts.app')
@section('title', 'Blog CTAs')

@section('breadcrumbs')
@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'Blog CTAs', 'url' => route('admin.blogs.blog_ctas.index')],
    ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Blog CTAs'" />
@endsection

@section('content')
@include('blogs::components.tabs')
<x-data-view
    :data="$lists"
    title="Blog CTAs"
/>
@endsection
