<?php

namespace Modules\Countries\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = [
        'addressable_id',
        'addressable_type',
        'address_type',
        'address_line_1',
        'address_line_2',
        'city_id',
        'state_id',
        'country_id',
        'zip_code',
        'latitude',
        'longitude',
        'is_default',
        "city_name",
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Get the parent addressable model.
     */
    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the country associated with the address.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the state associated with the address.
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(CountryState::class, 'state_id');
    }

    /**
     * Get the city associated with the address.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    /**
     * Get full address as a string.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_line_1,
            $this->address_line_2,
            $this->city?->name,
            $this->state?->name,
            $this->country?->name,
            $this->zip_code,
        ]);

        return implode(', ', $parts);
    }
}
