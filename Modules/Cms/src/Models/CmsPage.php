<?php

namespace Modules\Cms\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CmsPage extends Model
{
    protected $table = 'cms_page_settings';

    protected $fillable = [
        'page_title',
        'description',
        'slug',
        'status',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    // Auto-generate slug when creating
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->page_title);

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
            if ($model->isDirty('page_title') && !$model->isDirty('slug')) {
                $model->slug = Str::slug($model->page_title);

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

       
}
