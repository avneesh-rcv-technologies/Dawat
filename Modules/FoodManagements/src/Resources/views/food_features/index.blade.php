@extends('backend::layouts.app')

@section('title', 'Food Features')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'Food Features','url' => ''],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Food Features'" />

@endsection

@section('content')
   <x-data-view :data="$lists" title="Food Features" url="" />
@endsection
