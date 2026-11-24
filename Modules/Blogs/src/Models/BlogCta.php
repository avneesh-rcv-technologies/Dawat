<?php

namespace Modules\Blogs\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogCta extends Model
{
    use HasFactory;

    protected $table = 'blog_ctas';

    protected $fillable = [
        'name',
        'slug',
        'content',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
