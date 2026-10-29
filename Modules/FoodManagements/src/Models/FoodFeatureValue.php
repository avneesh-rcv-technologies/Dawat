<?php

namespace Modules\FoodManagements\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class FoodFeatureValue extends Model
{
    protected $table = 'food_feature_values';

    public $timestamps = false;

    protected $fillable = [
        'feature_id',
        'food_item_id',
        'variant_id',
        'value',
        'value_int',
        'lang_code',
    ];

    protected $casts = [
        'value_int' => 'decimal:2',
    ];

    public function feature()
    {
        return $this->belongsTo(FoodFeature::class, 'feature_id');
    }

    public function foodItem()
    {
        return $this->belongsTo(FoodItem::class, 'food_item_id');
    }

    public function variant()
    {
        return $this->belongsTo(FoodFeatureVariant::class, 'variant_id');
    }

    protected function value(): Attribute
    {
        return Attribute::make(
            get: fn($value) => decode_html_entities($value)
        );
    }
}
