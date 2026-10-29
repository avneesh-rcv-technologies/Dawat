<?php

namespace Modules\Tax\Models;

use Illuminate\Database\Eloquent\Model;

class TaxCategory extends Model
{
    protected $fillable = [
        'name',
        'description',
        'priority',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    public function taxRates()
    {
        return $this->belongsToMany(TaxRate::class, 'tax_rules')
                   ->withPivot('priority')
                   ->orderByPivot('priority', 'desc');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function calculateTax(float $amount): float
    {
        return $this->taxRates->reduce(function ($carry, $taxRate) use ($amount) {
            return $carry + $taxRate->calculateTax($amount);
        }, 0);
    }
}