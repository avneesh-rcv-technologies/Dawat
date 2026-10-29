{{-- Takeaway Processor Template --}}
<div class="shipping-option takeaway-option" data-method="takeaway">
    <div class="shipping-header">
        <h4>
            <i class="fas fa-shopping-bag"></i>
            Takeaway
        </h4>
        <div class="shipping-cost">
            @if($cost > 0)
                {{ $currency }} {{ number_format($cost, 2) }}
            @else
                FREE
            @endif
        </div>
    </div>
    
    <div class="shipping-details">
        <div class="delivery-time">
            <i class="fas fa-clock"></i>
            Ready in {{ $deliveryTime['min_time'] }}-{{ $deliveryTime['max_time'] }} {{ $deliveryTime['unit'] }}
        </div>
        
        @if(isset($metadata['pickup_location']) && $metadata['pickup_location'])
            <div class="pickup-location">
                <i class="fas fa-map-marker-alt"></i>
                <strong>Pickup Location:</strong> {{ $metadata['pickup_location'] }}
            </div>
        @endif
        
        @if(isset($metadata['pickup_instructions']) && $metadata['pickup_instructions'])
            <div class="pickup-instructions">
                <i class="fas fa-info-circle"></i>
                <strong>Instructions:</strong> {{ $metadata['pickup_instructions'] }}
            </div>
        @endif
        
        @if(isset($metadata['contact_phone']) && $metadata['contact_phone'])
            <div class="contact-phone">
                <i class="fas fa-phone"></i>
                <strong>Contact:</strong> {{ $metadata['contact_phone'] }}
            </div>
        @endif
    </div>
    
    <div class="shipping-actions">
        <button type="button" class="btn btn-primary select-shipping" data-method="takeaway">
            Select Takeaway
        </button>
    </div>
</div>