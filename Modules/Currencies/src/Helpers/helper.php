<?php

use Modules\Currencies\Models\Currency;
use Modules\Currencies\Repositories\CurrencyRepository;
use Illuminate\Support\Facades\DB;

if (!function_exists('fn_get_currencies')) {
    /**
     * Get currencies with optional repository filters.
     * Supported filters: q, code, name, paginate, per_page, limit, order_by, order_dir
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\Paginator|\Illuminate\Database\Eloquent\Collection
     */
    function fn_get_currencies(array $filters = []): mixed
    {
        $repo = new CurrencyRepository();
        return $repo->all($filters);
    }
}

if (!function_exists('fn_get_currency')) {
    /**
     * Get single currency by id.
     */
    function fn_get_currency(int $id): ?Currency
    {
        try {
            $repo = new CurrencyRepository();
            return $repo->find($id);
        } catch (\Throwable $e) {
            return null;
        }
    }
}

if (!function_exists('fn_get_currency_by_code')) {
    /**
     * Find currency by its ISO code (exact match).
     */
    function fn_get_currency_by_code(string $code): ?Currency
    {
        $list = fn_get_currencies(['code' => $code]);
        if ($list instanceof \Illuminate\Contracts\Pagination\Paginator || $list instanceof \Illuminate\Database\Eloquent\Collection) {
            return $list->first() ?: null;
        }
        return null;
    }
}

if (!function_exists('fn_get_currency_symbol')) {
    /**
     * Return currency symbol by code (or null).
     */
    function fn_get_currency_symbol(string $code): ?string
    {
        $currency = fn_get_currency_by_code($code);
        return $currency ? ($currency->symbol ?? null) : null;
    }
}

if (!function_exists('fn_format_currency_amount')) {
    /**
     * Format a numeric amount using a currency's decimal places and optionally prepend symbol.
     *
     * @param float|int|string $amount
     * @param string|null $code
     * @param bool $withSymbol
     * @return string
     */
    function fn_format_currency_amount($amount, ?string $code = null, bool $withSymbol = true): string
    {
        $decimals = 2;
        $symbol = '';
        if ($code) {
            $currency = fn_get_currency_by_code($code);
            if ($currency) {
                $decimals = (int)($currency->decimal ?? $decimals);
                $symbol = $currency->symbol ? (string)$currency->symbol : '';
            }
        }

        $formatted = number_format((float)$amount, $decimals, '.', ',');
        if ($withSymbol && $symbol !== '') {
            return $symbol . ' ' . $formatted;
        }
        return $formatted;
    }
}



if (!function_exists('fn_convert_currency_rate')) {
    function fn_convert_currency_rate(
        float $amount,
        string $toCurrencyCode
    ): float {

        $fromCurrencyCode = fn_get_setting('general.currency') ?? 'USD';

        // Validate input
        if (!is_numeric($amount) || $amount < 0) {
            throw new InvalidArgumentException("Amount must be a non-negative number");
        }

        // If same currency, just round and return
        if ($fromCurrencyCode === $toCurrencyCode) {
            $toCurrency = DB::table('currencies')->where('code', $toCurrencyCode)->first();
            $decimalPlaces = $toCurrency->decimal ?? 2;
            return (float) round((float)$amount, $decimalPlaces);
        }

        // Get currency records using repository helper if available
        $fromCurrency = fn_get_currency_by_code($fromCurrencyCode);
        $toCurrency = fn_get_currency_by_code($toCurrencyCode);

        if (!$fromCurrency || !$toCurrency) {
            throw new InvalidArgumentException("Invalid currency code(s): {$fromCurrencyCode}, {$toCurrencyCode}");
        }

        // Try to find a direct exchange rate: base -> target
        $rateRow = DB::table('currency_exchange_rates')
            ->where('base_currency', $fromCurrency->id)
            ->where('target_currency', $toCurrency->id)
            ->first();

        $rate = null;
        if ($rateRow && isset($rateRow->rate)) {
            $rate = (float) $rateRow->rate;
        } else {
            // Attempt reverse rate and invert
            $reverse = DB::table('currency_exchange_rates')
                ->where('base_currency', $toCurrency->id)
                ->where('target_currency', $fromCurrency->id)
                ->first();
            if ($reverse && isset($reverse->rate) && $reverse->rate != 0) {
                $rate = 1 / (float) $reverse->rate;
            }
        }

        if (!$rate) {
            throw new InvalidArgumentException("Exchange rate not found for {$fromCurrencyCode} → {$toCurrencyCode}");
        }

        // Convert amount
        $convertedAmount = (float)$amount * $rate;

        // Round based on target currency decimal places
        $decimalPlaces = $toCurrency->decimal ?? 2;

        return (float) round($convertedAmount, $decimalPlaces);
    }
}

if (!function_exists('fn_convert_currency')) {
    function fn_convert_currency(
        float $amount,
        string $toCurrencyCode = 'USD'
    ): string {
        try {
            $convertedAmount = fn_convert_currency_rate($amount, $toCurrencyCode);
            $currency = fn_get_currency_by_code($toCurrencyCode);
            $decimalPlaces = $currency->decimal ?? 2;
            $formattedAmount = number_format($convertedAmount, $decimalPlaces, '.', ',');

            // Add symbol if exists, else append code
            if ($currency && !empty($currency->symbol)) {
                return trim($currency->symbol) . ' ' . $formattedAmount;
            }
            return $formattedAmount . ' ' . $toCurrencyCode;
        } catch (Exception $e) {
            // On error, return a safe formatted original amount with site currency
            $siteCode = fn_get_setting('general.currency') ?? $toCurrencyCode;
            return fn_format_currency_amount($amount, $siteCode, true);
        }
    }
}

if (!function_exists('fn_get_currency_data')) {
    function fn_get_currency_data($code)
    {
        return DB::table('currencies')
            ->where('code', $code)
            ->first();
    }
}


// Format amount using site default currency (safe helper)
if (!function_exists('fn_format_site_currency')) {
    function fn_format_site_currency(float $amount): string
    {
        try {
            $toCurrencyCode = fn_get_setting('general.currency') ?? 'USD';
            return fn_format_currency_amount($amount, $toCurrencyCode, true);
        } catch (Exception $e) {
            return (string)$amount;
        }
    }
}

if (!function_exists('fn_format_default_currency')) {
    function fn_format_default_currency()
    {
        $code = fn_get_setting('general.currency') ?? 'USD';
        return fn_get_currency_symbol($code);
    }
}
