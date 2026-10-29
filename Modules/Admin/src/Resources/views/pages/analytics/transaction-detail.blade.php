@extends('backend::layouts.master')

@section('title', 'Transaction Analytics - ' . $transaction->transaction_number)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-1">Transaction Details</h2>
            <p class="text-muted mb-0">{{ $analytics['transaction']['transaction_number'] }}</p>
        </div>
    </div>

    <!-- Transaction Info -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Transaction Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-5"><strong>Transaction Number:</strong></div>
                        <div class="col-7">{{ $analytics['transaction']['transaction_number'] }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5"><strong>Type:</strong></div>
                        <div class="col-7 text-capitalize">{{ $analytics['transaction']['type'] }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5"><strong>Amount:</strong></div>
                        <div class="col-7 text-success"><strong>${{ number_format($analytics['transaction']['amount'], 2) }}</strong></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5"><strong>Status:</strong></div>
                        <div class="col-7">
                            <span class="badge bg-{{ $analytics['transaction']['status'] == 'completed' ? 'success' : ($analytics['transaction']['status'] == 'pending' ? 'warning' : 'danger') }}">
                                {{ $analytics['transaction']['status'] }}
                            </span>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5"><strong>Currency:</strong></div>
                        <div class="col-7">{{ $analytics['transaction']['currency'] }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5"><strong>Gateway Reference:</strong></div>
                        <div class="col-7">{{ $analytics['transaction']['gateway_reference'] ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5"><strong>Created At:</strong></div>
                        <div class="col-7">{{ $analytics['transaction']['created_at'] }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5"><strong>Processed At:</strong></div>
                        <div class="col-7">{{ $analytics['transaction']['processed_at'] ?? 'Not processed yet' }}</div>
                    </div>
                    @if($analytics['transaction']['notes'])
                    <div class="row">
                        <div class="col-12">
                            <strong>Notes:</strong>
                            <p class="mt-2 p-2 bg-light rounded">{{ $analytics['transaction']['notes'] }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Related Order</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-5"><strong>Order Number:</strong></div>
                        <div class="col-7">{{ $analytics['order']['order_number'] }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5"><strong>Order Total:</strong></div>
                        <div class="col-7">${{ number_format($analytics['order']['total'], 2) }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5"><strong>Order Status:</strong></div>
                        <div class="col-7"><span class="badge bg-info">{{ $analytics['order']['status'] }}</span></div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Payment Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-5"><strong>Payment Status:</strong></div>
                        <div class="col-7">
                            <span class="badge bg-{{ $analytics['payment']['status'] == 'completed' ? 'success' : 'warning' }}">
                                {{ $analytics['payment']['status'] }}
                            </span>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5"><strong>Payment Type:</strong></div>
                        <div class="col-7 text-capitalize">{{ str_replace('_', ' ', $analytics['payment']['payment_type']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer & Restaurant Info -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Customer Information</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Name:</strong> {{ $analytics['customer']['name'] }}</p>
                    <p class="mb-0"><strong>Email:</strong> {{ $analytics['customer']['email'] }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Restaurant Information</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0"><strong>Name:</strong> {{ $analytics['restaurant']['name'] }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
