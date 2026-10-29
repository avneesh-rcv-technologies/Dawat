<?php

namespace Modules\Discounts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\FoodManagements\Models\FoodCategory;
use Modules\FoodManagements\Models\FoodItem;

class DiscountRule extends Model
{
    protected $fillable = [
        'discount_id',
        'rule_type',
        'rule_id',
        'rule_value',
        'condition_type'
    ];

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }


    protected $appends = ['rule_name'];

    public function product()
    {
        return $this->belongsTo(FoodItem::class, 'rule_id');
    }

    public function category()
    {
        return $this->belongsTo(FoodCategory::class, 'rule_id');
    }

    public function getRuleNameAttribute()
    {
        switch ($this->rule_type) {
            case 'product':
                return $this->product?->description?->name ?? '-';
            case 'category':
                return $this->category?->description?->name ?? '-';
            case 'subtotal':
                return 'Subtotal';
            default:
                return '-';
        }
    }
}