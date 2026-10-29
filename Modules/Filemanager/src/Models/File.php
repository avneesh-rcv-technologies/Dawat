<?php

namespace Modules\Filemanager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'original_name',
        'mime_type',
        'extension',
        'size',
        'path',
        'is_image',
        'width',
        'height',
    ];

    protected $casts = [
        'is_image' => 'boolean',
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    protected $appends = [
        'url',
        'formatted_size',
        'thumbnail_url',
        'medium_url',
        'large_url',
    ];

    /**
     * Get the file links for the file.
     */
    public function fileLinks(): HasMany
    {
        return $this->hasMany(FileLink::class);
    }

    /**
     * Get the full file path.
     */
    public function getFullPathAttribute(): string
    {
        return storage_path('app/' . $this->path . '/' . $this->file_name);
    }

    /**
     * Get the public URL for the file.
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path . '/' . $this->file_name);
    }

    /**
     * Get the thumbnail URL for the image.
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->is_image) {
            return null;
        }
        
        $thumbnailPath = $this->path . '/thumbnails/' . $this->file_name;
        
        if (Storage::exists($thumbnailPath)) {
            return asset('storage/' . $thumbnailPath);
        }
        
        return $this->url; // Fallback to original if thumbnail doesn't exist
    }

    /**
     * Get the medium-sized URL for the image.
     */
    public function getMediumUrlAttribute(): ?string
    {
        if (!$this->is_image) {
            return null;
        }
        
        $mediumPath = $this->path . '/medium/' . $this->file_name;
        
        if (Storage::exists($mediumPath)) {
            return asset('storage/' . $mediumPath);
        }
        
        return $this->url; // Fallback to original if medium doesn't exist
    }

    /**
     * Get the large-sized URL for the image.
     */
    public function getLargeUrlAttribute(): ?string
    {
        if (!$this->is_image) {
            return null;
        }
        
        $largePath = $this->path . '/large/' . $this->file_name;
        
        if (Storage::exists($largePath)) {
            return asset('storage/' . $largePath);
        }
        
        return $this->url; // Fallback to original if large doesn't exist
    }

    /**
     * Get the human readable file size.
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get image dimensions as a string.
     */
    public function getDimensionsAttribute(): ?string
    {
        if (!$this->is_image) {
            return null;
        }
        
        return $this->width . '×' . $this->height;
    }

    /**
     * Get image aspect ratio.
     */
    public function getAspectRatioAttribute(): ?string
    {
        if (!$this->is_image || !$this->height) {
            return null;
        }
        
        $gcd = function($a, $b) use (&$gcd) {
            return $b ? $gcd($b, $a % $b) : $a;
        };
        
        $divisor = $gcd($this->width, $this->height);
        
        return ($this->width / $divisor) . ':' . ($this->height / $divisor);
    }

  

}