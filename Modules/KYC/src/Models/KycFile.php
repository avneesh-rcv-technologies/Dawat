<?php

namespace Modules\KYC\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class KycFile extends Model
{
    use HasFactory;
    protected $fillable = [
        'submission_id',
        'field_id',
        'file_path',
        'file_type',
        'uploaded_at'
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    protected $appends = ['url'];

    public $timestamps = false;

    /**
     * Get the submission this file belongs to
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(KycSubmission::class, 'submission_id');
    }

    /**
     * Get the field this file belongs to
     */
    public function field(): BelongsTo
    {
        return $this->belongsTo(KycField::class, 'field_id');
    }

    /**
     * Get the full URL of the file
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    /**
     * Get the file size
     */
    public function getSizeAttribute(): int
    {
        return Storage::size($this->file_path);
    }

    /**
     * Get human readable file size
     */
    public function getHumanSizeAttribute(): string
    {
        $size = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, 2) . ' ' . $units[$i];
    }

    /**
     * Delete the physical file when model is deleted
     */
    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function ($file) {
            Storage::delete($file->file_path);
        });
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Modules\KYC\Database\Factories\KycFileFactory::new();
    }
}
