@extends('backend::layouts.app')

@section('title', 'Mobile CMS')

@section('breadcrumbs')
@php
$breadcrumbs = [
['label' => 'Dashboard', 'url' => route('dashboard.index')],
['label' => 'Website', 'url' => ''],
['label' => 'Mobile CMS','url' => ''],
];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Mobile CMS'" />
@endsection
@section('content')
<x-data-view :data="$lists" title="Mobile CMS" url="" />
@endsection