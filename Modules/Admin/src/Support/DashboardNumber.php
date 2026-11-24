<?php

namespace Modules\Admin\Support;

final class DashboardNumber
{
    public static function compact(int|float $value): string
    {
        $units = ['', 'K', 'M', 'B', 'T', 'Q', 'Qi'];
        $scaled = abs($value);
        $unit = 0;
        while ($scaled >= 1000 && $unit < count($units) - 1) {
            $scaled /= 1000;
            $unit++;
        }
        $rounded = round($scaled, $unit === 0 ? 0 : 1);
        // Promote values such as 999,999 to 1M rather than displaying 1,000K.
        if ($rounded >= 1000 && $unit < count($units) - 1) {
            $rounded /= 1000;
            $unit++;
        }
        $formatted = $unit === 0 ? number_format($rounded, 0)
            : rtrim(rtrim(number_format($rounded, 1, '.', ''), '0'), '.');

        return ($value < 0 && $rounded > 0 ? '-' : '').$formatted.$units[$unit];
    }

    public static function currency(int|float|string $amount, string $currency): string
    {
        if (abs((float) $amount) < 1000) {
            return fn_format_currency_amount($amount, $currency, true);
        }

        return (fn_get_currency_symbol($currency) ?: $currency).' '.self::compact((float) $amount);
    }
}
