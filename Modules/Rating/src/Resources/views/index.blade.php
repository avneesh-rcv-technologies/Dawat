@extends('backend::layouts.app')

@section('title', 'Ratings & Reviews')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'System Settings', 'url' => route('settings.index') ],
    ['label' => 'Ratings & Reviews', 'url' => null ],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Ratings & Reviews'" />
@endsection

@section('content')
    <x-data-view :data="$lists" title="" url="" />
@endsection