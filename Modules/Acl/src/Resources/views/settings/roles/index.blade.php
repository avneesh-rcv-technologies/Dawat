@extends('backend::layouts.app')

@section('title', 'Dashboard | Roles & Permission')
@section('breadcrumbs')
@php
  $title = 'Roles & Permission';
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