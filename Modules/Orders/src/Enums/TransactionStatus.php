<?php

namespace Modules\Orders\Enums;

enum TransactionStatus: string
{
    case PENDING    = 'pending';
    case COMPLETED  = 'completed';
    case FAILED     = 'failed';
    case VOIDED     = 'voided';
    case DECLINED   = 'declined';

    public function label(): string
    {
        return match ($this) {
            self::PENDING   => 'Pending',
            self::COMPLETED => 'Completed',
            self::FAILED    => 'Failed',
            self::VOIDED    => 'Voided',
            self::DECLINED  => 'Declined',
        };
    }

    public function class(): string
    {
        return match ($this) {
            self::PENDING => 'background-color: #B8860B; color: #ffffff;',
            self::COMPLETED => 'background-color: #198754; color: #ffffff;',
            self::FAILED => 'background-color: #dc3545; color: #ffffff;',
            self::VOIDED => 'background-color: #6c757d; color: #ffffff;',
            self::DECLINED => 'background-color: #b91c1c; color: #ffffff;',
        };
    }
}
