<?php

namespace Modules\FoodManagements\Http\Controllers\Concerns;

use Modules\FoodManagements\Models\FoodFeature;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\RestaurantOwnerRoleResolver;

/**
 * Food features are restaurant-owned. In the admin panel a restaurant owner only sees and
 * changes their own restaurant's feature groups and features; other admins see all.
 */
trait ScopesFeaturesToOwner
{
    /** Restaurant owners manage only their own restaurant's features. */
    protected function ownerRestaurantId(): ?int
    {
        $admin = auth('admin')->user();
        if (!$admin || !app(RestaurantOwnerRoleResolver::class)->isOwner($admin)) {
            return null;
        }

        return (int) Restaurant::where('admin_id', $admin->id)->value('id') ?: -1;
    }

    protected function authorizeOwner(FoodFeature $feature): void
    {
        $ownerRestaurantId = $this->ownerRestaurantId();
        abort_if($ownerRestaurantId && (int) $feature->restaurant_id !== $ownerRestaurantId, 404);
    }

    protected function ownedIds(array $ids): array
    {
        $ownerRestaurantId = $this->ownerRestaurantId();

        return $ownerRestaurantId
            ? FoodFeature::whereIn('feature_id', $ids)->forRestaurant($ownerRestaurantId)->pluck('feature_id')->all()
            : $ids;
    }
}
