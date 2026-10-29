{{-- Delivery Processor Template --}}
<div class="shipping-option delivery-option card mb-3" data-method="delivery">
    <div class="card-body">
        <div class="shipping-header d-flex justify-content-between align-items-center mb-3">
            <div class="shipping-method">
                <h5 class="mb-1">
                    <i class="fas fa-truck text-primary mr-2"></i>
                    <strong>Home Delivery</strong>
                </h5>
                <small class="text-muted">Get your order delivered to your doorstep</small>
            </div>
            <div class="shipping-cost">
                <div class="cost-display">
                    @if($cost > 0)
                        <span class="price h4 mb-0 text-success">{{ $currency }}{{ number_format($cost, 2) }}</span>
                    @else
                        <span class="price h4 mb-0 text-success">FREE</span>
                    @endif
                </div>
            </div>
        </div>
    
        <div class="shipping-details">
            {{-- Delivery Time --}}
            <div class="delivery-info-item mb-2">
                <div class="d-flex align-items-center">
                    <i class="fas fa-clock text-info mr-2"></i>
                    <div>
                        <strong>Estimated Delivery:</strong> 
                        <span class="text-primary">{{ $deliveryTime['total_time'] }} {{ $deliveryTime['unit'] }}</span>
                        <br>
                        <small class="text-muted">
                            ({{ $deliveryTime['preparation_time'] }}min prep + {{ $deliveryTime['delivery_time'] }}min delivery)
                        </small>
                    </div>
                </div>
            </div>

            {{-- Minimum Order --}}
            @if(isset($metadata['minimum_order']) && $metadata['minimum_order'] > 0)
                <div class="delivery-info-item mb-2">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-shopping-cart text-warning mr-2"></i>
                        <div>
                            <strong>Minimum Order:</strong> 
                            <span class="text-dark">{{ $currency }}{{ number_format($metadata['minimum_order'], 2) }}</span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Free Delivery Threshold --}}
            @if(isset($metadata['free_delivery_threshold']) && $metadata['free_delivery_threshold'])
                <div class="delivery-info-item mb-2">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-gift text-success mr-2"></i>
                        <div>
                            <strong>Free Delivery:</strong> 
                            <span class="text-success">Orders over {{ $currency }}{{ number_format($metadata['free_delivery_threshold'], 2) }}</span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Delivery Area --}}
            @if(isset($metadata['delivery_radius']) && $metadata['delivery_radius'])
                <div class="delivery-info-item mb-2">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-map-marked-alt text-danger mr-2"></i>
                        <div>
                            <strong>Service Area:</strong> 
                            <span class="text-dark">Within {{ $metadata['delivery_radius'] }}km radius</span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Delivery Zones --}}
            @if(isset($metadata['delivery_zones']) && count($metadata['delivery_zones']) > 0)
                <div class="delivery-info-item mb-2">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-map text-info mr-2 mt-1"></i>
                        <div>
                            <strong>Available Zones:</strong>
                            <div class="zones-list mt-1">
                                @foreach($metadata['delivery_zones'] as $zone)
                                    <span class="badge badge-outline-primary mr-1 mb-1">
                                        {{ $zone['name'] ?? 'Zone' }} - {{ $currency }}{{ number_format($zone['delivery_fee'] ?? 0, 2) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    
        <div class="shipping-actions mt-3">
            <button type="button" class="btn btn-primary btn-block select-shipping" data-method="delivery">
                <i class="fas fa-check mr-2"></i>
                Choose Delivery
            </button>
        </div>
    </div>
</div>

{{-- Custom Styles for Delivery Option --}}
<style>
.delivery-option {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.delivery-option:hover {
    border-color: #007bff;
    box-shadow: 0 4px 15px rgba(0, 123, 255, 0.1);
    transform: translateY(-2px);
}

.delivery-option.selected {
    border-color: #28a745;
    background-color: #f8fff9;
}

.delivery-option .shipping-header {
    border-bottom: 1px solid #f1f3f4;
    padding-bottom: 15px;
}

.delivery-option .cost-display .price {
    font-weight: 700;
}

.delivery-info-item {
    padding: 8px 0;
    border-bottom: 1px solid #f8f9fa;
}

.delivery-info-item:last-child {
    border-bottom: none;
}

.badge-outline-primary {
    color: #007bff;
    border: 1px solid #007bff;
    background-color: transparent;
}

.zones-list {
    max-height: 100px;
    overflow-y: auto;
}

.select-shipping {
    font-weight: 600;
    border-radius: 8px;
    padding: 12px;
    transition: all 0.3s ease;
}

.select-shipping:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
}

@media (max-width: 768px) {
    .delivery-option .shipping-header {
        flex-direction: column;
        text-align: center;
    }
    
    .delivery-option .shipping-cost {
        margin-top: 10px;
    }
}
</style>