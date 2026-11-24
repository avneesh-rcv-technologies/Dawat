<?php

namespace Modules\Blogs\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogCategory extends Model
{
    use HasFactory;

    protected $table = 'blog_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'position',
        'image',
        'status',
        'parent_id',
        'banner_path',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'locale_id',
    ];

    /**
     * A category belongs to many blogs (many-to-many).
     */
    public function blogs()
    {
        return $this->belongsToMany(
            Blog::class,
            'blog_category_pivot', // pivot table
            'category_id',         // foreign key for category
            'blog_id'              // foreign key for blog
        )->withTimestamps();
    }

    /**
     * Parent category (for hierarchical categories).
     */
    public function parent()
    {
        return $this->belongsTo(BlogCategory::class, 'parent_id');
    }

    /**
     * Child categories.
     */
    public function children()
    {
        return $this->hasMany(BlogCategory::class, 'parent_id');
    }

    public function getBannerUrlAttribute(): ?string
    {
        $images = fn_get_images('blog_category_banner', $this->id);
        if (! empty($images[0]['url'])) {
            return $images[0]['url'];
        }

        if ($this->banner_path) {
            return asset('storage/' . ltrim($this->banner_path, '/'));
        }

        return null;
    }
}
