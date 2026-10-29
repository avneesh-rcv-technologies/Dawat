@extends('backend::layouts.app')

@section('title', 'Cities Management')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'System Settings', 'url' => route('settings.index') ],
    ['label' => 'Cities Management', 'url' => null ],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Cities Management'" />
@endsection

@section('content')
<style>
    .dataview-table-scroll thead th:first-child,
    .dataview-table-scroll tbody td:first-child,
    .dataview-table-scroll thead th:last-child,
    .dataview-table-scroll tbody td:last-child,
    .dataview-mobile-heading > .row-checkbox,
    .dataview-mobile-actions { display: none !important; }
</style>
    <x-data-view :data="$lists" title="" url="" />
@endsection
