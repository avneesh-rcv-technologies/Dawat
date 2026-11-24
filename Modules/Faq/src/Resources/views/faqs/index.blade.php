@extends('backend::layouts.app')
@section('title', 'Faqs')

@section('breadcrumbs')
@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'FAQs', 'url' => route('admin.faqs.index')],
    ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'FAQs'" />
@endsection

@section('content')
<x-data-view
    :data="$lists"
    title="Faqs"
/>
@endsection
