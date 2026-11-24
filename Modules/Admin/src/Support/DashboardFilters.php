<?php

namespace Modules\Admin\Support;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class DashboardFilters
{
    public const PERIODS = [
        'today' => 'Today', 'yesterday' => 'Yesterday', '7d' => 'Last 7 days',
        '30d' => 'Last 30 days', 'month' => 'This month', 'custom' => 'Custom range',
    ];

    public function __construct(
        public readonly string $period,
        public readonly string $fulfillment,
        public readonly CarbonImmutable $start,
        public readonly CarbonImmutable $end,
        public readonly string $timezone,
        public readonly string $currency = 'USD',
    ) {}

    public static function from(array $input, string $timezone, string $defaultCurrency = 'USD'): self
    {
        $data = Validator::make($input, [
            'currency' => ['sometimes', 'nullable', 'string', 'regex:/^[A-Za-z]{3}$/'],
            'period' => ['sometimes', Rule::in(array_keys(self::PERIODS))],
            'fulfillment' => ['sometimes', Rule::in(['all', 'delivery', 'takeaway'])],
            'start_date' => ['nullable', 'required_if:period,custom', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'required_if:period,custom', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ])->validate();
        $today = CarbonImmutable::now($timezone)->startOfDay();
        $period = $data['period'] ?? '30d';
        [$start, $end] = match ($period) {
            'today' => [$today, $today],
            'yesterday' => [$today->subDay(), $today->subDay()],
            '7d' => [$today->subDays(6), $today],
            'month' => [$today->startOfMonth(), $today],
            'custom' => [CarbonImmutable::parse($data['start_date'], $timezone), CarbonImmutable::parse($data['end_date'], $timezone)],
            default => [$today->subDays(29), $today],
        };
        if ($start->diffInDays($end) > 365) {
            throw ValidationException::withMessages(['end_date' => 'Choose a date range of 366 days or fewer.']);
        }
        if ($end->greaterThan($today)) {
            throw ValidationException::withMessages(['end_date' => 'The reporting end date cannot be in the future.']);
        }

        $currency = strtoupper(trim(($data['currency'] ?? '') ?: $defaultCurrency));
        if (!preg_match('/^[A-Z]{3}$/', $currency)) $currency = 'USD';

        return new self($period, $data['fulfillment'] ?? 'all', $start->startOfDay(), $end->endOfDay(), $timezone, $currency);
    }

    public function bounds(): array
    {
        // Stored timestamps use the application timezone; filters use the reporting timezone.
        return [$this->start->setTimezone(config('app.timezone', 'UTC')),
            $this->end->setTimezone(config('app.timezone', 'UTC'))];
    }

    public function previousBounds(): array
    {
        $days = (int) $this->start->startOfDay()->diffInDays($this->end->startOfDay()) + 1;
        return [$this->start->subDays($days)->setTimezone(config('app.timezone', 'UTC')),
            $this->start->subDay()->endOfDay()->setTimezone(config('app.timezone', 'UTC'))];
    }
}
