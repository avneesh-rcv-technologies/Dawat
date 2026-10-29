<?php

namespace Modules\FoodManagements\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class FoodFeatureDescription extends Model
{
    protected $table = 'food_feature_descriptions';

    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = [
        'feature_id',
        'lang_code',
        'name',
        'description',
    ];

       protected function name(): Attribute
    {
        return Attribute::make(
            get: fn($value) => decode_html_entities($value)
        );
    }

    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn($value) => decode_html_entities($value)
        );
    }

    public function feature()
    {
        return $this->belongsTo(FoodFeature::class, 'feature_id');
    }

    protected function setKeysForSaveQuery($query): Builder
    {
        return $query
            ->where('feature_id', $this->getOriginal('feature_id', $this->feature_id))
            ->where('lang_code', $this->getOriginal('lang_code', $this->lang_code));
    }

   


}
