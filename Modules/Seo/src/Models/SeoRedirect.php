<?php

namespace Modules\Seo\Models;

use Illuminate\Database\Eloquent\Model;

class SeoRedirect extends Model
{
    protected $table = 'seo_redirects';

    protected $fillable = [
        'from_path',
        'to_url',
        'status_code',
        'match_type',
        'is_active',
        'preserve_query',
        'hits',
        'last_hit_at',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'preserve_query' => 'boolean',
        'hits' => 'integer',
        'status_code' => 'integer',
        'last_hit_at' => 'datetime',
    ];
}
