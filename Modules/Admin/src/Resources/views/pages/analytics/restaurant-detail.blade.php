@extends('backend::layouts.master')

@section('title', 'Restaurant Analytics - ' . $restaurant->name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">{{ $restaurant->name }}</h2>
                    <p class="text-muted mb-0">Restaurant Analytics Dashboard</p>
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
                    <small class="opacity-75">Completion Rate: {{ $analytics['orders']['completion_rate'] }}%</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 opacity-75">Total Revenue</h6>
                    <h2 class="card-title mb-0">${{ number_format($analytics['revenue']['total'], 2) }}</h2>
                    <small class="opacity-75">Avg: ${{ number_format($analytics['revenue']['average_order_value'], 2) }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 opacity-75">Unique Customers</h6>
                    <h2 class="card-title mb-0">{{ $analytics['customers']['unique'] }}</h2>
                    <small class="opacity-75">Repeat: {{ $analytics['customers']['repeat_rate'] }}%</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 opacity-75">Transactions</h6>
                    <h2 class="card-title mb-0">{{ $analytics['transactions']['total'] }}</h2>
                    <small class="opacity-75">Completed: {{ $analytics['transactions']['completed'] }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Status Breakdown -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Order Status</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Completed:</span>
                                <strong class="text-success">{{ $analytics['orders']['completed'] }}</strong>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Pending:</span>
                                <strong class="text-warning">{{ $analytics['orders']['pending'] }}</strong>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Cancelled:</span>
                                <strong class="text-danger">{{ $analytics['orders']['cancelled'] }}</strong>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Cancellation Rate:</span>
                                <strong>{{ $analytics['orders']['cancellation_rate'] }}%</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Order Type Breakdown</h5>
                </div>
                <div class="card-body">
                    @foreach($analytics['order_type_breakdown'] as $type => $count)
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-capitalize">{{ str_replace('_', ' ', $type) }}:</span>
                        <strong>{{ $count }}</strong>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Payment & Transaction Summary -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Payment Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Collected:</span>
                        <strong class="text-success">${{ number_format($analytics['payments']['total_collected'], 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Pending:</span>
                        <strong class="text-warning">${{ number_format($analytics['payments']['pending'], 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Failed Count:</span>
                        <strong class="text-danger">{{ $analytics['payments']['failed_count'] }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Transaction Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Transactions:</span>
                        <strong>{{ $analytics['transactions']['total'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Completed:</span>
                        <strong class="text-success">{{ $analytics['transactions']['completed'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Refunds:</span>
                        <strong class="text-danger">{{ $analytics['transactions']['refunds'] }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Customers -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Top Customers</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Customer Name</th>
                                    <th>Email</th>
                                    <th>Orders</th>
                                    <th>Total Spent</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analytics['top_customers'] as $customer)
                                <tr>
                                    <td>{{ $customer['customer_name'] }}</td>
                                    <td>{{ $customer['customer_email'] }}</td>
                                    <td>{{ $customer['order_count'] }}</td>
                                    <td>${{ number_format($customer['total_spent'], 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">No customers found</td>
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
    <div class="row">
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
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analytics['recent_orders'] as $order)
                                <tr>
                                    <td>{{ $order['order_number'] }}</td>
                                    <td>{{ $order['customer_name'] }}</td>
                                    <td>${{ number_format($order['total'], 2) }}</td>
                                    <td><span class="badge bg-info">{{ $order['status'] }}</span></td>
                                    <td>{{ $order['created_at'] }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No orders found</td>
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
