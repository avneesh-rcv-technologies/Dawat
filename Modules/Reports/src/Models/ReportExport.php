<?php

namespace Modules\Reports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ReportExport extends Model
{
    protected $fillable = [
        'report_id',
        'execution_id',
        'exported_by_id',
        'exported_by_type',
        'format',
        'filename',
        'file_path',
        'file_size',
        'status',
        'export_options',
        'rows_exported',
        'download_count',
        'last_downloaded_at',
        'expires_at',
    ];

    protected $casts = [
        'export_options' => 'array',
        'last_downloaded_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the report that was exported.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    /**
     * Get the execution associated with this export.
     */
    public function execution(): BelongsTo
    {
        return $this->belongsTo(ReportExecution::class);
    }

    /**
     * Get the user who requested the export.
     */
    public function user(): MorphTo
    {
        return $this->morphTo('exported_by');
    }

    /**
     * Alias for exported_by relation.
     */
    public function exportedBy(): MorphTo
    {
        return $this->user();
    }

    /**
     * Scope to get completed exports.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get non-expired exports.
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Increment download count.
     */
    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
        $this->update(['last_downloaded_at' => now()]);
    }

    /**
     * Check if export has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Get file size in human-readable format.
     */
    public function getFileSizeHumanAttribute(): string
    {
        if (!$this->file_size) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    /**
     * Get format label.
     */
    public function getFormatLabelAttribute(): string
    {
        return strtoupper($this->format);
    }
}
