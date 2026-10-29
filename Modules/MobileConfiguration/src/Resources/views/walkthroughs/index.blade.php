@extends('backend::layouts.app')

@section('title', 'Mobile Walkthroughs')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')], 
    ['label' => 'Mobile Configuration', 'url' => ''],
    ['label' => 'Walkthroughs','url' => ''],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Mobile Walkthroughs'" />
@endsection

@section('content')
 <x-data-view :data="$lists" title="Mobile Walkthroughs" url="" />
@endsection