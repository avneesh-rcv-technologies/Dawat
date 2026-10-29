<?php

namespace Modules\Tax\Models;

use Modules\Tax\Enums\TaxType;
use Modules\Countries\Models\Country;
use Illuminate\Database\Eloquent\Model;
use Modules\PartsManagement\Models\Part;
use Modules\PartsManagement\Models\PartsTax;

class TaxRate extends Model
{
    protected $fillable = [
        'name',
        'rate_value',
        'type',
        'country_id',
        'state',
        'postcode',
        'city',
        'is_active',
        'priority'
    ];

    protected $casts = [
        'rate_value' => 'decimal:4',
        'is_active' => 'boolean',
        'type' => TaxType::class
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function taxCategories()
    {
        return $this->belongsToMany(TaxCategory::class, 'tax_rules')
            ->withPivot('priority')
            ->orderByPivot('priority', 'desc');
    }

    public function calculateTax(float $amount): float
    {
        return match ($this->type) {
            TaxType::PERCENTAGE => ($amount * $this->rate_value) / 100,
            TaxType::FIXED => $this->rate_value,
            default => 0
        };
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getFormattedRateAttribute(): string
    {
        return $this->type === TaxType::PERCENTAGE
            ? $this->rate_value . '%'
            : format_currency($this->rate_value);
    }


 
}
