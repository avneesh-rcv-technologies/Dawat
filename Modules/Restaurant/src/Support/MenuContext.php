<?php

namespace Modules\Restaurant\Support;

use Modules\Restaurant\Models\Restaurant;

/**
 * Where the menu management screens are being shown: the onboarding flow (dark, owner only)
 * or the restaurant panel (light admin layout, owner or admin). The views use it to build
 * links and pick their layout, so the same screens work in both places.
 */
final class MenuContext
{
    private function __construct(
        public readonly Restaurant $restaurant,
        public readonly string $layout,
        public readonly bool $panel,
        private readonly string $routePrefix,
        private readonly array $routeParameters,
    ) {
    }

    public static function onboarding(Restaurant $restaurant): self
    {
        return new self($restaurant, 'restaurant::onboarding.menu.layout', false, 'restaurant.onboarding.menu.', []);
    }

    public static function panel(Restaurant $restaurant): self
    {
        return new self($restaurant, 'restaurant::admin.menu.layout', true, 'admin.restaurant.menu.', ['restaurant' => $restaurant->id]);
    }

    /** URL of a menu screen, e.g. route('items.edit', ['id' => 5]). */
    public function route(string $name, array $parameters = []): string
    {
        return route($this->routePrefix . $name, $this->routeParameters + $parameters);
    }

    /** Where the restaurant uploads dietary certificates (needed for Halal/Kosher labels). */
    public function certificatesUrl(): string
    {
        return $this->panel
            ? route('profile.edit', ['tab' => 'dietary_certificates', 'restaurant_id' => $this->restaurant->id])
            : route('restaurant.onboarding.kyc', ['step' => 'dietary_certificates']);
    }
}
