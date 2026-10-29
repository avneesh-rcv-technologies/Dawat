<?php

namespace Modules\FoodManagements\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class FoodFeatureVariantDescription extends Model
{
    protected $table = 'food_feature_variant_descriptions';

    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = [
        'variant_id',
        'lang_code',
        'description',
    ];

    public function variant()
    {
        return $this->belongsTo(FoodFeatureVariant::class, 'variant_id');
    }

    protected function setKeysForSaveQuery($query): Builder
    {
        return $query
            ->where('variant_id', $this->getOriginal('variant_id', $this->variant_id))
            ->where('lang_code', $this->getOriginal('lang_code', $this->lang_code));
    }
    public function description(): Attribute
    {
        return Attribute::make(
            get: fn($value) => decode_html_entities($value)
        );
    }
}
