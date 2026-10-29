@extends('backend::layouts.app')

@section('title', 'Phone Number | '. $phoneNumber->phone_number)

@section('content')
@include('admin::components.common.back-button', ['route' => route('admin.twilio.index'), 'name' => 'Messages / '. $phoneNumber->phone_number])

<x-data-view :data="$lists" title="" url="" />

@endsection
