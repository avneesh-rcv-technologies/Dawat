@extends('backend::layouts.app')

@section('title', 'Push Notifications Templates')

@section('breadcrumbs')
@php
  $title = 'Push Notifications Templates';
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'System Settings', 'url' => route('settings.index') ],
    ['label' => $title, 'url' => null ],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')
    <x-data-view :data="$lists" title="" url="" />   
@endsection

