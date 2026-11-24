@extends('backend::layouts.app')
@section('title', 'Payouts')
@section('breadcrumbs')
    <x-breadcrumbs :breadcrumbs="[['label' => 'Dashboard', 'url' => route('dashboard.index')], ['label' => 'Payouts', 'url' => null]]" name="Payouts" />
@endsection
@section('content')
    <p class="mb-6 text-sm text-gray-500">Commission and restaurant share for paid orders, using each restaurant’s accepted rate.</p>
    @include('orders::commissions.kpis', ['summary' => $summary])
    <x-data-view :data="$lists" title="Payouts" />
@endsection
