@extends('backend::layouts.app')

@section('title', 'Users')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'System Settings', 'url' => route('settings.index') ],
    ['label' => 'Users', 'url' => null ],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Users'" />
@endsection

@section('content')    
    <x-data-view :data="$lists" title="" />
@endsection