<?php

namespace Modules\Admin\Support;

use Carbon\CarbonImmutable;

final class DashboardTimeline
{
    public static function bucket(CarbonImmutable $date, string $unit): CarbonImmutable
    {
        return match ($unit) {
            'month' => $date->startOfMonth(), 'week' => $date->startOfWeek(), default => $date->startOfDay(),
        };
    }

    public static function seed(DashboardFilters $filters, array $defaults): array
    {
        $days = (int) $filters->start->startOfDay()->diffInDays($filters->end->startOfDay()) + 1;
        $unit = $days > 180 ? 'month' : ($days > 45 ? 'week' : 'day');
        $points = [];
        for ($date = self::bucket($filters->start, $unit); $date <= $filters->end; $date = $date->add(1, $unit)) {
            $key = $date->format('Y-m-d');
            $points[$key] = ['date' => $key, 'label' => $date->format($unit === 'month' ? 'M Y' : 'M j')] + $defaults;
        }

        return ['unit' => $unit, 'points' => $points];
    }
}
