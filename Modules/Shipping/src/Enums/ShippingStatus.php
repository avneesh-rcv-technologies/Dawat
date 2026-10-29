<?php

namespace Modules\Shipping\Enums;

enum ShippingStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case OUT_FOR_DELIVERY = 'out_for_delivery';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case RETURNED = 'returned';
    case READY_FOR_PICKUP = 'ready_for_pickup';
    case PICKED_UP = 'picked_up';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::SHIPPED => 'Shipped',
            self::OUT_FOR_DELIVERY => 'Out for Delivery',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
            self::RETURNED => 'Returned',
            self::READY_FOR_PICKUP => 'Ready for Pickup',
            self::PICKED_UP => 'Picked Up',
        };
    }
}