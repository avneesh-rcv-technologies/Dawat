<?php

namespace Modules\Admin\Services;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Modules\Admin\Support\DashboardFilters;
use Modules\Admin\Support\DashboardTimeline;
use Modules\Orders\Models\Status;

/** Reports only on the caller's already-authorized order query. */
class DashboardOrderReport
{
    public function summarize(Builder $orders, int $previousTotal, DashboardFilters $filters): array
    {
        $statuses = (clone $orders)->select('status')->selectRaw('COUNT(*) AS count')->groupBy('status')->pluck('count', 'status');
        $total = (int) $statuses->sum();
        $definitions = Status::query()->where('type_code', 'O')->orderBy('id')
            ->get(['status_code', 'name', 'color'])->unique('status_code')->keyBy('status_code');
        $statusRows = $definitions->map(function (Status $status, $code) use ($statuses) {
            $color = trim((string) $status->color);
            return [
                'code' => $code,
                'label' => filled($status->name) ? $status->name : 'Status '.$code,
                'color' => preg_match('/^#(?:[0-9a-f]{3}|[0-9a-f]{6})$/i', $color) ? $color : '#64748b',
                'count' => (int) ($statuses[$code] ?? 0),
            ];
        })->values();
        // Preserve historical orders whose status definition has since been removed.
        foreach ($statuses as $code => $count) {
            if (!$definitions->has($code)) {
                $statusRows->push(['code' => $code, 'label' => 'Other ('.$code.')', 'color' => '#64748b', 'count' => (int) $count]);
            }
        }
        $types = (clone $orders)->select('order_type')->selectRaw('COUNT(*) AS count')->groupBy('order_type')->pluck('count', 'order_type');
        return [
            'totalOrders' => $total, 'previousOrders' => $previousTotal,
            'orderChange' => $previousTotal > 0 ? round(($total - $previousTotal) / $previousTotal * 100, 1) : null,
            'completedOrders' => (int) ($statuses['D'] ?? 0),
            'cancelledOrders' => (int) ($statuses['C'] ?? 0), 'failedOrders' => (int) ($statuses['F'] ?? 0),
            'completionRate' => $total ? round(($statuses['D'] ?? 0) / $total * 100, 1) : 0,
            'deliveryCount' => (int) ($types['delivery'] ?? 0), 'pickupCount' => (int) ($types['takeaway'] ?? 0),
            'otherCount' => (int) $types->except(['delivery', 'takeaway'])->sum(),
            'statuses' => $statusRows, 'trend' => $this->trend(clone $orders, $filters),
            'recentOrders' => (clone $orders)->with(['vendor', 'customer'])->latest('created_at')->latest('id')->limit(6)->get(),
        ];
    }

    private function trend(Builder $orders, DashboardFilters $filters): array
    {
        $timeline = DashboardTimeline::seed($filters, ['count' => 0, 'completed' => 0]);
        $points = $timeline['points'];
        $unit = $timeline['unit'];
        // Minute buckets preserve exact local days in timezones with non-hour offsets.
        $format = $filters->timezone === config('app.timezone', 'UTC') ? '%Y-%m-%d' : '%Y-%m-%d %H:%i:00';
        $rows = $orders->selectRaw("DATE_FORMAT(created_at, '$format') AS bucket_time, COUNT(*) AS count, SUM(CASE WHEN status = 'D' THEN 1 ELSE 0 END) AS completed")
            ->groupBy('bucket_time')->orderBy('bucket_time')->get();
        foreach ($rows as $row) {
            $date = CarbonImmutable::parse($row->bucket_time, config('app.timezone', 'UTC'))->setTimezone($filters->timezone);
            $key = DashboardTimeline::bucket($date, $unit)->format('Y-m-d');
            if (isset($points[$key])) {
                $points[$key]['count'] += (int) $row->count;
                $points[$key]['completed'] += (int) $row->completed;
            }
        }
        return ['unit' => $unit, 'points' => array_values($points)];
    }
}
