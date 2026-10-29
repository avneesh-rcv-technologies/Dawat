<?php

namespace Modules\Acl\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'guard',
        'permission_type',
        'permissions',
    ];
    protected $casts = [
        'permissions' => 'array',
    ];

    public function isProtected(): bool
    {
        return strtolower(trim((string) $this->name)) === 'admin';
    }

    protected static function booted(): void
    {
        static::updating(function (self $role) {
            if (strtolower(trim((string) $role->getRawOriginal('name'))) === 'admin') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'role' => 'The Admin role is protected and cannot be edited.',
                ]);
            }
        });
    }
}
