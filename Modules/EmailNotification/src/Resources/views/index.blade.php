@extends('backend::layouts.app')

@section('title', 'Email Templates')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'System Settings', 'url' => route('settings.index') ],
    ['label' => 'Email Templates', 'url' => null ],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Email Templates'" />
@endsection

@section('content')

<div class="flex justify-left gap-4 mb-6">
    <a href="{{ route('notification.email-design.edit', 'header') }}"
        class="btn-primary">
        Edit Header
    </a>

    <a href="{{ route('notification.email-design.edit', 'footer') }}"
        class="btn-primary">
        Edit Footer
    </a>
</div>
<x-data-view :data="$lists" title="" url="" />
@endsection