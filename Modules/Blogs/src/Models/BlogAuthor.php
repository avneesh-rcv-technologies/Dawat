<?php

namespace Modules\Blogs\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlogAuthor extends Model
{
    use HasFactory;

    protected $table = 'blog_authors';

    protected $fillable = [
        'name',
        'slug',
        'designation',
        'bio',
        'avatar',
        'email',
        'twitter_url',
        'linkedin_url',
        'facebook_url',
        'instagram_url',
        'youtube_url',
        'website_url',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class, 'author_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Social links that have a value set.
     */
    public function socialLinks(): array
    {
        $links = [
            'twitter' => $this->twitter_url,
            'linkedin' => $this->linkedin_url,
            'facebook' => $this->facebook_url,
            'instagram' => $this->instagram_url,
            'youtube' => $this->youtube_url,
            'website' => $this->website_url,
        ];

        return array_filter($links, fn ($url) => filled($url));
    }

    public function getAvatarUrlAttribute(): ?string
    {
        $images = fn_get_images('blog_author_avatar', $this->id);
        if (! empty($images[0]['url'])) {
            return $images[0]['url'];
        }

        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }

        return null;
    }
}
