<?php

namespace Modules\Admin\Services;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Modules\Acl\Models\Customer;
use Modules\Admin\Support\DashboardFilters;
use Modules\Admin\Support\DashboardTimeline;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\Status;
use Modules\Payments\Models\Refund;
use Modules\Restaurant\Enums\OnboardingStep;
use Modules\Restaurant\Models\Restaurant;

class AdminDashboardService
{
    private const REVIEW_STATUSES = ['submitted', 'pending', 'under_review'];

    public function orders(): Builder
    {
        return Order::query()->where('vendor_type', (new Restaurant)->getMorphClass())
            ->whereIn('payment_status', ['paid', 'completed', 'captured', 'partially_refunded', 'refunded']);
    }

    public function summarize(DashboardFilters $filters): array
    {
        $orders = $this->orders()->whereBetween('created_at', $filters->bounds())
            ->when($filters->fulfillment !== 'all', fn ($q) => $q->where('order_type', $filters->fulfillment));
        $previous = $this->orders()->whereBetween('created_at', $filters->previousBounds())
            ->when($filters->fulfillment !== 'all', fn ($q) => $q->where('order_type', $filters->fulfillment));
        $report = app(DashboardOrderReport::class)->summarize(clone $orders, $previous->count(), $filters);
        $applications = Restaurant::query()->where('approval_status', 'pending')->where(function ($q) {
            $q->whereNotNull('submitted_at')->orWhereHas('onboardingSteps', fn ($steps) => $steps
                ->where('step_key', 'registration')->whereIn('status', self::REVIEW_STATUSES));
        });
        $documents = Restaurant::query()->where(function ($q) {
            $q->whereHas('onboardingSteps', fn ($steps) => $steps->whereIn('step_key', ['banking', 'tax', 'kyc'])
                ->whereIn('status', self::REVIEW_STATUSES))
                ->orWhereHas('certifications', fn ($certs) => $certs->whereIn('status', self::REVIEW_STATUSES));
        });
        $eligible = Restaurant::query()->where('status', 'active')->where('approval_status', 'approved')
            ->where('onboarding_status', 'approved')->where('is_accepting_orders', true)
            ->where(fn ($q) => $q->where('is_delivery', true)->orWhere('is_takeaway', true))
            ->whereDoesntHave('certifications', fn ($q) => $q->where('status', '!=', 'approved'));
        foreach (OnboardingStep::required() as $step) {
            $eligible->whereHas('onboardingSteps', fn ($q) => $q->where('step_key', $step->value)->where('status', 'approved'));
        }
        $refunds = Refund::query()->whereIn('refund_status', ['pending', 'processing', 'failed'])
            ->whereHas('order', fn ($q) => $q->where('vendor_type', (new Restaurant)->getMorphClass()));
        $reviewQueue = Restaurant::query()->where(function ($q) use ($applications, $documents) {
            $q->whereIn('id', (clone $applications)->select('id'))->orWhereIn('id', (clone $documents)->select('id'));
        })->with(['onboardingSteps', 'certifications'])->orderByRaw('COALESCE(submitted_at, created_at) ASC')->limit(5)->get();

        return [
            'restaurantCount' => Restaurant::count(),
            'newRestaurants' => Restaurant::whereBetween('created_at', $filters->bounds())->count(),
            'customerCount' => Customer::where('is_guest', false)->count(),
            'newCustomers' => Customer::where('is_guest', false)->whereBetween('created_at', $filters->bounds())->count(),
            'applicationCount' => $applications->count(), 'documentCount' => $documents->count(),
            'eligibleCount' => $eligible->count(), 'refundCount' => $refunds->count(),
            'pendingOrders' => $this->orders()->where('status', Order::STATUS_PENDING)->count(),
            ...$report, 'reviewQueue' => $reviewQueue,
            'refundQueue' => $refunds->with('order.vendor')->oldest('created_at')->limit(4)->get(),
            'updatedAt' => CarbonImmutable::now($filters->timezone),
        ];
    }

}
