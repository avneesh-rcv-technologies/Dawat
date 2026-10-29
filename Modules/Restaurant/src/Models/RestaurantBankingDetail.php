<?php

namespace Modules\Restaurant\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Countries\Models\City;
use Modules\Countries\Models\Country;
use Modules\Countries\Models\CountryState;

class RestaurantBankingDetail extends Model
{
    protected $fillable = [
        'account_holder_name', 'routing_number', 'account_number', 'business_legal_name',
        'doing_business_as', 'address', 'city_id', 'state_id', 'country_id', 'postal_code',
    ];

    protected $hidden = ['account_number', 'routing_number', 'legacy_city', 'legacy_state'];

    protected $casts = ['account_number' => 'encrypted', 'routing_number' => 'encrypted',
        'city_id' => 'integer', 'state_id' => 'integer', 'country_id' => 'integer'];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(CountryState::class, 'state_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function locationName(string $location): ?string
    {
        // Legacy text attributes can shadow Eloquent relations with the same name.
        $related = $this->getRelationValue($location);
        $name = $related?->name ?: ($this->getRawOriginal('legacy_'.$location) ?: $this->getRawOriginal($location));

        return is_string($name) && trim($name) !== '' ? $name : null;
    }

    protected static function booted(): void
    {
        static::updated(function (self $banking) {
            if ($banking->wasChanged($banking->getFillable())) {
                app(\Modules\Restaurant\Services\OnboardingHistoryService::class)
                    ->detailsUpdated($banking->restaurant, 'banking');
            }
        });
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function maskedAccountNumber(): string
    {
        return '•••• ' . substr($this->account_number, -4);
    }
}
