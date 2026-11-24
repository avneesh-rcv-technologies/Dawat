@extends('backend::layouts.app')
@section('title', 'Blogs Category')
@section('breadcrumbs')
    @php
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index')],

            ['label' => 'Blog Categories', 'url' => route('admin.blogs.blog_categories.index')],
        ];
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Blogs Category'" />
@endsection
@section('content')
    @include("blogs::components.tabs")
    <x-data-view :data="$lists" title="Blogs Category" />
@endsection