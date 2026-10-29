@extends('backend::layouts.master')

@section('title', 'Order Analytics - ' . $order->order_number)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-1">Order Details</h2>
            <p class="text-muted mb-0">{{ $analytics['order']['order_number'] }} | {{ $analytics['order']['created_at'] }}</p>
        </div>
    </div>

    <!-- Order Info Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Customer Information</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Name:</strong> {{ $analytics['customer']['name'] }}</p>
                    <p class="mb-2"><strong>Email:</strong> {{ $analytics['customer']['email'] }}</p>
                    <p class="mb-0"><strong>Phone:</strong> {{ $analytics['customer']['phone'] }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Restaurant Information</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Name:</strong> {{ $analytics['restaurant']['name'] }}</p>
                    <p class="mb-0"><strong>Phone:</strong> {{ $analytics['restaurant']['phone'] }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Order Status</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Status:</strong> <span class="badge bg-info">{{ $analytics['order']['status'] }}</span></p>
                    <p class="mb-0"><strong>Type:</strong> <span class="text-capitalize">{{ str_replace('_', ' ', $analytics['order']['order_type']) }}</span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pricing Breakdown -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Pricing Breakdown</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <strong>${{ number_format($analytics['pricing']['subtotal'], 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Discount:</span>
                        <strong class="text-success">-${{ number_format($analytics['pricing']['discount'], 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax:</span>
                        <strong>${{ number_format($analytics['pricing']['tax'], 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Delivery Fee:</span>
                        <strong>${{ number_format($analytics['pricing']['delivery_fee'], 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Service Charge:</span>
                        <strong>${{ number_format($analytics['pricing']['service_charge'], 2) }}</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Total:</strong>
                        <strong class="text-primary">${{ number_format($analytics['pricing']['total'], 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Payment Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Payment Method:</span>
                        <strong class="text-capitalize">{{ str_replace('_', ' ', $analytics['payment_summary']['payment_method']) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Payment Status:</span>
                        <strong><span class="badge bg-{{ $analytics['payment_summary']['payment_status'] == 'paid' ? 'success' : 'warning' }}">{{ $analytics['payment_summary']['payment_status'] }}</span></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Paid:</span>
                        <strong class="text-success">${{ number_format($analytics['payment_summary']['total_paid'], 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Refunded:</span>
                        <strong class="text-danger">${{ number_format($analytics['payment_summary']['total_refunded'], 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Order Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analytics['items'] as $item)
                                <tr>
                                    <td>{{ $item['name'] }}</td>
                                    <td>{{ $item['quantity'] }}</td>
                                    <td>${{ number_format($item['unit_price'], 2) }}</td>
                                    <td>${{ number_format($item['total_price'], 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">No items found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment History -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Payment History</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Payment Type</th>
                                    <th>Processed At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analytics['payment_history'] as $payment)
                                <tr>
                                    <td>${{ number_format($payment['amount'], 2) }}</td>
                                    <td><span class="badge bg-{{ $payment['status'] == 'completed' ? 'success' : 'warning' }}">{{ $payment['status'] }}</span></td>
                                    <td class="text-capitalize">{{ str_replace('_', ' ', $payment['payment_type']) }}</td>
                                    <td>{{ $payment['processed_at'] ?? 'N/A' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">No payment history found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Transaction History</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Transaction Number</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analytics['transaction_history'] as $transaction)
                                <tr>
                                    <td>{{ $transaction['transaction_number'] }}</td>
                                    <td class="text-capitalize">{{ $transaction['type'] }}</td>
                                    <td>${{ number_format($transaction['amount'], 2) }}</td>
                                    <td><span class="badge bg-{{ $transaction['status'] == 'completed' ? 'success' : 'warning' }}">{{ $transaction['status'] }}</span></td>
                                    <td>{{ $transaction['created_at'] }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No transaction history found</td>
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
