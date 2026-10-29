@extends('backend::layouts.app')

@section('title', 'Mobile Features')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')], 
    ['label' => 'Mobile Configuration', 'url' => ''],
    ['label' => 'Features','url' => ''],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Mobile Features'" />
@endsection

@section('content')
 <x-data-view :data="$lists" title="Mobile Features" url="" />
@endsection