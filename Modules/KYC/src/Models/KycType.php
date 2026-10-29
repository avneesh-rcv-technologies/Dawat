<?php

namespace Modules\KYC\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Acl\Models\Admin;

class KycType extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'type',
        'description',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the admin who created this KYC type
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    /**
     * Get the fields for this KYC type
     */
    public function fields(): HasMany
    {
        return $this->hasMany(KycField::class, 'kyc_type_id')->orderBy('order_index');
    }

    /**
     * Get the submissions for this KYC type
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(KycSubmission::class, 'kyc_type_id');
    }

    /**
     * Get the requests for this KYC type
     */
    public function requests(): HasMany
    {
        return $this->hasMany(KycRequest::class, 'kyc_type_id');
    }

    /**
     * Check if this KYC type is being used
     */
    public function isInUse(): bool
    {
        return $this->submissions()->exists();
    }

    /**
     * Scope for active KYC types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for onboarding KYC types (excludes compliance)
     */
    public function scopeKyc($query)
    {
        return $query->where('type', 'kyc');
    }

    /**
     * Scope for compliance types
     */
    public function scopeCompliance($query)
    {
        return $query->where('type', 'compliance');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Modules\KYC\Database\Factories\KycTypeFactory::new();
    }
}
