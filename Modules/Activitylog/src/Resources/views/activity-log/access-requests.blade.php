@extends('backend::layouts.app')

@section('title', 'System Access Requests - Activity Logs')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'System Settings', 'url' => route('settings.index') ],
    ['label' => 'Access Requests', 'url' => route('activity-log.access-requests') ],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Access Requests'" />
@endsection

@section('content')

<x-data-view :data="$lists" title="" url="" />

@endsection
