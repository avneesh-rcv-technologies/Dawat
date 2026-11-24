<?php

namespace Modules\DietaryManagment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CertificationType extends Model
{
    protected $attributes = ['is_required_for_orders' => false, 'is_active' => true];

    protected $fillable = ['name', 'dietary_label_id', 'is_required_for_orders', 'is_active'];

    protected $casts = ['is_required_for_orders' => 'boolean', 'is_active' => 'boolean'];

    public function dietaryLabel(): BelongsTo
    {
        return $this->belongsTo(DietaryLabel::class);
    }

    public function restaurantCertifications(): HasMany
    {
        return $this->hasMany(RestaurantCertification::class);
    }
}
