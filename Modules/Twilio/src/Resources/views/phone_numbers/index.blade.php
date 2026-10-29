@extends('backend::layouts.app')

@section('title', 'Phone Numbers | Twilio')

@section('content')
<x-data-view :data="$lists" title="Phone Numbers" url="" />
@endsection