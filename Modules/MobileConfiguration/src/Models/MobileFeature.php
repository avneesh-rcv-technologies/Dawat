<?php

namespace Modules\MobileConfiguration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MobileFeature extends Model
{
    protected $table = 'mobile_features';

    protected $fillable = [
        'name',
        'slug',
        'used_for',
        'type',
        'selection_type',
        'custom_items',
        'items_count',
        'position',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'custom_items' => 'array',
    ];

    // Auto-generate slug when creating
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);

                // Ensure unique slug
                $originalSlug = $model->slug;
                $counter = 1;
                while (static::where('slug', $model->slug)->exists()) {
                    $model->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('name') && !$model->isDirty('slug')) {
                $model->slug = Str::slug($model->name);

                // Ensure unique slug (excluding current record)
                $originalSlug = $model->slug;
                $counter = 1;
                while (static::where('slug', $model->slug)->where('id', '!=', $model->id)->exists()) {
                    $model->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
        });
    }

    // Get items based on selection type
    public function getItems()
    {
        // Use the service for better data handling
        $service = app(\Modules\MobileConfiguration\Services\MobileFeatureService::class);
        return $service->getFeatureItems($this);
    }
}