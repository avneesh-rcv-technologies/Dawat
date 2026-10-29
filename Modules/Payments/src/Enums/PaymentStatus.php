<?php

namespace Modules\Payments\Enums;

enum PaymentStatus: string
{
    case PENDING    = 'pending';
    case COMPLETED  = 'completed';
    case FAILED     = 'failed';
    case REFUNDED   = 'refunded';
    case PARTIALLY_REFUNDED = 'partially_refunded';
    case CANCELLED  = 'cancelled';
    case AUTHORIZED = 'authorized';
    case CAPTURED   = 'captured';
    case PAID       = 'paid';
    case UNPAID     = 'unpaid';
    case PROCESSING = 'processing';

    public function label(): string
    {
        return match ($this) {
            self::PENDING    => 'Pending',
            self::COMPLETED  => 'Completed',
            self::FAILED     => 'Failed',
            self::REFUNDED   => 'Refunded',
            self::PARTIALLY_REFUNDED => 'Partially Refunded',
            self::CANCELLED  => 'Cancelled',
            self::AUTHORIZED => 'Authorized',
            self::CAPTURED   => 'Captured',
            self::PAID       => 'Paid',
            self::UNPAID     => 'Unpaid',
            self::PROCESSING => 'Processing',
        };
    }

    public function transactionNote(string $gateway): string
    {
        $state = match ($this) {
            self::COMPLETED, self::CAPTURED, self::PAID => 'completed',
            self::PENDING => 'pending',
            self::UNPAID => 'unpaid',
            self::PROCESSING => 'processing',
            self::AUTHORIZED => 'authorized',
            self::FAILED => 'failed',
            self::CANCELLED => 'cancelled',
            self::REFUNDED => 'refunded',
            self::PARTIALLY_REFUNDED => 'partially refunded',
        };

        return "Payment {$state} via {$gateway}";
    }

    public function class(): string
    {
        return match ($this) {
            self::PENDING    => 'background-color: #B8860B; color: #ffffff;',
            self::COMPLETED  => 'background-color: #198754; color: #ffffff;',
            self::FAILED     => 'background-color: #dc3545; color: #ffffff;',
            self::REFUNDED   => 'background-color: #6f42c1; color: #ffffff;',
            self::PARTIALLY_REFUNDED => 'background-color: #6f42c1; color: #ffffff;',
            self::CANCELLED  => 'background-color: #6c757d; color: #ffffff;',
            self::AUTHORIZED => 'background-color: #0dcaf0; color: #000000;',
            self::CAPTURED   => 'background-color: #0d6efd; color: #ffffff;',
            self::PAID       => 'background-color: #198754; color: #ffffff;',
            self::UNPAID     => 'background-color: #fd7e14; color: #ffffff;',
            self::PROCESSING => 'background-color: #0d6efd; color: #ffffff;',
        };
    }
}
