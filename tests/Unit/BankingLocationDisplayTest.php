<?php

namespace Tests\Unit;

use Modules\Countries\Models\City;
use Modules\Countries\Models\Country;
use Modules\Countries\Models\CountryState;
use Modules\Restaurant\Models\RestaurantBankingDetail;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BankingLocationDisplayTest extends TestCase
{
    public static function locations(): array
    {
        return [['city', City::class], ['state', CountryState::class], ['country', Country::class]];
    }

    #[DataProvider('locations')]
    public function test_related_location_takes_priority_over_legacy_text(string $location, string $class): void
    {
        $banking = new RestaurantBankingDetail();
        $banking->setRawAttributes([$location => 'Old text'], true);
        $related = new $class();
        $related->setRawAttributes(['name' => 'Selected location'], true);
        $banking->setRelation($location, $related);
        $this->assertSame('Selected location', $banking->locationName($location));
    }

    #[DataProvider('locations')]
    public function test_unmapped_legacy_text_is_safe_to_display(string $location): void
    {
        $banking = new RestaurantBankingDetail();
        $banking->setRawAttributes([$location => 'Legacy location'], true);
        $banking->setRelation($location, null);
        $this->assertSame('Legacy location', $banking->locationName($location));
        $banking->setRawAttributes(['legacy_'.$location => 'Archived location'], true);
        $this->assertSame('Archived location', $banking->locationName($location));
        $banking->setRawAttributes([], true);
        $this->assertNull($banking->locationName($location));
    }
}
