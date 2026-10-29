<?php

namespace Modules\Restaurant\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Restaurant\Enums\OnboardingStatus;
use Modules\Restaurant\Models\OnboardingStatusHistory;
use Illuminate\Support\Facades\Auth;

class Restaurant extends Model
{
    use HasFactory;

    protected $table = 'restaurants';

    protected $fillable = [
        'admin_id',
        'name',
        'slug',
        'phone',
        'business_license',
        'description',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'country',
        'postal_code',
        'latitude',
        'longitude',
        'food_type',
        'cuisine_type',
        'opening_time',
        'closing_time',
        'is_takeaway',
        'is_delivery',
        'is_accepting_orders',
        'order_time_limit',
        'allow_order_cancellation',
        'cancellation_time_limit',
        'cancellable_statuses',
        'status',
        'onboarding_status',
        'approval_status',
        'rejection_reason',
        'approved_at',
        'approved_by',
        'onboarding_step',
        'submitted_at',
        'tax_type',
    ];

    protected $casts = [
        'is_takeaway' => 'boolean',
        'is_delivery' => 'boolean',
        'is_accepting_orders' => 'boolean',
        'allow_order_cancellation' => 'boolean',
        'cancellable_statuses' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'onboarding_status' => OnboardingStatus::class,
        'approved_at' => 'datetime',
        'onboarding_step' => 'integer',
        'submitted_at' => 'datetime',
    ];

    /* ============================
     | Relationships
     |============================ */

    public function hours()
    {
        return $this->hasOne(RestaurantHour::class);
    }

    public function canManageFeatures(): bool
    {
        return $this->onboarding_status === OnboardingStatus::APPROVED
            && $this->approval_status === 'approved';
    }

    public function holidays()
    {
        return $this->hasMany(RestaurantHoliday::class);
    }

    public function admin()
    {
        return $this->belongsTo(\Modules\Acl\Models\Admin::class, 'admin_id');
    }

    public function onboardingStatusHistories()
    {
        return $this->hasMany(OnboardingStatusHistory::class, 'restaurant_id');
    }

    public function onboardingSteps(): HasMany
    {
        return $this->hasMany(RestaurantOnboardingStep::class, 'restaurant_id');
    }

    public function bankingDetail(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(RestaurantBankingDetail::class);
    }

    public function taxInformation(): MorphMany
    {
        return $this->morphMany(TaxInformation::class, 'taxable');
    }

    protected static function booted()
    {
        static::updated(function (Restaurant $restaurant) {
            if ($restaurant->wasChanged(['status', 'onboarding_status', 'approval_status'])) {
                \Illuminate\Support\Facades\DB::afterCommit(function () use ($restaurant) {
                    $cache = app(\Modules\Restaurant\Services\RestaurantCacheService::class);
                    $cache->forgetList();
                    $cache->forgetDetail($restaurant->id);
                });
            }
            if ($restaurant->wasChanged([
                'name', 'phone', 'address_line1', 'address_line2', 'city', 'state',
                'country', 'postal_code', 'description', 'business_license',
            ])) {
                app(\Modules\Restaurant\Services\OnboardingHistoryService::class)
                    ->detailsUpdated($restaurant, 'registration');
            }
            if ($restaurant->wasChanged('onboarding_status')) {
                try {
                    $old = $restaurant->getOriginal('onboarding_status');
                    $new = $restaurant->onboarding_status;
                    $changedBy = null;
                    try {
                        $changedBy = Auth::guard('admin')->id() ?? Auth::id();
                    } catch (\Throwable $e) {
                        $changedBy = null;
                    }

                    $oldStatus = $old instanceof \BackedEnum ? $old->value : (string) $old;
                    $newStatus = $new instanceof \BackedEnum ? $new->value : (string) $new;

                    app(\Modules\Restaurant\Services\OnboardingHistoryService::class)->record(
                        $restaurant->id, null, 'status_changed', $oldStatus, $newStatus, $changedBy
                    );
                } catch (\Throwable $e) {
                    \Log::error('Failed to record onboarding status history', ['restaurant_id' => $restaurant->id, 'error' => $e->getMessage()]);
                }
            }
        });
    }

    /**
     * Get all addresses for the restaurant (polymorphic relationship)
     */
    public function addresses()
    {
        return $this->morphMany(\Modules\Countries\Models\Address::class, 'addressable');
    }

    /* ============================
     | Helpers
     |============================ */

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /* ============================
     | Cancellation Policy Methods
     |============================ */

    /**
     * Check if restaurant allows order cancellation
     */
    public function allowsOrderCancellation(): bool
    {
        return $this->allow_order_cancellation === true;
    }

    /**
     * Get cancellation time limit in minutes
     */
    public function getCancellationTimeLimit(): int
    {
        return $this->cancellation_time_limit ?? 15;
    }

    /**
     * Get allowed cancellable statuses
     * Returns default statuses if not configured
     */
    public function getCancellableStatuses(): array
    {
        $statuses = $this->cancellable_statuses;

        // Handle if it's a string (JSON) - convert to array
        if (is_string($statuses)) {
            $statuses = json_decode($statuses, true);
        }

        // Return if valid array
        if (!empty($statuses) && is_array($statuses)) {
            return $statuses;
        }

        // Default cancellable statuses for this workflow: W=Pending, O=Received, P=Accepted, H=Preparing
        return ['W', 'O', 'P', 'H'];
    }

    /**
     * Calculate cancellation fee for an order
     */
    public function calculateCancellationFee(float $orderTotal): float
    {
        // No cancellation fee for now
        return 0;
    }

    /**
     * Check if a specific order can be cancelled based on restaurant policy
     */
    // public function canCancelOrder($order): array
    // {
    //     // Check if cancellation is allowed at all
    //     if (!$this->allowsOrderCancellation()) {
    //         return [
    //             'can_cancel' => false,
    //             'reason' => 'Restaurant does not allow order cancellation',
    //             'reason_code' => 'POLICY_DISABLED'
    //         ];
    //     }

    //     // Check if order status is in allowed cancellable statuses
    //     $cancellableStatuses = $this->getCancellableStatuses();
    //     if (!in_array($order->status, $cancellableStatuses)) {
    //         return [
    //             'can_cancel' => false,
    //             'reason' => 'Order status does not allow cancellation',
    //             'reason_code' => 'STATUS_NOT_ALLOWED',
    //             'current_status' => $order->status,
    //             'allowed_statuses' => $cancellableStatuses
    //         ];
    //     }


    //     // $timeLimit = $this->getCancellationTimeLimit();
    //     // $orderCreatedAt = \Carbon\Carbon::parse($order->created_at);
    //     // $cancellationDeadline = $orderCreatedAt->copy()->addMinutes($timeLimit);
    //     // $now = \Carbon\Carbon::now();
    //     //
    //     // if ($now->greaterThan($cancellationDeadline)) {
    //     //     $minutesElapsed = $orderCreatedAt->diffInMinutes($now);
    //     //     return [
    //     //         'can_cancel' => false,
    //     //         'reason' => "Cancellation time limit exceeded. Orders can only be cancelled within {$timeLimit} minutes of placement",
    //     //         'reason_code' => 'TIME_LIMIT_EXCEEDED',
    //     //         'time_limit_minutes' => $timeLimit,
    //     //         'minutes_elapsed' => $minutesElapsed,
    //     //         'order_placed_at' => $orderCreatedAt->toDateTimeString(),
    //     //         'cancellation_deadline' => $cancellationDeadline->toDateTimeString()
    //     //     ];
    //     // }

    //     // All checks passed - Status based cancellation allowed
    //     return [
    //         'can_cancel' => true,
    //         'reason' => 'Order can be cancelled',
    //         'reason_code' => 'ALLOWED',
    //         'refund_amount' => $order->total,
    //     ];
    // }


    public function canCancelOrder($order): array
    {
        // Check if cancellation is allowed at all
        if (!$this->allowsOrderCancellation()) {
            return [
                'can_cancel' => false,
                'reason' => sprintf(
                    'Sorry, %s does not allow order cancellations. Please contact the restaurant directly for assistance.',
                    $this->name
                ),
                'reason_code' => 'POLICY_DISABLED',
                'restaurant_name' => $this->name,
                'restaurant_phone' => $this->phone ?? 'N/A'
            ];
        }

        // Check if order status is in allowed cancellable statuses
        $cancellableStatuses = $this->getCancellableStatuses();

        if (!in_array($order->status, $cancellableStatuses)) {
            // Get status names from database
            $currentStatus = fn_get_order_status($order->status, 'O');
            $currentStatusName = $currentStatus ? $currentStatus->name : ucfirst($order->status);

            // Get allowed status names
            $allowedStatusNames = [];
            foreach ($cancellableStatuses as $statusCode) {
                $status = fn_get_order_status($statusCode, 'O');
                $allowedStatusNames[] = $status ? $status->name : ucfirst($statusCode);
            }

            return [
                'can_cancel' => false,
                'reason' => sprintf(
                    'Your order cannot be cancelled because it is currently "%s".',
                    $currentStatusName,
                    implode(', ', $allowedStatusNames)
                ),
                'reason_code' => 'STATUS_NOT_ALLOWED',
                'current_status' => $order->status,
                'current_status_name' => $currentStatusName,
                'allowed_statuses' => $cancellableStatuses,
                'allowed_status_names' => $allowedStatusNames,
                'help_text' => sprintf(
                    'For assistance, please contact %s at %s',
                    $this->name,
                    $this->phone ?? 'the restaurant'
                )
            ];
        }

        // All checks passed - Status based cancellation allowed
        return [
            'can_cancel' => true,
            'reason' => 'Your order is eligible for cancellation.',
            'reason_code' => 'ALLOWED',
            'refund_amount' => $order->total,
            'refund_message' => $order->isPaid()
                ? sprintf('A refund of %s %.2f will be processed within 5-7 business days.', $order->currency ?? 'USD', $order->total)
                : 'No payment has been processed for this order.'
        ];
    }



    /**
     * Get cancellation policy summary
     */
    public function getCancellationPolicy(): array
    {
        return [
            'enabled' => $this->allowsOrderCancellation(),
            'time_limit_minutes' => $this->getCancellationTimeLimit(),
            'cancellable_statuses' => $this->getCancellableStatuses(),
            'policy_text' => $this->getCancellationPolicyText()
        ];
    }

    /**
     * Get human-readable cancellation policy text
     */
    public function getCancellationPolicyText(): string
    {
        if (!$this->allowsOrderCancellation()) {
            return 'This restaurant does not allow order cancellation.';
        }

        // Status based cancellation (time limit removed)
        $text = "You can cancel your order based on order status.";

        return $text;
    }


    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn($value) => decode_html_entities($value)
        );
    }

    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn($value) => decode_html_entities($value)
        );
    }


}
