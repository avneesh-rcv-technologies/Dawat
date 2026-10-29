<?php

namespace Modules\MobileConfiguration\Models;

use Illuminate\Database\Eloquent\Model;

class MobileWalkthrough extends Model
{
    protected $table = 'mobile_walkthrough';

    protected $fillable = [
        'title',
        'description',
        'status',
        'position',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    // Get image using Filemanager helper
    public function getImageAttribute()
    {
        return fn_get_image('mobile_walkthrough', $this->id);
    }

    // Get image URL
    public function getImageUrlAttribute()
    {
        $image = $this->image;
        if ($image) {
            return asset('storage/' . $image->path . '/' . $image->file_name);
        }
        return null;
    }
}