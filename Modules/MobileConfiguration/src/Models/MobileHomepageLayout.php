<?php

namespace Modules\MobileConfiguration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MobileHomepageLayout extends Model
{
    protected $table = 'mobile_homepage_layouts';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'layout_config',
        'is_default',
        'status',
        'position',
    ];

    protected $casts = [
        'layout_config' => 'array',
        'is_default' => 'boolean',
        'status' => 'boolean',
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

            // Ensure only one default layout
            if ($model->is_default) {
                static::where('is_default', true)->update(['is_default' => false]);
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

            // Ensure only one default layout
            if ($model->isDirty('is_default') && $model->is_default) {
                static::where('id', '!=', $model->id)->where('is_default', true)->update(['is_default' => false]);
            }
        });
    }

    // Get layout with populated data
    public function getLayoutWithData()
    {
        $layoutData = [];
        
        if (!$this->layout_config) {
            return $layoutData;
        }

        foreach ($this->layout_config as $section) {
            $sectionData = [
                'type' => $section['type'],
                'position' => $section['position'] ?? 0,
                'config' => $section['config'] ?? [],
                'data' => []
            ];

            if ($section['type'] === 'banners') {
                // Get banners data
                $bannerIds = $section['config']['banner_ids'] ?? [];
                if (!empty($bannerIds)) {
                    $sectionData['data'] = MobileBanner::whereIn('id', $bannerIds)
                        ->where('status', true)
                        ->orderByRaw("FIELD(id, " . implode(',', $bannerIds) . ")")
                        ->get()
                        ->map(function ($banner) {
                            return [
                                'id' => $banner->id,
                                'name' => $banner->name,
                                'type' => $banner->type,
                                'type_value' => $banner->type_value,
                                'link_display' => $banner->link_display,
                                'image' => $this->getBannerImageUrl($banner->id),
                            ];
                        });
                }
            } elseif ($section['type'] === 'features') {
                // Get features data
                $featureIds = $section['config']['feature_ids'] ?? [];
                if (!empty($featureIds)) {
                    $sectionData['data'] = MobileFeature::whereIn('id', $featureIds)
                        ->where('status', true)
                        ->orderByRaw("FIELD(id, " . implode(',', $featureIds) . ")")
                        ->get()
                        ->map(function ($feature) {
                            return [
                                'id' => $feature->id,
                                'name' => $feature->name,
                                'slug' => $feature->slug,
                                'used_for' => $feature->used_for, // Use existing used_for setting
                                'type' => $feature->type,
                                'items' => $feature->getItems()->take( 10), // Limit items for performance
                            ];
                        });
                }
            }

            $layoutData[] = $sectionData;
        }

        // Sort by position
        usort($layoutData, function ($a, $b) {
            return ($a['position'] ?? 0) <=> ($b['position'] ?? 0);
        });

        return $layoutData;
    }

    private function getBannerImageUrl($bannerId)
    {
        $image = fn_get_image('mobile_banner', $bannerId);
        return $image ? $image['url'] : null;
    }

    // Scope for default layout
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    // Scope for active layouts
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}