@extends('backend::layouts.app')
@section('title', 'Blogs')
@section('breadcrumbs')
@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'Blogs', 'url' => route('admin.blogs.blogs.index')],
      
    ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Blogs'" />
@endsection

@section('content')
@include("blogs::components.tabs")
<x-data-view 
    :data="$lists" 
    title="Blogs"
/>
@endsection
