@extends('backend::layouts.app')

@section('title', 'Feature Groups')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'Feature Groups','url' => ''],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Feature Groups'" />

@endsection

@section('content')
   <x-data-view :data="$lists" title="Feature Groups" url="" />
@endsection
