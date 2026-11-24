<?php

namespace Modules\DietaryManagment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\DietaryManagment\Services\CertificationTypeSynchronizer;

class DietaryLabel extends Model
{
    protected $table = 'dietary_labels';
    protected $keyType = 'string';

    protected $attributes = ['is_active' => true, 'is_certification_required' => false, 'display_order' => 0];

    protected $fillable = [
        'name', 'code', 'slug', 'icon', 'description', 'backend_value',
        'is_active', 'is_certification_required', 'display_order',
    ];

    protected $casts = ['is_active' => 'boolean', 'is_certification_required' => 'boolean', 'display_order' => 'integer'];

    protected static function booted(): void
    {
        static::creating(function (self $label) {
            // Preserve numeric keys on legacy databases and generate UUIDs on the new schema.
            if (! in_array(Schema::getColumnType($label->getTable(), $label->getKeyName()), [
                'bigint', 'int', 'integer', 'smallint', 'mediumint', 'tinyint',
            ], true)) {
                $label->setIncrementing(false);
                $label->setAttribute($label->getKeyName(), $label->getKey() ?: (string) Str::uuid());
            }
        });

        static::saved(function (self $label) {
            if ($label->wasRecentlyCreated || $label->wasChanged(['name', 'is_active', 'is_certification_required'])) {
                app(CertificationTypeSynchronizer::class)->sync($label);
            }
        });

        static::deleting(function (self $label) {
            $label->certificationType()->update(['is_required_for_orders' => false, 'is_active' => false]);
        });
    }

    public function certificationType(): HasOne
    {
        return $this->hasOne(CertificationType::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
