<?php

namespace Modules\Filemanager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Filemanager\Services\FileService;

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
        'driver',
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
        return app(FileService::class)->getStoredFileUrl($this);
    }

    /**
     * Get the thumbnail URL for the image.
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->is_image) {
            return null;
        }

        return app(FileService::class)->getStoredFileUrl($this, 'thumbnails');
    }

    /**
     * Get the medium-sized URL for the image.
     */
    public function getMediumUrlAttribute(): ?string
    {
        if (!$this->is_image) {
            return null;
        }

        return app(FileService::class)->getStoredFileUrl($this, 'medium');
    }

    /**
     * Get the large-sized URL for the image.
     */
    public function getLargeUrlAttribute(): ?string
    {
        if (!$this->is_image) {
            return null;
        }

        return app(FileService::class)->getStoredFileUrl($this, 'large');
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
