@extends('backend::layouts.app')

@section('title', 'Shipping')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Shipping Module</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Available Shipping Methods</h2>
            <ul class="space-y-2">
                <li class="flex items-center">
                    <i class="fas fa-shopping-bag text-blue-500 mr-2"></i>
                    <span>Takeaway - Customer pickup</span>
                </li>
                <li class="flex items-center">
                    <i class="fas fa-truck text-green-500 mr-2"></i>
                    <span>Delivery - Home delivery service</span>
                </li>
            </ul>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Features</h2>
            <ul class="space-y-2">
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    <span>Configurable delivery zones</span>
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    <span>Distance-based pricing</span>
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    <span>Real-time tracking</span>
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    <span>Automated notifications</span>
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection