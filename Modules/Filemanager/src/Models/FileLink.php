<?php
namespace Modules\Filemanager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'object_type',
        'object_id',
        'type',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    /**
     * Get the file that owns the file link.
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Get the related model instance.
     */
    public function object()
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include main files.
     */
    public function scopeMain($query)
    {
        return $query->where('type', 'M');
    }

    /**
     * Scope a query to only include additional files.
     */
    public function scopeAdditional($query)
    {
        return $query->where('type', 'A');
    }

    /**
     * Scope a query to filter by object.
     */
    public function scopeForObject($query, $objectType, $objectId)
    {
        return $query->where('object_type', $objectType)
                    ->where('object_id', $objectId);
    }
}   