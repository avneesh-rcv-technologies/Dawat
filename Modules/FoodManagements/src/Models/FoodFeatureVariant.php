<?php

namespace Modules\FoodManagements\Models;

use Illuminate\Database\Eloquent\Model;

class FoodFeatureVariant extends Model
{
    protected $table = 'food_feature_variants';

    protected $primaryKey = 'variant_id';

    protected $fillable = [
        'feature_id',
        'position',
        'status',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    public function feature()
    {
        return $this->belongsTo(FoodFeature::class, 'feature_id');
    }

    public function descriptions()
    {
        return $this->hasMany(FoodFeatureVariantDescription::class, 'variant_id');
    }

    public function description(string $lang = 'en')
    {
        return $this->hasOne(FoodFeatureVariantDescription::class, 'variant_id')
            ->where('lang_code', $lang);
    }

    public function values()
    {
        return $this->hasMany(FoodFeatureValue::class, 'variant_id');
    }
}
