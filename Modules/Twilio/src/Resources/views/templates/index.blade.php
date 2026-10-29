@extends('backend::layouts.app')

@section('title', 'SMS Templates')

@section('breadcrumbs')
@php
$title = 'SMS Templates';
$breadcrumbs = [
['label' => 'Dashboard', 'url' => route('dashboard.index')],
['label' => $title],
];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')
    <x-data-view :data="$lists" title="" url="" />   
@endsection

