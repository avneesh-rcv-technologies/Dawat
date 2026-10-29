<?php

namespace Modules\FoodManagements\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FoodFeature extends Model
{
    protected $primaryKey = 'feature_id';

    protected $fillable = [
        'feature_code',
        'purpose',
        'feature_style',
        'filter_style',
        'feature_type',
        'parent_id',
        'display_on_product',
        'display_on_catalog',
        'display_on_header',
        'status',
        'position',
        'comparison',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'position' => 'integer',
    ];

    public function descriptions()
    {
        return $this->hasMany(FoodFeatureDescription::class, 'feature_id');
    }

    public function description(string $lang = 'en')
    {
        return $this->hasOne(FoodFeatureDescription::class, 'feature_id')
            ->where('lang_code', $lang);
    }

    public function variants()
    {
        return $this->hasMany(FoodFeatureVariant::class, 'feature_id')
            ->orderBy('position');
    }

    public function values()
    {
        return $this->hasMany(FoodFeatureValue::class, 'feature_id');
    }

    public function categories()
    {
        return $this->belongsToMany(
            FoodCategory::class,
            'food_feature_categories',
            'feature_id',
            'category_id'
        );
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Features without category links are treated as global and apply to all categories.
     */
    public function scopeApplicableToCategories(Builder $query, array $categoryIds = []): Builder
    {
        $categoryIds = array_values(array_unique(array_filter(
            array_map('intval', $categoryIds),
            fn (int $id) => $id > 0
        )));

        if (empty($categoryIds)) {
            return $query;
        }

        return $query->where(function (Builder $featureQuery) use ($categoryIds) {
            $featureQuery
                ->whereDoesntHave('categories')
                ->orWhereHas('categories', function (Builder $categoryQuery) use ($categoryIds) {
                    $categoryQuery->whereIn('food_categories.id', $categoryIds);
                });
        });
    }
}
