@extends('backend::layouts.app')

@section('title', 'Currency Management')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'System Settings', 'url' => route('settings.index') ],
    ['label' => 'Currency Management', 'url' => null ],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Currency Management'" />
@endsection

@section('content')
   <x-data-view :data="$lists" title="" url="" />
@endsection

