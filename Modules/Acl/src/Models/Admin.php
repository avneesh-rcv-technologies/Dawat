<?php

namespace Modules\Acl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Modules\Filemanager\Models\File;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'admins';
    
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'role_id',
        'email_verified_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id')
            ->where('guard', 'admin');
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->role && $this->role->permission_type === 'all') {
            return true;
        }

        return in_array($permission, $this->role->permissions ?? []);
    }


    public function logo(){
        return $this->hasOne(File::class, 'admin_id', 'id');
    }

    /**
     * Polymorphic relation to FileLink entries for this admin.
     */
    public function fileLinks()
    {
        return $this->morphMany(\Modules\Filemanager\Models\FileLink::class, 'object');
    }

    /**
     * Returns the primary avatar FileLink (type 'M' or 'A' depending on usage) with the file loaded.
     */
    public function avatarLink()
    {
        return $this->morphOne(\Modules\Filemanager\Models\FileLink::class, 'object')->with('file');
    }

    /**
     * Accessor to get formatted avatar data via FileService (uses object type 'avatar').
     * Returns array from FileService::getFile() or null.
     */
    public function getAvatarAttribute()
    {
        try {
            $fileService = app(\Modules\Filemanager\Services\FileService::class);
            $data = $fileService->getFile('avatar', $this->id);
            return $data ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Helper to get avatar URL (or default image path if none).
     */
    public function logoUrl(): ?string
    {
        $avatar = $this->avatar;
        return is_array($avatar) ? ($avatar['url'] ?? null) : null;
    }

}
