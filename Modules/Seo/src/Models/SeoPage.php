<?php

namespace Modules\Seo\Models;

use Illuminate\Database\Eloquent\Model;

class SeoPage extends Model
{
    protected $table = 'seo_pages';

    protected $fillable = [
        'page_key',
        'route_name',
        'title',
        'description',
        'keywords',
        'robots',
        'og_image',
        'twitter_image',
        'schema',
        'canonical',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'schema' => 'array',
    ];
}
