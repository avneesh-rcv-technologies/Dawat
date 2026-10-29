<?php

namespace Modules\Core\Enums;

enum Language: string
{

    case EN = 'en';
    case ES = 'es';

    public function label(): string
    {
        return match($this) {
            self::EN => 'English',
            self::ES => 'Spanish'
        };
    }

    public static function all(): array
    {
        return array_column(self::cases(), 'value');
    }
}
