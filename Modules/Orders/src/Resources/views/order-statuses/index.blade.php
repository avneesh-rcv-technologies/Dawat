@extends('backend::layouts.app')

@section('title', 'Order Statuses')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'System Settings', 'url' => route('settings.index')],
    ['label' => 'Order Statuses', 'url' => null],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Order Statuses'" />
@endsection

@section('content')
<x-data-view :data="$lists" title="" url="" />

@endsection

