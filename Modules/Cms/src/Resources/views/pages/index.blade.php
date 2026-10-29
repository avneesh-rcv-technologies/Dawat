@extends('backend::layouts.app')

@section('title', 'CMS Pages')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')], 
    ['label' => 'Website', 'url' => ''],
    ['label' => 'CMS Pages','url' => ''],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'CMS Pages'" />
@endsection

@section('content')
 <x-data-view :data="$lists" title="CMS Pages" url="" />
@endsection