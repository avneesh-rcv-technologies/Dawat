@extends('backend::layouts.app')

@section('title', 'Food Item Preview - '.$item->name)

@section('breadcrumbs')
    @php
        $breadcrumbs = [['label' => 'Dashboard', 'url' => route('dashboard.index')]];
        if ($restaurant) {
            $breadcrumbs[] = ['label' => 'Restaurants', 'url' => route('admin.restaurant.index')];
            $breadcrumbs[] = ['label' => $restaurant->name, 'url' => route('admin.restaurant.edit', $restaurant->id)];
            $breadcrumbs[] = ['label' => 'Food Management', 'url' => route('admin.restaurant.menu.index', ['restaurant' => $restaurant->id])];
        } else {
            $breadcrumbs[] = ['label' => 'Food Items', 'url' => route('admin.foodmanagements.food-items.index')];
        }
        $breadcrumbs[] = ['label' => $item->name];
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" name="Food Item Preview" />
@endsection

@section('content')
    @php
        $backUrl = $restaurant ? route('admin.restaurant.menu.index', ['restaurant' => $restaurant->id]) : route('admin.foodmanagements.food-items.index');
        $editUrl = route('admin.foodmanagements.food-items.edit', [
            'food_item' => $item->id,
            ...($restaurant ? ['restaurant_id' => $restaurant->id, 'from_restaurant' => 1] : []),
        ]);
        $canEdit = bouncer('admin')->hasPermission('admin.foodmanagements.food-items.edit');
        $currency = fn_format_default_currency();
    @endphp
    @include('foodmanagements::food_items.partials.preview')
@endsection
