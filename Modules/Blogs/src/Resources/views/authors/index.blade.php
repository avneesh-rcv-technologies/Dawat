@extends('backend::layouts.app')
@section('title', 'Blog Authors')

@section('breadcrumbs')
@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'Blog Authors', 'url' => route('admin.blogs.blog_authors.index')],
    ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Blog Authors'" />
@endsection

@section('content')
@include('blogs::components.tabs')
<x-data-view
    :data="$lists"
    title="Blog Authors"
/>
@endsection
