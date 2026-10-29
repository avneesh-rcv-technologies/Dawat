@extends('backend::layouts.app')

@section('title', 'Food Category')

@section('breadcrumbs')
    @php
        $title = 'Food Category';
        
        // Check if coming from restaurant context
        $restaurantId = session('category_restaurant_id');
        $restaurant = $restaurantId ? \Modules\Restaurant\Models\Restaurant::find($restaurantId) : null;
        
        if ($restaurant) {
            $breadcrumbs = [
                ['label' => 'Dashboard', 'url' => route('dashboard.index')],
                ['label' => 'Restaurants', 'url' => route('admin.restaurant.index')],
                ['label' => $restaurant->name, 'url' => route('admin.restaurant.edit', $restaurant->id)],
                ['label' => 'Food Management', 'url' => route('admin.restaurant.edit', $restaurant->id) . '?tab=food'],
                ['label' => $title],
            ];
        } else {
            $breadcrumbs = [
                ['label' => 'Dashboard', 'url' => route('dashboard.index')],
                ['label' => $title],
            ];
        }
    @endphp

    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')

  <x-data-view :data="$lists" title="Category Management" url="" />

@endsection