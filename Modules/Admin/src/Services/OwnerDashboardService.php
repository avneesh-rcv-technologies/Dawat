<?php

namespace Modules\Admin\Services;

use Carbon\CarbonImmutable;
use Modules\Acl\Models\Admin;
use Modules\Admin\Support\DashboardFilters;
use Modules\FoodManagements\Models\FoodItem;
use Modules\Rating\Models\Rating;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\RestaurantOnboardingCompletionService;

class OwnerDashboardService
{
    public function summarize(Admin $actor, DashboardFilters $filters): ?array
    {
        // Resolve ownership from the authenticated account, never a URL/query parameter.
        if (!$actor->getKey()) return null;
        $restaurant = Restaurant::query()->where('admin_id', $actor->getKey())->orderBy('id')->first();
        if (!$restaurant) return null;

        $base = app(AdminDashboardService::class)->orders()->where('vendor_id', $restaurant->id)
            ->when($filters->fulfillment !== 'all', fn ($q) => $q->where('order_type', $filters->fulfillment));
        $orders = (clone $base)->whereBetween('created_at', $filters->bounds());
        $report = app(DashboardOrderReport::class)->summarize(clone $orders,
            (clone $base)->whereBetween('created_at', $filters->previousBounds())->count(), $filters);
        $now = CarbonImmutable::now($filters->timezone);
        $todayStart = $now->startOfDay()->setTimezone(config('app.timezone'));
        $todayEnd = $now->endOfDay()->setTimezone(config('app.timezone'));
        $schedule = 'COALESCE(preferred_delivery_date, estimated_delivery_time)';
        $live = (clone $base)->whereIn('status', ['W', 'O', 'P', 'H', 'Z', 'N']);
        $today = (clone $base)->whereNotIn('status', ['C', 'F', 'R'])->whereRaw("$schedule BETWEEN ? AND ?", [$todayStart, $todayEnd]);
        $upcoming = (clone $live)->whereRaw("$schedule > ?", [$todayEnd]);
        $week = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $now->addDays($i);
            $week[] = ['date' => $date->format('M j'), 'label' => $i === 0 ? 'Today' : $date->format('D'),
                'count' => (clone $live)->whereRaw("$schedule BETWEEN ? AND ?", [
                    $date->startOfDay()->setTimezone(config('app.timezone')), $date->endOfDay()->setTimezone(config('app.timezone')),
                ])->count()];
        }
        $items = FoodItem::query()->where('restaurant_id', $restaurant->id);
        $reviews = Rating::query()->where('ratable_type', $restaurant->getMorphClass())->where('ratable_id', $restaurant->id)->approved()->active();
        $pending = app(RestaurantOnboardingCompletionService::class)->pendingSteps($restaurant);
        $blockers = [];
        if ($restaurant->approval_status !== 'approved') $blockers[] = 'Application approval pending';
        if ($pending) $blockers[] = 'Sections awaiting approval: '.implode(', ', $pending);
        if (!$restaurant->canManageFeatures()) $blockers[] = 'Onboarding approval pending';
        if ($restaurant->status !== 'active') $blockers[] = 'Restaurant is inactive';
        if (!$restaurant->is_accepting_orders) $blockers[] = 'Order acceptance is paused';
        if (!$restaurant->is_delivery && !$restaurant->is_takeaway) $blockers[] = 'Enable delivery or pickup';

        return $report + [
            'restaurant' => $restaurant, 'updatedAt' => $now, 'blockers' => $blockers, 'pendingSteps' => $pending,
            'finance' => app(AdminDashboardFinanceService::class)->summarize($filters, $restaurant->id),
            'todayCount' => $today->count(), 'pendingCount' => (clone $live)->where('status', 'W')->count(),
            'activeCount' => (clone $live)->whereIn('status', ['O', 'P', 'H', 'Z', 'N'])->count(),
            'completedToday' => (clone $base)->where('status', 'D')->whereRaw('COALESCE(actual_delivery_time, preferred_delivery_date, estimated_delivery_time, created_at) BETWEEN ? AND ?', [$todayStart, $todayEnd])->count(),
            'upcomingCount' => $upcoming->count(), 'week' => $week,
            'attention' => (clone $today)->with('customer')->orderByRaw('COALESCE(preferred_delivery_date, estimated_delivery_time, created_at) ASC')->orderBy('id')->limit(5)->get(),
            'upcoming' => $upcoming->with('customer')->orderByRaw("$schedule ASC")->orderBy('id')->limit(5)->get(),
            'unscheduledCount' => (clone $live)->whereNull('preferred_delivery_date')->whereNull('estimated_delivery_time')->count(),
            'menuCount' => (clone $items)->count(),
            'availableCount' => (clone $items)->where('is_active', true)->where('is_available', true)->where('in_stock', true)->count(),
            'reviewCount' => (clone $reviews)->count(), 'rating' => (clone $reviews)->avg('rating'),
            'reviews' => $reviews->with('user:id,name')->latest()->limit(3)->get(),
            'hours' => $restaurant->hours()->first(),
            'holidays' => $restaurant->holidays()->where('date', '>=', $now->toDateString())->orderBy('date')->limit(3)->get(),
        ];
    }
}
