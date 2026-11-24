<?php

namespace Modules\Blogs\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Faq\Models\Faq;
use Modules\Filemanager\Models\FileLink;
use Modules\Filemanager\Services\FileService;
use Modules\Seo\Support\JsonLd;

class Blog extends Model
{
    use HasFactory;

    protected $table = 'blogs';

    protected $fillable = [
        'title',
        'short_description',
        'long_description',
        'inner_image',
        'outer_image',
        'author_name',
        'author_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'schema',
        'post_url',
        'status',
        'published_at',
        'scheduled_at',
        'scheduled_timezone',
        'sort_order',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'scheduled_at' => 'datetime',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            BlogCategory::class,
            'blog_category_pivot',
            'blog_id',
            'category_id'
        )->withTimestamps();
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(BlogAuthor::class, 'author_id');
    }

    public function faqs(): BelongsToMany
    {
        return $this->belongsToMany(Faq::class, 'faq_blog')
            ->orderBy('faqs.sort_order');
    }

    public function outerImageLinks(): HasMany
    {
        return $this->hasMany(FileLink::class, 'object_id')
            ->where('object_type', 'blog_outer_image')->orderBy('position')->with('file');
    }

    public function innerImageLinks(): HasMany
    {
        return $this->hasMany(FileLink::class, 'object_id')
            ->where('object_type', 'blog_inner_image')->orderBy('position')->with('file');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeLatestFirst($query)
    {
        return $query->orderByDesc('published_at')->orderByDesc('created_at');
    }

    /**
     * Date shown on public blog pages (published date, falling back to created).
     */
    public function getDisplayDateAttribute()
    {
        return $this->published_at ?? $this->created_at;
    }

    public function getAuthorDisplayNameAttribute(): string
    {
        return $this->author?->name
            ?: ($this->author_name ?: 'Dawat Team');
    }

    /**
     * Decode custom SEO schema JSON safely.
     */
    public function getDecodedSchemaAttribute(): ?array
    {
        if (! filled($this->schema)) {
            return null;
        }

        $decoded = json_decode($this->schema, true);

        return json_last_error() === JSON_ERROR_NONE && JsonLd::valid($decoded)
            ? $decoded
            : null;
    }

    public function getInnerImageUrlAttribute(): ?string
    {
        if ($this->relationLoaded('innerImageLinks')) {
            return $this->loadedImageUrl('innerImageLinks', $this->inner_image);
        }

        $images = fn_get_images('blog_inner_image', $this->id);
        if (! empty($images[0]['url'])) {
            return $images[0]['url'];
        }

        return $this->inner_image ? asset('storage/' . $this->inner_image) : null;
    }

    public function getOuterImageUrlAttribute(): ?string
    {
        if ($this->relationLoaded('outerImageLinks')) {
            return $this->loadedImageUrl('outerImageLinks', $this->outer_image);
        }

        $images = fn_get_images('blog_outer_image', $this->id);
        if (! empty($images[0]['url'])) {
            return $images[0]['url'];
        }

        return $this->outer_image ? asset('storage/' . $this->outer_image) : null;
    }

    protected function loadedImageUrl(string $relation, ?string $fallback): ?string
    {
        $file = $this->getRelation($relation)->first()?->file;
        if ($file) {
            if (app()->runningInConsole() && ! app()->runningUnitTests()) {
                return app(FileService::class)->getStoredFileUrl($file);
            }

            // Reuse the selected storage driver for this request without changing other FileService users.
            $attributes = request()->attributes;
            $service = $attributes->get('blogs.image_file_service');
            if (! $service) {
                $service = app(FileService::class);
                $attributes->set('blogs.image_file_service', $service);
            }

            return $service->getStoredFileUrl($file);
        }

        return $fallback ? asset('storage/' . $fallback) : null;
    }
}
