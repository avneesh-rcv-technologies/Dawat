<?php

namespace Modules\Discounts\Listeners;

use Modules\Core\Events\RegisterGlobalSearch;
use Modules\Discounts\Models\Discount;
use Modules\Discounts\Models\Coupon;
use Modules\Discounts\Models\CouponUsage;

class GlobalSearchEvent
{
    /**
     * Handle the event.
     */
    public function handle(RegisterGlobalSearch $event): void
    {
        $query = $event->query;
        $moduleFilter = $event->module;

        // Only super admin should see discounts in global search
        $currentAdmin = auth('admin')->user();
        if ($currentAdmin) {
            $restaurant = \Modules\Restaurant\Models\Restaurant::where('admin_id', $currentAdmin->id)->first();
            
            // If restaurant admin, don't register this preference
            if ($restaurant) {
                return;
            }
        }

        $event->addSearchPreference('discounts', 'Discounts & Coupons');
        
        if (strlen($query) < 2) {
            return;
        }

        // If module filter is set and isn't 'discounts' or 'all', skip
        if ($moduleFilter !== 'all' && !in_array($moduleFilter, ['discounts'])) {
            return;
        }
  
        // Search discounts by name
        $this->searchDiscounts($event, $query);

        // Search coupons by code with morph usage data
        $this->searchCoupons($event, $query);

        // Search coupon usages using morph relationships
        $this->searchCouponUsages($event, $query);
    }

    /**
     * Search discounts by name with creator information
     */
    private function searchDiscounts(RegisterGlobalSearch $event, string $query): void
    {
        $discounts = Discount::where('name', 'LIKE', "%{$query}%")
            ->with('createdBy')
            ->limit(5)
            ->get();

        foreach ($discounts as $discount) {
            $creatorInfo = '';
            if ($discount->createdBy) {
                $creatorName = $discount->createdBy->name ?? $discount->createdBy->email ?? '';
                $creatorInfo = $creatorName ? ' (by ' . $creatorName . ')' : '';
            }

            $event->addResult([
                'id' => $discount->id,
                'title' => $discount->name . $creatorInfo,
                'url' => 'discount?filters[name]=' . $discount->name,
                'type' => 'discount',
                'module' => 'Discount',
                'module_name' => 'Discounts',
            ]);
        }
    }

    /**
     * Search coupons by code with discount relationship
     */
    private function searchCoupons(RegisterGlobalSearch $event, string $query): void
    {
        $coupons = Coupon::where('code', 'LIKE', "%{$query}%")
            ->with('discount')
            ->limit(5)
            ->get();

        foreach ($coupons as $coupon) {
            $event->addResult([
                'id' => $coupon->id,
                'title' => $coupon->code . ' - ' . ($coupon->discount?->name ?? 'Coupon'),
                'url' => 'discount?filters[code]=' . $coupon->code,
                'type' => 'discount',
                'module' => 'Discount',
                'module_name' => 'Coupons',
            ]);
        }
    }

    /**
     * Search coupon usages using morph relationships to find who used a coupon
     */
    private function searchCouponUsages(RegisterGlobalSearch $event, string $query): void
    {
        // Search by coupon code in usages
        $usages = CouponUsage::with(['coupon', 'usable'])
            ->whereHas('coupon', function ($q) use ($query) {
                $q->where('code', 'LIKE', "%{$query}%");
            })
            ->orderBy('used_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($usages as $usage) {
            if ($usage->usable) {
                $usableTitle = $this->getUsableTitle($usage->usable);
                $couponCode = $usage->coupon?->code ?? 'Unknown';
                
                $event->addResult([
                    'id' => $usage->id,
                    'title' => $usableTitle . ' used coupon ' . $couponCode,
                    'url' => 'discount?filters[coupon_code]=' . urlencode($couponCode),
                    'type' => 'discount',
                    'module' => 'Discount',
                    'module_name' => 'Coupon Usage',
                ]);
            }
        }
    }

    /**
     * Get a readable title from the usable entity based on its type
     */
    private function getUsableTitle($usable): string
    {
        // For other models, try common attributes
        return $usable->name ?? $usable->title ?? $usable->email ?? class_basename($usable);
    }
}
