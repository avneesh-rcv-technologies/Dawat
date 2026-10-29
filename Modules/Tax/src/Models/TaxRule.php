<?php

namespace Modules\Tax\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_category_id',
        'tax_rate_id',
        'priority'
    ];

    public function taxCategory()
    {
        return $this->belongsTo(TaxCategory::class);
    }

    public function taxRate()
    {
        return $this->belongsTo(TaxRate::class);
    }
}