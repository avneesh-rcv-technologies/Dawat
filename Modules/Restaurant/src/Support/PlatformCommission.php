<?php

namespace Modules\Restaurant\Support;

use Modules\Restaurant\Models\Restaurant;

/**
 * Platform commission the admin sets in General Settings and every restaurant
 * agrees to (with a checkbox) in the banking onboarding step.
 */
class PlatformCommission
{
    public const SETTING_KEY = 'general.platform_commission_percentage';
    public const INPUT = 'accept_platform_commission';
    public const DEFAULT_PERCENTAGE = 10.0;

    /** Current configured percentage, defaulting to 10% when unset or invalid. */
    public static function percentage(): float
    {
        $value = fn_get_setting(self::SETTING_KEY, self::DEFAULT_PERCENTAGE);

        if ($value === null || $value === '' || !is_numeric($value)) {
            return self::DEFAULT_PERCENTAGE;
        }

        return round(max(0, min(100, (float) $value)), 2);
    }

    /** Owners must accept the current rate on every banking submission and update. */
    public static function isRequired(): bool
    {
        return true;
    }

    public static function label(?float $percentage = null): string
    {
        $percentage ??= self::percentage();

        return 'I agree that a ' . self::format($percentage) . '% platform commission will be applied to every order amount.';
    }

    public static function format(float $percentage): string
    {
        return rtrim(rtrim(number_format($percentage, 2, '.', ''), '0'), '.');
    }

    /** Has this restaurant agreed to the commission that is in effect right now? */
    public static function acceptedCurrent(Restaurant $restaurant): bool
    {
        return $restaurant->platform_commission_accepted
            && $restaurant->platform_commission_percentage !== null
            && (float) $restaurant->platform_commission_percentage === (float) (self::percentage());
    }

    public static function accept(Restaurant $restaurant): void
    {
        $restaurant->forceFill([
            'platform_commission_accepted' => true,
            'platform_commission_percentage' => self::percentage(),
            'platform_commission_accepted_at' => now(),
        ])->save();
    }

    /** Payload for the onboarding / banking APIs, so the app can render the checkbox. */
    public static function forRestaurant(Restaurant $restaurant): array
    {
        $percentage = self::percentage();

        return [
            'input_name' => self::INPUT,
            'percentage' => $percentage,
            'is_required' => self::isRequired(),
            'label' => self::label($percentage),
            'accepted' => (bool) $restaurant->platform_commission_accepted,
            'accepted_percentage' => $restaurant->platform_commission_percentage !== null
                ? (float) $restaurant->platform_commission_percentage : null,
            'accepted_at' => $restaurant->platform_commission_accepted_at?->toIso8601String(),
            // True when the admin changed the rate after this restaurant agreed.
            'needs_reacceptance' => $restaurant->platform_commission_accepted && self::isRequired() && !self::acceptedCurrent($restaurant),
        ];
    }
}
