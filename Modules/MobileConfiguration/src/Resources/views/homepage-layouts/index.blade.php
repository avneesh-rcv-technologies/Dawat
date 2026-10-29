@extends('backend::layouts.app')

@section('title', 'Homepage Layouts')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')], 
    ['label' => 'Mobile Configuration', 'url' => ''],
    ['label' => 'Homepage Layouts','url' => ''],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Homepage Layouts'" />
@endsection

@section('content')
 <x-data-view :data="$lists" title="Homepage Layouts" url="" />
@endsection