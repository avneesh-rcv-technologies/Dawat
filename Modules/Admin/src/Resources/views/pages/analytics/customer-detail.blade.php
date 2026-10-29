@extends('backend::layouts.master')

@section('title', 'Customer Analytics - ' . $customer->name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">{{ $customer->name }}</h2>
                    <p class="text-muted mb-0">{{ $customer->email }} | {{ $customer->phone }}</p>
                    <small class="text-muted">Member since: {{ $analytics['customer']['member_since'] }}</small>
                </div>
                <div>
                    <form method="GET" class="d-flex gap-2">
                        <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}" required>
                        <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}" required>
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 opacity-75">Total Orders</h6>
                    <h2 class="card-title mb-0">{{ $analytics['orders']['total'] }}</h2>
                    <small class="opacity-75">Completed: {{ $analytics['orders']['completed'] }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 opacity-75">Total Spent</h6>
                    <h2 class="card-title mb-0">${{ number_format($analytics['spending']['total'], 2) }}</h2>
                    <small class="opacity-75">Avg: ${{ number_format($analytics['spending']['average_order_value'], 2) }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 opacity-75">Pending Orders</h6>
                    <h2 class="card-title mb-0">{{ $analytics['orders']['pending'] }}</h2>
                    <small class="opacity-75">Active orders</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 opacity-75">Cancelled</h6>
                    <h2 class="card-title mb-0">{{ $analytics['orders']['cancelled'] }}</h2>
                    <small class="opacity-75">Cancelled orders</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Favorite Restaurants -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Favorite Restaurants</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Restaurant Name</th>
                                    <th>Order Count</th>
                                    <th>Total Spent</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analytics['favorite_restaurants'] as $restaurant)
                                <tr>
                                    <td>{{ $restaurant['restaurant_name'] }}</td>
                                    <td>{{ $restaurant['order_count'] }}</td>
                                    <td>${{ number_format($restaurant['total_spent'], 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">No restaurants found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Preferences -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Order Type Preferences</h5>
                </div>
                <div class="card-body">
                    @foreach($analytics['order_type_preferences'] as $type => $count)
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-capitalize">{{ str_replace('_', ' ', $type) }}:</span>
                        <strong>{{ $count }}</strong>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Payment Method Preferences</h5>
                </div>
                <div class="card-body">
                    @foreach($analytics['payment_method_preferences'] as $method => $count)
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-capitalize">{{ str_replace('_', ' ', $method) }}:</span>
                        <strong>{{ $count }}</strong>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Spending Trend -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Monthly Spending Trend</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Orders</th>
                                    <th>Total Spent</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analytics['monthly_spending_trend'] as $month)
                                <tr>
                                    <td>{{ $month->month }}</td>
                                    <td>{{ $month->order_count }}</td>
                                    <td>${{ number_format($month->total_spent, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">No data found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Orders</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order Number</th>
                                    <th>Restaurant</th>
                                    <th>Type</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analytics['recent_orders'] as $order)
                                <tr>
                                    <td>{{ $order['order_number'] }}</td>
                                    <td>{{ $order['restaurant_name'] }}</td>
                                    <td class="text-capitalize">{{ str_replace('_', ' ', $order['order_type']) }}</td>
                                    <td>${{ number_format($order['total'], 2) }}</td>
                                    <td><span class="badge bg-info">{{ $order['status'] }}</span></td>
                                    <td>{{ $order['created_at'] }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No orders found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Transactions</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Transaction Number</th>
                                    <th>Order Number</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analytics['recent_transactions'] as $transaction)
                                <tr>
                                    <td>{{ $transaction['transaction_number'] }}</td>
                                    <td>{{ $transaction['order_number'] }}</td>
                                    <td class="text-capitalize">{{ $transaction['type'] }}</td>
                                    <td>${{ number_format($transaction['amount'], 2) }}</td>
                                    <td><span class="badge bg-{{ $transaction['status'] == 'completed' ? 'success' : 'warning' }}">{{ $transaction['status'] }}</span></td>
                                    <td>{{ $transaction['created_at'] }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No transactions found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
