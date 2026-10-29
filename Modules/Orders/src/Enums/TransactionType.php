<?php
namespace Modules\Orders\Enums;

enum TransactionType: string
{
    case PAYMENT        = 'payment';
    case REFUND         = 'refund';
    case CAPTURE        = 'capture';
    case AUTHORIZATION  = 'authorization';
    case ADJUSTMENT     = 'adjustment';
    case CHARGEBACK     = 'chargeback';

    public function label(): string
    {
        return match ($this) {
            self::PAYMENT       => 'Payment',
            self::REFUND        => 'Refund',
            self::CAPTURE       => 'Capture',
            self::AUTHORIZATION => 'Authorization',
            self::ADJUSTMENT    => 'Adjustment',
            self::CHARGEBACK    => 'Chargeback',
        };
    }

    public function class(): string
    {
        return match ($this) {
            self::PAYMENT => 'background-color: #0d6efd; color: #ffffff;',
            self::REFUND => 'background-color: #6f42c1; color: #ffffff;',
            self::CAPTURE => 'background-color: #198754; color: #ffffff;',
            self::AUTHORIZATION => 'background-color: #0dcaf0; color: #000000;',
            self::ADJUSTMENT => 'background-color: #B8860B; color: #ffffff;',
            self::CHARGEBACK => 'background-color: #dc3545; color: #ffffff;',
        };
    }
}
