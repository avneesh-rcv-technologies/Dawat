@extends('backend::layouts.app')

@section('title', 'Mobile Banners')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')], 
    ['label' => 'Mobile Configuration', 'url' => ''],
    ['label' => 'Banners','url' => ''],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Mobile Banners'" />
@endsection

@section('content')
 <x-data-view :data="$lists" title="Mobile Banners" url="" />
@endsection