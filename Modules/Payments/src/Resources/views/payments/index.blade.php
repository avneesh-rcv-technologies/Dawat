@extends('backend::layouts.app')

@section('title', 'Payments')

@section('content')
<x-data-view :data="$lists" title="Payments" url="" />
   
@endsection

@section('scripts')

@endsection