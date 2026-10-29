@extends('backend::layouts.app')

@section('title', 'TaxRule')

@section('content')
{{-- @include('admin::components.common.back-button', ['route' => route('admin.tax-category.index'), 'name' => 'Tax Rule']) --}}

<x-data-view :data="$lists" title="" url="" />   
@endsection

